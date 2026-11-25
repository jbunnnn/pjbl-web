<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#1B365D">
    <title>@yield('title', 'Energy Desk') — Energy Desk</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="portal-body">
    <header class="site-header">
        <div class="header-inner">
            <a class="brand" href="{{ route('home') }}" aria-label="Energy Desk, beranda">
                <span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 32 32" fill="none"><path d="M17.5 3.5 7 18h7l-.5 10.5L25 13h-7l-.5-9.5Z" fill="currentColor"/></svg></span>
                <span class="brand-copy"><strong>energi<span>desk</span></strong><small>INDONESIA ENERGY DATA</small></span>
            </a>
            <nav class="portal-nav" aria-label="Navigasi akun">
                <a href="{{ route('home') }}">Dashboard energi</a>
                @auth
                    <a href="{{ route('profile.edit') }}">Profil</a>
                    <a href="{{ route('profile.notes.index') }}">Catatan</a>
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.guides.index') }}">Kelola panduan</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit">Keluar</button>
                    </form>
                @endauth
            </nav>
        </div>
    </header>
    <main class="portal-main">
        @if (session('status'))
            <div class="portal-flash" role="status">{{ session('status') }}</div>
        @endif
        @yield('content')
    </main>
    <footer class="portal-footer">Energy Desk · Informasi energi publik Indonesia</footer>
</body>
</html>
