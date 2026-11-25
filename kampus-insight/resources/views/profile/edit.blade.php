@extends('layouts.portal')

@section('title', 'Profil Saya')

@section('content')
    <div class="portal-shell">
        <div class="portal-heading">
            <div>
                <span class="section-kicker"><span>AKUN SAYA</span></span>
                <h1>Profil &amp; keamanan</h1>
                <p>Kelola informasi akun dan akses fitur sesuai peranmu.</p>
            </div>
            <span class="role-badge">{{ $user->isAdmin() ? 'Administrator' : 'Pengguna' }}</span>
        </div>

        <div class="portal-columns">
            <section class="portal-card">
                <h2>Informasi profil</h2>
                <p class="portal-card-intro">Nama dan email yang digunakan untuk akun ini.</p>
                <form method="POST" action="{{ route('profile.update') }}" class="portal-form">
                    @csrf
                    @method('PUT')
                    <label for="name">Nama</label>
                    <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required maxlength="255" autocomplete="name">
                    @error('name')<span class="field-error">{{ $message }}</span>@enderror
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required maxlength="255" autocomplete="email">
                    @error('email')<span class="field-error">{{ $message }}</span>@enderror

                    <div class="form-divider"></div>
                    <h3>Ganti password <span>(opsional)</span></h3>
                    <label for="current_password">Password saat ini</label>
                    <input id="current_password" type="password" name="current_password" autocomplete="current-password">
                    @error('current_password')<span class="field-error">{{ $message }}</span>@enderror
                    <label for="password">Password baru</label>
                    <input id="password" type="password" name="password" minlength="8" autocomplete="new-password">
                    @error('password')<span class="field-error">{{ $message }}</span>@enderror
                    <label for="password_confirmation">Ulangi password baru</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" minlength="8" autocomplete="new-password">
                    <button class="button button-primary" type="submit">Simpan profil</button>
                </form>
            </section>

            <aside class="portal-card account-actions">
                <span class="account-avatar">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                <h2>{{ $user->name }}</h2>
                <p>{{ $user->email }}</p>
                <div class="account-action-list">
                    <a href="{{ route('profile.notes.index') }}"><span>✎</span><span><strong>Catatan pribadi</strong><small>Buat dan kelola catatan analisis energi</small></span><b>→</b></a>
                    @if ($user->isAdmin())
                        <a href="{{ route('admin.guides.index') }}"><span>▤</span><span><strong>Kelola panduan</strong><small>Terbitkan pengumuman dan artikel energi</small></span><b>→</b></a>
                    @endif
                </div>
            </aside>
        </div>
    </div>
@endsection
