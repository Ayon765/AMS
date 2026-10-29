@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
@php $cur = \App\Models\Setting::val('currency', '৳'); @endphp
<h1 class="text-xl font-bold mb-3">Dashboard</h1>
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
    <div class="card"><div class="text-gray-500 text-xs">Products</div><div class="text-2xl font-bold">{{ $products }}</div></div>
    <div class="card"><div class="text-gray-500 text-xs">Total Stock Qty</div><div class="text-2xl font-bold">{{ number_format($stockQty, 2) }}</div></div>
    <div class="card"><div class="text-gray-500 text-xs">Stock Value</div><div class="text-2xl font-bold">{{ $cur }}{{ number_format($stockValue, 2) }}</div></div>
    <div class="card"><div class="text-gray-500 text-xs">Cash Balance</div><div class="text-2xl font-bold {{ $cashIn - $cashOut < 0 ? 'text-red-600' : 'text-green-600' }}">{{ $cur }}{{ number_format($cashIn - $cashOut, 2) }}</div></div>
    <div class="card"><div class="text-gray-500 text-xs">Today's Sales</div><div class="text-xl font-bold">{{ $cur }}{{ number_format($todaySales, 2) }}</div></div>
    <div class="card"><div class="text-gray-500 text-xs">This Month's Sales</div><div class="text-xl font-bold">{{ $cur }}{{ number_format($monthSales, 2) }}</div></div>
    <div class="card"><div class="text-gray-500 text-xs">Total Cash In</div><div class="text-xl font-bold">{{ $cur }}{{ number_format($cashIn, 2) }}</div></div>
    <div class="card"><div class="text-gray-500 text-xs">Total Cash Out</div><div class="text-xl font-bold">{{ $cur }}{{ number_format($cashOut, 2) }}</div></div>
</div>

<div class="grid md:grid-cols-3 gap-4">
    <div class="card">
        <h2 class="font-semibold mb-2">⏳ Pending Approvals</h2>
        @foreach (['purchase' => 'Purchase', 'delivery' => 'Delivery', 'adjustment' => 'Adjustment', 'damage' => 'Damage'] as $k => $l)
            @if (auth()->user()->hasPerm('approval'))
                <a href="{{ route('approval.index', $k) }}" class="flex justify-between py-1 border-b hover:bg-gray-50"><span>{{ $l }}</span><span class="font-bold">{{ $pending[$k] ?? 0 }}</span></a>
            @else
                <div class="flex justify-between py-1 border-b"><span>{{ $l }}</span><span class="font-bold">{{ $pending[$k] ?? 0 }}</span></div>
            @endif
        @endforeach
    </div>
    <div class="card">
        <h2 class="font-semibold mb-2">⚠️ Low Stock</h2>
        @forelse ($low as $p)
            <div class="flex justify-between py-1 border-b"><span>{{ $p->name }}</span><span class="text-red-600">{{ (float) ($p->total_qty ?? 0) }} / {{ $p->min_stock }}</span></div>
        @empty <p class="text-gray-500">All good.</p> @endforelse
    </div>
    <div class="card">
        <h2 class="font-semibold mb-2">🧾 Recent Invoices & Purchases</h2>
        @forelse ($recent as $d)
            <a href="{{ route('doc.show', [$d->type, $d]) }}" class="flex justify-between py-1 border-b hover:bg-gray-50"><span>{{ $d->ref_no }}</span><span>{{ $cur }}{{ number_format($d->total, 2) }}</span></a>
        @empty <p class="text-gray-500">Nothing yet.</p> @endforelse
    </div>
</div>
@endsection
