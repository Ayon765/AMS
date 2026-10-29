@extends('layouts.app')
@section('title', 'Inventory Stock')
@section('content')
@php $cur = \App\Models\Setting::val('currency', '৳'); @endphp
<h1 class="text-xl font-bold mb-3">Inventory Stock</h1>
<div class="grid grid-cols-2 gap-3 mb-3 max-w-lg">
    <div class="card"><div class="text-xs text-gray-500">Total Qty</div><div class="text-xl font-bold">{{ number_format($qty, 2) }}</div></div>
    <div class="card"><div class="text-xs text-gray-500">Stock Value (at purchase price)</div><div class="text-xl font-bold">{{ $cur }}{{ number_format($value, 2) }}</div></div>
</div>
<form class="flex flex-wrap gap-2 mb-3">
    <input name="q" value="{{ request('q') }}" placeholder="Product name / code" class="inp max-w-xs">
    <select name="branch_id" class="inp max-w-48"><option value="">All branches</option>
        @foreach ($branches as $b)<option value="{{ $b->id }}" @selected(request('branch_id') == $b->id)>{{ $b->name }}</option>@endforeach
    </select>
    <button class="btn-sec">Filter</button>
</form>
<div class="bg-white rounded shadow overflow-x-auto">
    <table class="w-full">
        <thead><tr><th class="th">Code</th><th class="th">Product</th><th class="th">Category</th><th class="th">Branch</th><th class="th text-right">Qty</th><th class="th text-right">Value</th></tr></thead>
        <tbody>
        @forelse ($rows as $s)
            <tr>
                <td class="td">{{ $s->product->code }}</td>
                <td class="td">{{ $s->product->name }}</td>
                <td class="td">{{ $s->product->category?->name ?? '—' }}</td>
                <td class="td">{{ $s->branch->name }}</td>
                <td class="td text-right {{ $s->qty <= $s->product->min_stock ? 'text-red-600 font-semibold' : '' }}">{{ (float) $s->qty }} {{ $s->product->uom?->short_name }}</td>
                <td class="td text-right">{{ $cur }}{{ number_format($s->qty * $s->product->purchase_price, 2) }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="p-6 text-center text-gray-500">No stock yet. Create and approve a purchase first.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $rows->links() }}</div>
@endsection
