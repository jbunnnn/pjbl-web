@extends('layouts.portal')

@section('title', $article->exists ? 'Ubah Panduan' : 'Tulis Panduan')

@section('content')
    <div class="portal-shell portal-shell-narrow">
        <div class="portal-heading">
            <div>
                <span class="section-kicker"><span>AREA ADMIN</span></span>
                <h1>{{ $article->exists ? 'Ubah panduan' : 'Tulis panduan energi' }}</h1>
                <p>Konten menggunakan teks biasa agar aman dan mudah dibaca.</p>
            </div>
            <a class="button button-outline" href="{{ route('admin.guides.index') }}">← Semua panduan</a>
        </div>
        <section class="portal-card">
            <form method="POST" action="{{ $article->exists ? route('admin.guides.update', $article) : route('admin.guides.store') }}" class="portal-form">
                @csrf
                @if ($article->exists)
                    @method('PUT')
                @endif
                <label for="title">Judul</label>
                <input id="title" type="text" name="title" value="{{ old('title', $article->title) }}" required maxlength="255">
                @error('title')<span class="field-error">{{ $message }}</span>@enderror
                <label for="slug">Slug URL <span>(opsional — dibuat otomatis jika kosong)</span></label>
                <input id="slug" type="text" name="slug" value="{{ old('slug', $article->slug) }}" maxlength="255">
                @error('slug')<span class="field-error">{{ $message }}</span>@enderror
                <label for="excerpt">Ringkasan <span>(opsional)</span></label>
                <textarea id="excerpt" name="excerpt" rows="3" maxlength="500">{{ old('excerpt', $article->excerpt) }}</textarea>
                @error('excerpt')<span class="field-error">{{ $message }}</span>@enderror
                <label for="content">Isi panduan</label>
                <textarea id="content" name="content" rows="14" required maxlength="30000">{{ old('content', $article->content) }}</textarea>
                @error('content')<span class="field-error">{{ $message }}</span>@enderror
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    <option value="draft" @selected(old('status', $article->status ?: 'draft') === 'draft')>Simpan sebagai draf</option>
                    <option value="published" @selected(old('status', $article->status ?: 'draft') === 'published')>Terbitkan di dashboard</option>
                </select>
                @error('status')<span class="field-error">{{ $message }}</span>@enderror
                <div class="form-actions">
                    <button class="button button-primary" type="submit">{{ $article->exists ? 'Simpan perubahan' : 'Simpan panduan' }}</button>
                    <a class="button button-outline" href="{{ route('admin.guides.index') }}">Batal</a>
                </div>
            </form>
        </section>
    </div>
@endsection
