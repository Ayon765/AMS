@extends('layouts.app')
@section('title', 'Role')
@section('content')
@php $selected = old('permissions', $role->permissions ?? []); @endphp
<h1 class="text-xl font-bold mb-3">{{ $role->exists ? 'Edit' : 'New' }} Role</h1>
<form method="POST" action="{{ $role->exists ? route('roles.update', $role) : route('roles.store') }}" class="card space-y-4">
    @csrf
    @if ($role->exists) @method('PUT') @endif
    <div class="max-w-sm"><label class="lbl mb-1">Role Name *</label><input name="name" value="{{ old('name', $role->name) }}" required class="inp"></div>
    <label class="flex items-center gap-2"><input type="checkbox" name="is_admin" value="1" @checked(old('is_admin', $role->is_admin))> Administrator (full access to everything)</label>

    <div class="grid md:grid-cols-3 gap-4">
        @foreach ($groups as $title => $perms)
            <div class="border rounded p-3">
                <div class="font-semibold mb-1">{{ $title }}</div>
                @foreach ($perms as $key => $label)
                    <label class="flex items-center gap-2 py-0.5"><input type="checkbox" name="permissions[]" value="{{ $key }}" @checked(in_array($key, $selected))> {{ $label }}</label>
                @endforeach
            </div>
        @endforeach
    </div>
    <div class="flex gap-2"><button class="btn">Save</button><a href="{{ route('roles.index') }}" class="btn-sec">Cancel</a></div>
</form>
@endsection
