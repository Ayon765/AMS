@extends('layouts.app')
@section('title', 'Company Profile')
@section('content')
<h1 class="text-xl font-bold mb-3">Company Profile</h1>
<form method="POST" action="{{ route('company.update') }}" class="card grid md:grid-cols-2 gap-4">
    @csrf @method('PUT')
    <div><label class="lbl">Company Name *</label><input name="company_name" value="{{ old('company_name', $s['company_name']) }}" required class="inp"></div>
    <div><label class="lbl">Currency Symbol</label><input name="currency" value="{{ old('currency', $s['currency']) }}" class="inp"></div>
    <div><label class="lbl">Phone</label><input name="phone" value="{{ old('phone', $s['phone']) }}" class="inp"></div>
    <div><label class="lbl">Email</label><input type="email" name="email" value="{{ old('email', $s['email']) }}" class="inp"></div>
    <div><label class="lbl">Website</label><input name="website" value="{{ old('website', $s['website']) }}" class="inp"></div>
    <div><label class="lbl">Address</label><input name="address" value="{{ old('address', $s['address']) }}" class="inp"></div>
    <div class="md:col-span-2"><label class="lbl">Invoice Footer Text</label><input name="invoice_footer" value="{{ old('invoice_footer', $s['invoice_footer']) }}" class="inp"></div>
    <div class="md:col-span-2"><button class="btn">Save</button></div>
</form>
@endsection
