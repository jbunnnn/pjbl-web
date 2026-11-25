@extends('layouts.portal')

@section('title', $article->title)

@section('content')
    <article class="portal-shell portal-shell-narrow">
        <a class="guide-back-link" href="{{ route('home') }}#guides">← Kembali ke dashboard energi</a>
        <section class="portal-card guide-detail">
            <span class="section-kicker"><span>PANDUAN ENERGI</span></span>
            <h1>{{ $article->title }}</h1>
            <div class="guide-detail-meta">Diterbitkan {{ $article->published_at?->translatedFormat('d F Y') }}</div>
            @if ($article->excerpt)
                <p class="guide-detail-excerpt">{{ $article->excerpt }}</p>
            @endif
            <div class="guide-detail-content">{{ $article->content }}</div>
        </section>
    </article>
@endsection
