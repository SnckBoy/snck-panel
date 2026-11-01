<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create panel owner · {{ config('app.name', 'Snck Panel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
<main class="mx-auto flex min-h-screen max-w-xl items-center px-6 py-12">
    <section class="w-full rounded-2xl border border-slate-800 bg-slate-900/90 p-8 shadow-2xl">
        <p class="text-xs font-semibold uppercase tracking-[.22em] text-violet-300">Snck hosting panel</p>
        <h1 class="mt-3 text-3xl font-semibold">Create Owner account</h1>
        <p class="mt-2 text-sm text-slate-400">This secure setup page is available only until the first account is created.</p>
        @if ($errors->any())
            <div class="mt-5 rounded-lg border border-rose-500/30 bg-rose-500/10 p-3 text-sm text-rose-200">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ route('setup.store') }}" class="mt-7 space-y-5">
            @csrf
            <label class="block text-sm">Username<input name="username" value="{{ old('username') }}" required autofocus autocomplete="username" class="mt-2 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2.5 outline-none focus:border-violet-400"></label>
            <label class="block text-sm">Email<input type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="mt-2 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2.5 outline-none focus:border-violet-400"></label>
            <label class="block text-sm">Password<input type="password" name="password" required autocomplete="new-password" class="mt-2 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2.5 outline-none focus:border-violet-400"></label>
            <label class="block text-sm">Confirm password<input type="password" name="password_confirmation" required autocomplete="new-password" class="mt-2 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2.5 outline-none focus:border-violet-400"></label>
            <button class="w-full rounded-lg bg-violet-400 px-4 py-3 font-semibold text-slate-950 transition hover:bg-violet-300">Create Owner account</button>
        </form>
    </section>
</main>
</body>
</html>
