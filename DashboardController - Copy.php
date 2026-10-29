<?php

namespace App\Http\Controllers;

use App\Models\CashEntry;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockDocument;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $low = Product::where('is_active', true)->where('min_stock', '>', 0)
            ->withSum('stocks as total_qty', 'qty')->get()
            ->filter(fn ($p) => (float) ($p->total_qty ?? 0) <= $p->min_stock)->take(8);

        $cashIn = CashEntry::where('type', 'in')->sum('amount');
        $cashOut = CashEntry::where('type', 'out')->sum('amount');

        return view('dashboard', [
            'products'   => Product::count(),
            'stockQty'   => Stock::sum('qty'),
            'stockValue' => Stock::join('products', 'products.id', '=', 'stocks.product_id')->sum(DB::raw('stocks.qty * products.purchase_price')),
            'pending'    => StockDocument::where('status', 'pending')->selectRaw('type, count(*) c')->groupBy('type')->pluck('c', 'type'),
            'cashIn'     => $cashIn,
            'cashOut'    => $cashOut,
            'todaySales' => StockDocument::where('type', 'invoice')->whereDate('doc_date', today())->sum('total'),
            'monthSales' => StockDocument::where('type', 'invoice')->whereYear('doc_date', now()->year)->whereMonth('doc_date', now()->month)->sum('total'),
            'low'        => $low,
            'recent'     => StockDocument::with('branch')->whereIn('type', ['invoice', 'purchase'])->latest('id')->take(6)->get(),
        ]);
    }
}
