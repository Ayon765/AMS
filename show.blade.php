@extends('layouts.app')
@section('title', $title)
@section('content')
@php $cur = \App\Models\Setting::val('currency', '৳'); @endphp
<div class="flex justify-between items-center mb-3 print:hidden">
    <h1 class="text-xl font-bold">{{ $title }}</h1>
    <button onclick="window.print()" class="btn-sec">🖨 Print</button>
</div>
<form class="flex flex-wrap gap-2 mb-3 items-end print:hidden">
    <div><label class="lbl">From</label><input type="date" name="from" value="{{ $from }}" class="inp"></div>
    <div><label class="lbl">To</label><input type="date" name="to" value="{{ $to }}" class="inp"></div>
    <div><label class="lbl">Branch</label><select name="branch_id" class="inp"><option value="">All</option>
        @foreach ($branches as $b)<option value="{{ $b->id }}" @selected($branch == $b->id)>{{ $b->name }}</option>@endforeach</select></div>
    @unless ($isCash)
        <div><label class="lbl">Status</label><select name="status" class="inp">
            @foreach (['approved', 'pending', 'rejected', 'all'] as $s)<option value="{{ $s }}" @selected($status === $s)>{{ ucfirst($s) }}</option>@endforeach</select></div>
    @endunless
    <button class="btn">Show</button>
</form>

<div class="card">
    <div class="text-center mb-3">
        <div class="font-bold text-lg">{{ \App\Models\Setting::val('company_name') }}</div>
        <div class="font-semibold">{{ $title }}</div>
        <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($from)->format('d M Y') }} — {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</div>
    </div>
    <table class="w-full">
        @if ($isCash)
            <thead><tr><th class="th">Date</th><th class="th">Branch</th><th class="th">Head</th><th class="th">Note</th><th class="th text-right">Amount</th></tr></thead>
            <tbody>
            @forelse ($rows as $r)
                <tr><td class="td">{{ $r->entry_date->format('d M Y') }}</td><td class="td">{{ $r->branch?->name ?? '—' }}</td><td class="td">{{ $r->head }}</td><td class="td">{{ $r->note }}</td><td class="td text-right">{{ number_format($r->amount, 2) }}</td></tr>
            @empty <tr><td colspan="5" class="p-4 text-center text-gray-500">No data.</td></tr> @endforelse
            </tbody>
            <tfoot><tr class="font-bold"><td colspan="4" class="td text-right">Total</td><td class="td text-right">{{ $cur }}{{ number_format($total, 2) }}</td></tr></tfoot>
        @else
            <thead><tr><th class="th">Date</th><th class="th">Ref No</th><th class="th">Branch</th><th class="th">Party</th><th class="th text-right">Items</th><th class="th">Status</th><th class="th text-right">Value</th></tr></thead>
            <tbody>
            @forelse ($rows as $r)
                <tr>
                    <td class="td">{{ $r->doc_date->format('d M Y') }}</td>
                    <td class="td"><a class="text-indigo-600 print:text-black" href="{{ route('doc.show', [$r->type, $r]) }}">{{ $r->ref_no }}</a></td>
                    <td class="td">{{ $r->branch->name }}</td><td class="td">{{ $r->party }}</td>
                    <td class="td text-right">{{ $r->items_count }}</td>
                    <td class="td">{{ ucfirst($r->status) }}</td>
                    <td class="td text-right">{{ number_format($r->total, 2) }}</td>
                </tr>
            @empty <tr><td colspan="7" class="p-4 text-center text-gray-500">No data.</td></tr> @endforelse
            </tbody>
            <tfoot><tr class="font-bold"><td colspan="6" class="td text-right">Total</td><td class="td text-right">{{ $cur }}{{ number_format($total, 2) }}</td></tr></tfoot>
        @endif
    </table>
</div>
@endsection
