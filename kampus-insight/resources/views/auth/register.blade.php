<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#1B365D">
    <title>Buat akun — Energy Desk</title>
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
            <h1>Buat akunmu.</h1>
            <p>Simpan profil dan catatan analisis energi secara pribadi.</p>
            <form method="POST" action="{{ route('register.store') }}" class="portal-form">
                @csrf
                <label for="name">Nama</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required maxlength="255" autocomplete="name" autofocus>
                @error('name')<span class="field-error">{{ $message }}</span>@enderror
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email">
                @error('email')<span class="field-error">{{ $message }}</span>@enderror
                <label for="password">Password</label>
                <input id="password" type="password" name="password" required minlength="8" autocomplete="new-password">
                @error('password')<span class="field-error">{{ $message }}</span>@enderror
                <label for="password_confirmation">Ulangi password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required minlength="8" autocomplete="new-password">
                <button class="button button-primary form-submit" type="submit">Buat akun <span>→</span></button>
            </form>
            <div class="auth-switch">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></div>
        </section>
        <a class="auth-back" href="{{ route('home') }}">← Kembali ke dashboard energi</a>
    </main>
</body>
</html>
