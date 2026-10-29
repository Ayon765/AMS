<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function index(Request $r)
    {
        $q = Stock::query()
            ->when($r->branch_id, fn ($q, $b) => $q->where('stocks.branch_id', $b))
            ->when($r->q, fn ($q, $s) => $q->whereHas('product', fn ($p) => $p->where('name', 'like', "%$s%")->orWhere('code', 'like', "%$s%")));

        $value = (clone $q)->join('products', 'products.id', '=', 'stocks.product_id')
            ->sum(DB::raw('stocks.qty * products.purchase_price'));
        $qty = (clone $q)->sum('stocks.qty');

        return view('stock.index', [
            'rows'     => $q->with(['product.category', 'product.uom', 'branch'])->orderByDesc('stocks.id')->paginate(20)->withQueryString(),
            'branches' => Branch::orderBy('name')->get(),
            'value'    => $value,
            'qty'      => $qty,
        ]);
    }
}
