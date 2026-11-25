<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#1B365D">
    <title>Masuk — Energy Desk</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="auth-page">
    <main class="auth-layout">
        <a class="brand" href="{{ route('home') }}">
            <span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 32 32" fill="none"><path d="M17.5 3.5 7 18h7l-.5 10.5L25 13h-7l-.5-9.5Z" fill="currentColor"/></svg></span>
            <span class="brand-copy"><strong>energi<span>desk</span></strong><small>INDONESIA ENERGY DATA</small></span>
        </a>
        <section class="auth-card">
            <span class="section-kicker"><span>AKUN ENERGY DESK</span></span>
            <h1>Selamat datang kembali.</h1>
            <p>Masuk untuk mengelola profil dan catatan energi milikmu.</p>
            <form method="POST" action="{{ route('login.store') }}" class="portal-form">
                @csrf
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                @error('email')<span class="field-error">{{ $message }}</span>@enderror
                <label for="password">Password</label>
                <input id="password" type="password" name="password" required autocomplete="current-password">
                @error('password')<span class="field-error">{{ $message }}</span>@enderror
                <label class="checkbox-label"><input type="checkbox" name="remember" value="1"> Ingat saya di perangkat ini</label>
                <button class="button button-primary form-submit" type="submit">Masuk <span>→</span></button>
            </form>
            <div class="auth-switch">Belum punya akun? <a href="{{ route('register') }}">Daftar di sini</a></div>
        </section>
        <a class="auth-back" href="{{ route('home') }}">← Kembali ke dashboard energi</a>
    </main>
</body>
</html>
