@php
    $groups = \App\Support\Menu::visible(auth()->user());
    $co = \App\Models\Setting::val('company_name', 'Assets Management System');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') — {{ $co }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style type="text/tailwindcss">
        .btn{@@apply bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 inline-block text-sm cursor-pointer}
        .btn-sec{@@apply border px-4 py-2 rounded inline-block text-sm bg-white hover:bg-gray-50 cursor-pointer}
        .btn-red{@@apply bg-red-600 text-white px-3 py-1 rounded text-xs cursor-pointer}
        .btn-green{@@apply bg-green-600 text-white px-3 py-1 rounded text-xs cursor-pointer}
        .inp{@@apply w-full border rounded px-3 py-2 bg-white text-sm}
        .card{@@apply bg-white rounded shadow p-4}
        .th{@@apply p-2 text-left font-semibold bg-gray-100 whitespace-nowrap}
        .td{@@apply p-2 border-t}
        .lbl{@@apply block text-sm font-medium text-gray-700}
    </style>
</head>
<body class="bg-gray-100 text-gray-800 text-sm">
<div class="flex min-h-screen">
    <aside id="side" class="hidden md:block fixed md:sticky top-0 h-screen overflow-y-auto z-30 w-64 shrink-0 bg-slate-900 text-slate-200 print:hidden">
        <div class="p-4 font-bold text-white border-b border-slate-700">📦 {{ $co }}</div>
        <nav class="p-2 space-y-1">
            <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded hover:bg-slate-700 {{ request()->routeIs('dashboard') ? 'bg-slate-700 text-white' : '' }}">Dashboard</a>
            @foreach ($groups as $g)
                <details @if ($g['open']) open @endif>
                    <summary class="px-3 py-2 cursor-pointer hover:bg-slate-700 rounded font-medium">{{ $g['title'] }}</summary>
                    <div class="ml-3 border-l border-slate-700 mb-1">
                        @foreach ($g['items'] as $it)
                            <a href="{{ $it['url'] }}" class="block px-3 py-1.5 hover:text-white {{ $it['active'] ? 'text-white font-semibold' : 'text-slate-400' }}">{{ $it['label'] }}</a>
                        @endforeach
                    </div>
                </details>
            @endforeach
        </nav>
    </aside>

    <div class="flex-1 min-w-0">
        <header class="bg-white border-b px-4 py-3 flex items-center gap-3 print:hidden">
            <button type="button" class="md:hidden text-xl" onclick="document.getElementById('side').classList.toggle('hidden')">☰</button>
            <span class="ml-auto text-gray-600">{{ auth()->user()->name }} <span class="text-gray-400">({{ auth()->user()->role?->name }})</span></span>
            <form method="POST" action="{{ route('logout') }}">@csrf <button class="text-red-600 hover:underline">Logout</button></form>
        </header>
        <main class="p-4">
            @if (session('ok'))<div class="mb-3 p-3 rounded bg-green-100 text-green-800 print:hidden">{{ session('ok') }}</div>@endif
            @if ($errors->any())
                <div class="mb-3 p-3 rounded bg-red-100 text-red-800 print:hidden">
                    <ul class="list-disc ml-4">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
