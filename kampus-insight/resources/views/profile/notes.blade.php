@extends('layouts.portal')

@section('title', 'Catatan Saya')

@section('content')
    <div class="portal-shell">
        <div class="portal-heading">
            <div>
                <span class="section-kicker"><span>RUANG PRIBADI</span></span>
                <h1>Catatan analisis</h1>
                <p>Catatan ini hanya dapat dilihat dan dikelola oleh akunmu.</p>
            </div>
            <a class="button button-outline" href="{{ route('profile.edit') }}">← Profil</a>
        </div>

        <div class="portal-columns notes-columns">
            <section class="portal-card">
                <h2>Buat catatan</h2>
                <p class="portal-card-intro">Simpan ide atau hasil pengamatan dari data energi.</p>
                <form method="POST" action="{{ route('profile.notes.store') }}" class="portal-form">
                    @csrf
                    <label for="new-title">Judul</label>
                    <input id="new-title" type="text" name="title" value="{{ old('title') }}" required maxlength="120">
                    @error('title')<span class="field-error">{{ $message }}</span>@enderror
                    <label for="new-content">Catatan</label>
                    <textarea id="new-content" name="content" rows="7" required maxlength="10000">{{ old('content') }}</textarea>
                    @error('content')<span class="field-error">{{ $message }}</span>@enderror
                    <button class="button button-primary" type="submit">Simpan catatan</button>
                </form>
            </section>

            <section class="notes-list">
                <div class="notes-list-heading"><h2>Catatan tersimpan</h2><span>{{ $notes->total() }} catatan</span></div>
                @forelse ($notes as $note)
                    <article class="portal-card saved-note">
                        <form method="POST" action="{{ route('profile.notes.update', $note) }}" class="portal-form">
                            @csrf
                            @method('PUT')
                            <label for="note-title-{{ $note->id }}">Judul</label>
                            <input id="note-title-{{ $note->id }}" type="text" name="title" value="{{ $note->title }}" required maxlength="120">
                            <label for="note-content-{{ $note->id }}">Catatan</label>
                            <textarea id="note-content-{{ $note->id }}" name="content" rows="5" required maxlength="10000">{{ $note->content }}</textarea>
                            <div class="note-actions">
                                <span>Diperbarui {{ $note->updated_at->format('d M Y, H:i') }}</span>
                                <button class="button button-outline button-compact" type="submit">Perbarui</button>
                            </div>
                        </form>
                        <form method="POST" action="{{ route('profile.notes.destroy', $note) }}" class="delete-form" onsubmit="return confirm('Hapus catatan ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-link">Hapus catatan</button>
                        </form>
                    </article>
                @empty
                    <div class="portal-card notes-empty"><span>✎</span><strong>Belum ada catatan</strong><p>Catatan yang kamu buat akan muncul di sini.</p></div>
                @endforelse
                {{ $notes->links() }}
            </section>
        </div>
    </div>
@endsection
