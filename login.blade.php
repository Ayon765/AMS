<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4">
<form method="POST" action="{{ route('login') }}" class="bg-white p-6 rounded-lg shadow w-full max-w-sm space-y-4">
    @csrf
    <h1 class="text-xl font-bold text-center">📦 Assets Management System</h1>
    @if (session('ok'))<div class="p-2 bg-green-100 text-green-800 rounded text-sm">{{ session('ok') }}</div>@endif
    @if ($errors->any())<div class="p-2 bg-red-100 text-red-800 rounded text-sm">{{ $errors->first() }}</div>@endif
    <div><label class="block text-sm mb-1">Email</label>
        <input type="email" name="email" value="{{ old('email') }}" required autofocus class="w-full border rounded px-3 py-2"></div>
    <div><label class="block text-sm mb-1">Password</label>
        <input type="password" name="password" required class="w-full border rounded px-3 py-2"></div>
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remember" value="1"> Remember me</label>
    <button class="w-full bg-indigo-600 text-white py-2 rounded hover:bg-indigo-700">Login</button>
</form>
</body>
</html>
