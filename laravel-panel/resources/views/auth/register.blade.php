<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#05070b">
    <title>Create account · {{ config('app.name', 'Snck Panel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="snck-app">
<main class="snck-auth">
    <section class="snck-auth-main" style="grid-column:1 / -1;">
        <div class="snck-auth-card">
            <div class="snck-auth-head">
                <span class="snck-mark snck-mark-sm">
                    <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" fill="currentColor" d="M50 97 C42 73 29 55 9 41 C21 30 34 28 45 38 L50 25 L55 38 C66 28 79 30 91 41 C71 55 58 73 50 97 Z M50 45 L60 57.5 L50 70 L40 57.5 Z"/></svg>
                </span>
                <div>
                    <p class="snck-eyebrow">Snck hosting panel</p>
                    <h1>Create account</h1>
                    <p>Join the panel and start deploying servers.</p>
                </div>
            </div>
            @if ($errors->any())
                <div class="snck-alert-error" role="alert">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('register.store') }}" class="snck-auth-form">
                @csrf
                <label class="snck-field">
                    <span>Username</span>
                    <input name="username" value="{{ old('username') }}" required autocomplete="username" class="snck-input" placeholder="your-name">
                </label>
                <label class="snck-field">
                    <span>Email (optional)</span>
                    <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" class="snck-input" placeholder="you@example.com">
                </label>
                <label class="snck-field">
                    <span>Password</span>
                    <input type="password" name="password" required autocomplete="new-password" class="snck-input" placeholder="••••••••">
                </label>
                <label class="snck-field">
                    <span>Confirm password</span>
                    <input type="password" name="password_confirmation" required autocomplete="new-password" class="snck-input" placeholder="••••••••">
                </label>
                <button type="submit" class="snck-btn-primary">Create account</button>
            </form>
            <p class="snck-auth-alt"><a href="{{ route('login') }}">Return to sign in</a></p>
        </div>
    </section>
</main>
</body>
</html>
