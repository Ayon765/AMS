<?php

namespace App\Http\Controllers;

use App\Models\StockDocument;
use App\Services\StockService;
use App\Support\DocType;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    public function index(Request $r, string $type)
    {
        $cfg = DocType::get($type);
        $status = $r->get('status', 'pending');

        $docs = StockDocument::with(['branch', 'supplier', 'employee', 'creator'])->where('type', $type)
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest('id')->paginate(15)->withQueryString();

        return view('approvals.index', compact('type', 'cfg', 'docs', 'status'));
    }

    public function approve(StockDocument $doc, StockService $svc)
    {
        abort_unless($doc->status === 'pending' && DocType::get($doc->type)['approval'], 422);

        try {
            $svc->apply($doc->load('items'));
        } catch (\RuntimeException $e) {
            return back()->withErrors(['stock' => $e->getMessage()]);
        }

        $doc->update(['approved_by' => auth()->id(), 'approved_at' => now()]);

        return back()->with('ok', $doc->ref_no . ' approved and stock updated.');
    }

    public function reject(StockDocument $doc)
    {
        abort_unless($doc->status === 'pending', 422);
        $doc->update(['status' => 'rejected', 'approved_by' => auth()->id(), 'approved_at' => now()]);

        return back()->with('ok', $doc->ref_no . ' rejected.');
    }
}
