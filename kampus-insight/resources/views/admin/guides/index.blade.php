@extends('layouts.portal')

@section('title', 'Kelola Panduan')

@section('content')
    <div class="portal-shell">
        <div class="portal-heading">
            <div>
                <span class="section-kicker"><span>AREA ADMIN</span></span>
                <h1>Panduan &amp; pembaruan energi</h1>
                <p>Buat dan terbitkan informasi untuk ditampilkan di dashboard publik.</p>
            </div>
            <a class="button button-primary" href="{{ route('admin.guides.create') }}">+ Tulis panduan</a>
        </div>
        <section class="portal-card admin-guide-list">
            @forelse ($articles as $article)
                <article class="admin-guide-row">
                    <div>
                        <span class="status-badge {{ $article->status === 'published' ? 'status-published' : 'status-draft' }}">{{ $article->status === 'published' ? 'Terbit' : 'Draf' }}</span>
                        <h2>{{ $article->title }}</h2>
                        <p>{{ $article->excerpt ?: \Illuminate\Support\Str::limit($article->content, 130) }}</p>
                        <span class="admin-guide-meta">Oleh {{ $article->user?->name ?? 'Admin' }} · Diubah {{ $article->updated_at->format('d M Y') }}</span>
                    </div>
                    <div class="admin-guide-actions">
                        <a class="button button-outline button-compact" href="{{ route('admin.guides.edit', $article) }}">Ubah</a>
                        @if ($article->status === 'published')
                            <a class="text-action" href="{{ route('guides.show', $article->slug) }}">Lihat ↗</a>
                        @endif
                        <form method="POST" action="{{ route('admin.guides.destroy', $article) }}" onsubmit="return confirm('Hapus panduan ini?')">
                            @csrf
                            @method('DELETE')
                            <button class="delete-link" type="submit">Hapus</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="notes-empty"><span>▤</span><strong>Belum ada panduan</strong><p>Tulis panduan untuk membagikan informasi energi kepada pengunjung.</p></div>
            @endforelse
            {{ $articles->links() }}
        </section>
    </div>
@endsection
