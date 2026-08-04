<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MR Field App Login - Exponit Labs</title>

    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#2563eb">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="bg-slate-50 text-slate-900 min-h-screen flex items-center justify-center p-4 font-sans antialiased selection:bg-blue-600 selection:text-white">
    <div class="w-full max-w-md bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-6">

        <!-- Header -->
        <div class="text-center space-y-2">
            <div class="w-1/4">@include('filament.admin.logo')</div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">ELOS App</h1>
        </div>

        <!-- Validation Errors -->
        @if ($errors->any())
            <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium space-y-1">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <!-- Form -->
        <form method="POST" action="{{ route('mr.login.submit') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email"
                    class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Email
                    Address</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                    placeholder="Enter your email"
                    class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            </div>

            <div>
                <label for="password"
                    class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Password</label>
                <input type="password" name="password" id="password" required placeholder="••••••••"
                    class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            </div>

            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center space-x-2 text-slate-600">
                    <input type="checkbox" name="remember"
                        class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <span>Remember Me</span>
                </label>
            </div>

            <button type="submit"
                class="w-full py-3 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 font-semibold text-sm text-white shadow-sm transition-all flex items-center justify-center space-x-2">
                <span>Sign In</span>
            </button>
        </form>
    </div>
</body>

</html>
