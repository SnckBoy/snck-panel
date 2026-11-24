<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#05070b">
    <title>Sign in · {{ config('app.name', 'Snck Panel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="snck-app">
<main class="snck-auth">
    <section class="snck-auth-hero" aria-hidden="true">
        <div class="snck-auth-blob snck-auth-blob-a"></div>
        <div class="snck-auth-blob snck-auth-blob-b"></div>
        <div class="snck-auth-hero-top">
            <span class="snck-mark" style="width:44px;height:44px;">
                <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" fill="currentColor" d="M50 97 C42 73 29 55 9 41 C21 30 34 28 45 38 L50 25 L55 38 C66 28 79 30 91 41 C71 55 58 73 50 97 Z M50 45 L60 57.5 L50 70 L40 57.5 Z"/></svg>
            </span>
            <p class="snck-brand" style="margin-top:14px;">SNCK <span>PANEL</span></p>
        </div>
        <div class="snck-auth-hero-body">
            <p class="snck-eyebrow">Minecraft hosting · control center</p>
            <h1>Command your whole fleet from one dark console.</h1>
            <ul class="snck-auth-points">
                <li><span><strong>Servers and nodes</strong> — lifecycle, console, files and backups in a couple of clicks.</span></li>
                <li><span><strong>Built for owners</strong> — roles, quotas and audit logs that stay out of your way.</span></li>
                <li><span><strong>Fast by default</strong> — a clean, dark interface tuned for long admin sessions.</span></li>
            </ul>
        </div>
        <p class="snck-auth-hero-foot snck-auth-foot">Self-hosted · {{ config('app.name', 'Snck Panel') }}</p>
    </section>

    <section class="snck-auth-main">
        <div class="snck-auth-card">
            <div class="snck-auth-head">
                <span class="snck-mark snck-mark-sm">
                    <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" fill="currentColor" d="M50 97 C42 73 29 55 9 41 C21 30 34 28 45 38 L50 25 L55 38 C66 28 79 30 91 41 C71 55 58 73 50 97 Z M50 45 L60 57.5 L50 70 L40 57.5 Z"/></svg>
                </span>
                <div>
                    <p class="snck-eyebrow">Snck hosting panel</p>
                    <h1>Welcome back</h1>
                    <p>Sign in with your username or email.</p>
                </div>
            </div>
            @if ($errors->any())
                <div class="snck-alert-error" role="alert">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('login.store') }}" class="snck-auth-form">
                @csrf
                <label class="snck-field">
                    <span>Username or email</span>
                    <input name="identity" value="{{ old('identity') }}" required autofocus autocomplete="username" class="snck-input" placeholder="you@example.com">
                </label>
                <label class="snck-field">
                    <span>Password</span>
                    <input type="password" name="password" required autocomplete="current-password" class="snck-input" placeholder="••••••••">
                </label>
                <div class="snck-auth-row">
                    <label class="snck-check">
                        <input type="checkbox" name="remember" value="1">
                        <span>Remember me</span>
                    </label>
                </div>
                <button type="submit" class="snck-btn-primary">Sign in</button>
            </form>
            @if (config('services.google.client_id') && config('services.google.client_secret'))
                <a href="{{ route('google.redirect') }}" class="snck-btn-ghost">Continue with Google</a>
            @endif
            @if ($registrationEnabled ?? false)
                <p class="snck-auth-alt">Need an account? <a href="{{ route('register') }}">Register</a></p>
            @endif
        </div>
    </section>
</main>
</body>
</html>
