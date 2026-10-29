<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\CashEntry;
use App\Models\StockDocument;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    private const TITLES = [
        'purchase' => 'Purchase Report', 'delivery' => 'Delivery Report', 'adjustment' => 'Adjustment Report',
        'damage' => 'Damage Report', 'cash_in' => 'Cash In Report', 'cash_out' => 'Cash Out Report',
    ];

    public function show(Request $r, string $type)
    {
        abort_unless(isset(self::TITLES[$type]), 404);

        $from = $r->get('from', now()->startOfMonth()->toDateString());
        $to = $r->get('to', now()->toDateString());
        $branch = $r->branch_id;
        $isCash = str_starts_with($type, 'cash_');

        if ($isCash) {
            $rows = CashEntry::with('branch')->where('type', substr($type, 5))
                ->whereBetween('entry_date', [$from, $to])
                ->when($branch, fn ($q, $b) => $q->where('branch_id', $b))
                ->orderBy('entry_date')->get();
            $total = $rows->sum('amount');
            $status = null;
        } else {
            $status = $r->get('status', 'approved');
            $rows = StockDocument::with(['branch', 'supplier', 'employee'])->withCount('items')
                ->where('type', $type)->whereBetween('doc_date', [$from, $to])
                ->when($status !== 'all', fn ($q) => $q->where('status', $status))
                ->when($branch, fn ($q, $b) => $q->where('branch_id', $b))
                ->orderBy('doc_date')->get();
            $total = $rows->sum('total');
        }

        return view('reports.show', [
            'type' => $type, 'title' => self::TITLES[$type], 'isCash' => $isCash, 'rows' => $rows,
            'total' => $total, 'from' => $from, 'to' => $to, 'branch' => $branch, 'status' => $status,
            'branches' => Branch::orderBy('name')->get(),
        ]);
    }
}
