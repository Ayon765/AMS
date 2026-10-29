<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\CashEntry;
use Illuminate\Http\Request;

class CashController extends Controller
{
    private function check(string $type): void
    {
        abort_unless(in_array($type, ['in', 'out']), 404);
        abort_unless(auth()->user()->hasPerm('cash_' . $type), 403);
    }

    public function index(Request $r, string $type)
    {
        $this->check($type);

        $q = CashEntry::with('branch')->where('type', $type)
            ->when($r->from, fn ($q, $d) => $q->whereDate('entry_date', '>=', $d))
            ->when($r->to, fn ($q, $d) => $q->whereDate('entry_date', '<=', $d));

        return view('cash.index', [
            'type'     => $type,
            'rows'     => (clone $q)->latest('entry_date')->latest('id')->paginate(15)->withQueryString(),
            'total'    => $q->sum('amount'),
            'branches' => Branch::orderBy('name')->get(),
        ]);
    }

    public function store(Request $r, string $type)
    {
        $this->check($type);

        $data = $r->validate([
            'entry_date' => 'required|date',
            'branch_id'  => 'nullable|exists:branches,id',
            'head'       => 'required|string|max:255',
            'amount'     => 'required|numeric|gt:0',
            'note'       => 'nullable|string',
        ]);

        CashEntry::create($data + ['type' => $type, 'created_by' => auth()->id()]);

        return back()->with('ok', 'Cash ' . $type . ' entry saved.');
    }

    public function destroy(CashEntry $entry)
    {
        $this->check($entry->type);

        if ($entry->stock_document_id) {
            return back()->withErrors(['delete' => 'This entry belongs to an invoice and cannot be deleted.']);
        }

        $entry->delete();

        return back()->with('ok', 'Deleted.');
    }
}
