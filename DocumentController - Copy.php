<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\CashEntry;
use App\Models\Employee;
use App\Models\Product;
use App\Models\StockDocument;
use App\Models\Supplier;
use App\Services\StockService;
use App\Support\DocType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DocumentController extends Controller
{
    private function cfg(string $type): array
    {
        $cfg = DocType::get($type);
        abort_unless(auth()->user()->hasPerm($cfg['perm']), 403);

        return $cfg;
    }

    public function index(Request $r, string $type)
    {
        $cfg = $this->cfg($type);

        $docs = StockDocument::with(['branch', 'supplier', 'employee', 'toBranch'])->where('type', $type)
            ->when($r->q, fn ($q, $s) => $q->where(fn ($w) => $w->where('ref_no', 'like', "%$s%")->orWhere('customer_name', 'like', "%$s%")))
            ->when($r->status, fn ($q, $s) => $q->where('status', $s))
            ->latest('doc_date')->latest('id')->paginate(15)->withQueryString();

        return view('documents.index', compact('type', 'cfg', 'docs'));
    }

    public function create(string $type)
    {
        $cfg = $this->cfg($type);

        return view('documents.form', [
            'type'      => $type,
            'cfg'       => $cfg,
            'branches'  => Branch::where('is_active', true)->orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
            'employees' => Employee::where('is_active', true)->orderBy('name')->get(),
            'products'  => Product::where('is_active', true)->orderBy('name')->get(['id', 'code', 'name', 'purchase_price', 'sale_price']),
        ]);
    }

    public function store(Request $r, string $type, StockService $svc)
    {
        $cfg = $this->cfg($type);

        $rules = [
            'doc_date'            => 'required|date',
            'branch_id'           => 'required|exists:branches,id',
            'note'                => 'nullable|string',
            'items'               => 'required|array|min:1',
            'items.*.product_id'  => 'required|exists:products,id',
            'items.*.qty'         => 'required|numeric|gt:0',
            'items.*.price'       => 'nullable|numeric|min:0',
        ];

        if ($type === 'purchase') {
            $rules['supplier_id'] = 'required|exists:suppliers,id';
        }
        if ($type === 'delivery') {
            $rules['employee_id'] = 'nullable|exists:employees,id';
        }
        if ($type === 'adjustment') {
            $rules['items.*.direction'] = 'required|in:in,out';
        }
        if ($type === 'transfer') {
            $rules['to_branch_id'] = 'required|exists:branches,id|different:branch_id';
        }
        if (in_array($type, ['proposal', 'invoice'])) {
            $rules += [
                'customer_name'  => 'required|string|max:255',
                'customer_phone' => 'nullable|string|max:50',
                'discount'       => 'nullable|numeric|min:0',
                'paid'           => 'nullable|numeric|min:0',
            ];
        }

        $data = $r->validate($rules);

        try {
            $doc = DB::transaction(function () use ($data, $type, $cfg, $svc) {
                $items = collect($data['items'])->map(fn ($i) => [
                    'product_id' => $i['product_id'],
                    'qty'        => $i['qty'],
                    'price'      => $i['price'] ?? 0,
                    'direction'  => $i['direction'] ?? null,
                ]);

                $subtotal = round($items->sum(fn ($i) => $i['qty'] * $i['price']), 2);
                $discount = min((float) ($data['discount'] ?? 0), $subtotal);
                $total = $subtotal - $discount;
                $paid = min((float) ($data['paid'] ?? 0), $total);

                $doc = StockDocument::create([
                    'type'           => $type,
                    'doc_date'       => $data['doc_date'],
                    'branch_id'      => $data['branch_id'],
                    'to_branch_id'   => $data['to_branch_id'] ?? null,
                    'supplier_id'    => $data['supplier_id'] ?? null,
                    'employee_id'    => $data['employee_id'] ?? null,
                    'customer_name'  => $data['customer_name'] ?? null,
                    'customer_phone' => $data['customer_phone'] ?? null,
                    'status'         => $cfg['approval'] ? 'pending' : 'open',
                    'subtotal'       => $subtotal,
                    'discount'       => $discount,
                    'total'          => $total,
                    'paid'           => $type === 'invoice' ? $paid : 0,
                    'note'           => $data['note'] ?? null,
                    'created_by'     => auth()->id(),
                ]);

                $doc->update(['ref_no' => $cfg['prefix'] . '-' . str_pad($doc->id, 6, '0', STR_PAD_LEFT)]);
                $doc->items()->createMany($items->all());

                if ($cfg['immediate']) {
                    $svc->apply($doc->load('items'));

                    if ($type === 'invoice' && $paid > 0) {
                        $this->cashFor($doc, $paid);
                    }
                }

                return $doc;
            });
        } catch (\RuntimeException $e) {
            return back()->withInput()->withErrors(['stock' => $e->getMessage()]);
        }

        return redirect()->route('doc.show', [$type, $doc])->with('ok', $cfg['label'] . ' created.');
    }

    public function show(string $type, StockDocument $doc)
    {
        $cfg = $this->cfg($type);
        abort_unless($doc->type === $type, 404);
        $doc->load(['items.product.uom', 'branch', 'toBranch', 'supplier', 'employee', 'creator', 'approver']);

        return view('documents.show', compact('type', 'cfg', 'doc'));
    }

    public function destroy(string $type, StockDocument $doc)
    {
        $this->cfg($type);
        abort_unless($doc->type === $type, 404);

        if (! in_array($doc->status, ['pending', 'rejected', 'open'])) {
            return back()->withErrors(['delete' => 'Only pending, rejected or open documents can be deleted.']);
        }

        $doc->delete();

        return redirect()->route('doc.index', $type)->with('ok', 'Deleted.');
    }

    public function convert(string $type, StockDocument $doc, StockService $svc)
    {
        $this->cfg('invoice');
        abort_unless($type === 'proposal' && $doc->type === 'proposal' && $doc->status === 'open', 422);

        try {
            $inv = DB::transaction(function () use ($doc, $svc) {
                $inv = $doc->replicate(['ref_no', 'approved_by', 'approved_at'])->fill([
                    'type' => 'invoice', 'status' => 'open', 'paid' => 0, 'created_by' => auth()->id(),
                ]);
                $inv->save();
                $inv->update(['ref_no' => 'INV-' . str_pad($inv->id, 6, '0', STR_PAD_LEFT)]);

                foreach ($doc->items as $i) {
                    $inv->items()->create($i->only(['product_id', 'qty', 'price', 'direction']));
                }

                $svc->apply($inv->load('items'));
                $doc->update(['status' => 'converted']);

                return $inv;
            });
        } catch (\RuntimeException $e) {
            return back()->withErrors(['stock' => $e->getMessage()]);
        }

        return redirect()->route('doc.show', ['invoice', $inv])->with('ok', 'Converted to invoice.');
    }

    /** Record the paid part of an invoice in the cash book. */
    public function pay(Request $r, string $type, StockDocument $doc)
    {
        $this->cfg('invoice');
        abort_unless($doc->type === 'invoice', 404);

        $amount = $r->validate(['amount' => 'required|numeric|gt:0'])['amount'];
        $due = (float) $doc->total - (float) $doc->paid;

        if ($amount > $due) {
            return back()->withErrors(['amount' => 'Amount exceeds the due balance (' . number_format($due, 2) . ').']);
        }

        $doc->increment('paid', $amount);
        $this->cashFor($doc, $amount);

        return back()->with('ok', 'Payment recorded in Cash In.');
    }

    private function cashFor(StockDocument $doc, float $amount): void
    {
        CashEntry::create([
            'type'              => 'in',
            'entry_date'        => $doc->doc_date,
            'branch_id'         => $doc->branch_id,
            'amount'            => $amount,
            'head'              => 'Invoice ' . $doc->ref_no,
            'stock_document_id' => $doc->id,
            'created_by'        => auth()->id(),
        ]);
    }
}
