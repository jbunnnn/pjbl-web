<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#1B365D">
    <meta name="description" content="Dashboard energi Indonesia: pantau tarif listrik PLN dan harga BBM bersubsidi, lalu olah datanya dalam satu tempat.">
    <title>Energi Indonesia — Energy Desk</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body>
    <header class="site-header">
        <div class="header-inner">
            <a class="brand" href="#" aria-label="Energi Desk, beranda">
                <span class="brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 32 32" fill="none">
                        <path d="M17.5 3.5 7 18h7l-.5 10.5L25 13h-7l-.5-9.5Z" fill="currentColor"/>
                    </svg>
                </span>
                <span class="brand-copy"><strong>energi<span>desk</span></strong><small>INDONESIA ENERGY DATA</small></span>
            </a>
            <button class="menu-toggle" type="button" aria-label="Buka navigasi" aria-expanded="false" data-menu-toggle>
                <span></span><span></span><span></span>
            </button>
            <nav class="main-nav" data-main-nav aria-label="Navigasi utama">
                <a href="#dashboard">Dashboard</a>
                <a href="#data">Data energi</a>
                <a href="#files">File studio</a>
                @auth
                    <a href="{{ route('profile.edit') }}">Profil</a>
                    <a href="{{ route('profile.notes.index') }}">Catatan saya</a>
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.guides.index') }}">Kelola konten</a>
                    @endif
                @else
                    <a href="{{ route('login') }}">Masuk</a>
                    <a href="{{ route('register') }}">Daftar</a>
                @endauth
                <a class="nav-cta" href="https://docs.apiindonesia.id/#energi" target="_blank" rel="noreferrer">Dokumentasi API <span aria-hidden="true">↗</span></a>
            </nav>
        </div>
    </header>

    <main>
        <section class="hero" id="dashboard">
            <div class="hero-grid" aria-hidden="true"></div>
            <div class="page-shell hero-inner">
                <div class="hero-copy">
                    <div class="eyebrow"><span class="live-pulse"></span> DATA ENERGI NASIONAL <span class="eyebrow-separator">/</span> API INDONESIA</div>
                    <h1>Energi Indonesia,<br><span>lebih mudah dipahami.</span></h1>
                    <p>Pantau tarif listrik PLN dan harga BBM bersubsidi dari satu dashboard. Jelajahi data, bandingkan harga, lalu unduh laporan sesuai kebutuhanmu.</p>
                    <div class="hero-actions">
                        <a class="button button-primary" href="#data">Jelajahi data <span aria-hidden="true">↓</span></a>
                        <a class="hero-doc-link" href="https://docs.apiindonesia.id/#energi" target="_blank" rel="noreferrer">Tentang sumber data <span aria-hidden="true">↗</span></a>
                    </div>
                    <div class="hero-source"><span class="source-seal">API</span><span>Data regulasi dan keputusan Kementerian ESDM<small>Disediakan melalui API Indonesia</small></span></div>
                </div>
                <div class="hero-art" aria-label="Ikhtisar data energi">
                    <div class="hero-orbit orbit-a"></div>
                    <div class="hero-orbit orbit-b"></div>
                    <div class="energy-illustration">
                        <div class="illustration-top"><span><i></i><i></i><i></i></span><small>energy-overview.json</small><span class="live-chip"><i></i> LIVE</span></div>
                        <div class="illustration-main">
                            <span class="illustration-label">RINGKASAN ENERGI</span>
                            <strong>Data untuk<br>keputusan lebih baik.</strong>
                            <div class="illustration-bars"><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i></div>
                            <div class="illustration-legend"><span><i></i> Listrik PLN</span><span><i></i> BBM subsidi</span></div>
                        </div>
                        <div class="illustration-bottom"><span>SUMBER TERVERIFIKASI</span><span>INDONESIA&nbsp; ↗</span></div>
                    </div>
                    <span class="float-label label-pln"><i>ϟ</i> Tarif listrik</span>
                    <span class="float-label label-fuel"><i>◉</i> Harga BBM</span>
                    <span class="hero-sparkle sparkle-a">✳</span>
                    <span class="hero-sparkle sparkle-b">✦</span>
                </div>
            </div>
            <div class="page-shell hero-footer"><span>TRANSPARAN · TERHUBUNG · SIAP DIOLAH</span><span>SUMBER TERBUKA, INTEGRASI API</span></div>
        </section>

        <section class="page-shell dashboard-section" id="data">
            <div class="section-heading">
                <div>
                    <div class="section-kicker"><span>01</span> DASHBOARD ENERGI</div>
                    <h2>Data terbaru, <span>langsung dari sumber.</span></h2>
                </div>
                <button class="button button-outline" type="button" data-refresh><span class="refresh-icon">↻</span> Perbarui data</button>
            </div>

            <div class="energy-tabs" role="tablist" aria-label="Pilih data energi">
                <button class="energy-tab is-active" type="button" role="tab" aria-selected="true" data-energy-tab="tarif-listrik"><span class="tab-icon">ϟ</span> Tarif listrik PLN</button>
                <button class="energy-tab" type="button" role="tab" aria-selected="false" data-energy-tab="bbm"><span class="tab-icon">◉</span> BBM bersubsidi</button>
                <span class="tab-date" data-as-of>Memuat tanggal data...</span>
            </div>

            <div class="energy-status" role="status" aria-live="polite" data-energy-status>
                <span class="status-spinner" aria-hidden="true"></span>
                <span>Menghubungkan ke API Indonesia...</span>
            </div>

            <div class="dashboard-content" data-energy-content hidden>
                <div class="summary-grid">
                    <article class="summary-card">
                        <div class="summary-label"><span data-summary-title>GOLONGAN TARIF</span><span class="summary-icon icon-electric">ϟ</span></div>
                        <strong data-summary-count>—</strong>
                        <span class="summary-foot" data-summary-count-label>kelas tarif terdata</span>
                    </article>
                    <article class="summary-card">
                        <div class="summary-label"><span>HARGA TERENDAH</span><span class="summary-icon icon-price">↓</span></div>
                        <strong data-summary-lowest>—</strong>
                        <span class="summary-foot" data-summary-lowest-label>tarif per kWh</span>
                    </article>
                    <article class="summary-card summary-card-wide">
                        <div class="summary-label"><span>CAKUPAN DATA</span><span class="summary-icon icon-coverage">▦</span></div>
                        <strong data-summary-coverage>—</strong>
                        <span class="summary-foot" data-summary-coverage-label>kategori pelanggan</span>
                    </article>
                </div>

                <div class="analysis-grid">
                    <article class="panel chart-panel">
                        <div class="panel-heading">
                            <div><span class="panel-eyebrow">VISUALISASI</span><h3 data-chart-title>Perbandingan tarif listrik</h3></div>
                            <span class="panel-badge"><i></i> Berdasarkan data API</span>
                        </div>
                        <div class="chart-wrap">
                            <svg data-energy-chart viewBox="0 0 760 250" role="img" aria-label="Grafik perbandingan data energi"></svg>
                        </div>
                        <div class="chart-legend"><span><i></i><span data-chart-legend>Tarif listrik per kWh</span></span><span>Nilai dalam rupiah</span></div>
                    </article>
                    <aside class="panel source-panel">
                        <div class="panel-heading"><div><span class="panel-eyebrow">TENTANG DATA</span><h3>Sumber tepercaya</h3></div><span class="source-icon-large">↗</span></div>
                        <p>Data tarif dan harga energi dikumpulkan dari regulasi serta keputusan resmi Kementerian ESDM.</p>
                        <div class="source-detail"><span class="source-detail-icon">✓</span><span><strong>API Indonesia</strong><small>Agregator data publik Indonesia</small></span></div>
                        <div class="source-detail"><span class="source-detail-icon source-document">▤</span><span><strong>Dasar hukum tersedia</strong><small>Arahkan ke nama data pada tabel untuk detail</small></span></div>
                        <a class="source-link" href="https://docs.apiindonesia.id/#energi" target="_blank" rel="noreferrer">Dokumentasi dan atribusi API <span>↗</span></a>
                    </aside>
                </div>

                <article class="panel data-panel">
                    <div class="data-panel-head">
                        <div><span class="panel-eyebrow">DATASET</span><h3 data-table-title>Tarif listrik yang berlaku</h3></div>
                        <div class="data-actions">
                            <button class="tool-button" type="button" data-export-excel title="Unduh workbook Excel"><span>↓</span> Ekspor Excel</button>
                            <button class="tool-button" type="button" data-download-template title="Unduh template Excel"><span>▤</span> Template</button>
                            <button class="tool-button" type="button" data-print-report title="Cetak atau simpan sebagai PDF"><span>▤</span> Simpan PDF</button>
                            <button class="tool-button button-import" type="button" data-import-trigger><span>↑</span> Impor Excel</button>
                            <input type="file" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" data-xlsx-file hidden>
                        </div>
                    </div>
                    <div class="table-toolbar">
                        <label class="table-search">
                            <svg viewBox="0 0 20 20" aria-hidden="true"><circle cx="8.5" cy="8.5" r="5.5"></circle><path d="m13 13 4 4"></path></svg>
                            <input type="search" placeholder="Cari golongan, produk, atau kategori..." aria-label="Cari data energi" data-table-search>
                        </label>
                        <label class="select-filter"><span>Kategori</span>
                            <select aria-label="Filter berdasarkan kategori" data-category-filter><option value="">Semua kategori</option></select>
                        </label>
                        <span class="table-count" data-table-count></span>
                    </div>
                    <div class="table-scroll">
                        <table class="energy-table">
                            <thead data-table-head></thead>
                            <tbody data-table-body></tbody>
                        </table>
                        <div class="table-empty" data-table-empty hidden>
                            <span>⌕</span><strong>Tidak ada data yang cocok</strong><p>Ubah filter pencarian atau muat ulang dataset.</p>
                        </div>
                    </div>
                    <div class="table-footer"><span data-page-summary></span><div class="pagination"><button type="button" data-page-prev aria-label="Halaman sebelumnya">←</button><span data-page-number></span><button type="button" data-page-next aria-label="Halaman berikutnya">→</button></div></div>
                    <div class="data-attribution" data-attribution>Data dan atribusi disediakan oleh API Indonesia. Tarif dapat berubah mengikuti kebijakan yang berlaku.</div>
                    <div class="import-notice" data-import-notice hidden><span>◷</span> Menampilkan file lokal: <strong data-import-name></strong><button type="button" data-restore-api>Kembali ke data API</button></div>
                    <div class="workflow-status" role="status" aria-live="polite" data-workflow-status hidden></div>
                </article>
            </div>
        </section>

        <section class="guides-section" id="guides">
            <div class="page-shell">
                <div class="section-heading guides-heading">
                    <div>
                        <div class="section-kicker"><span>02</span> PANDUAN &amp; PEMBARUAN</div>
                        <h2>Insight energi dari <span>komunitas.</span></h2>
                    </div>
                    <p>Panduan singkat dan pembaruan yang diterbitkan oleh administrator.</p>
                </div>
                <div class="guides-grid">
                    @forelse ($articles as $article)
                        <a class="guide-card" href="{{ route('guides.show', $article->slug) }}">
                            <span class="guide-label">PANDUAN ENERGI <span aria-hidden="true">↗</span></span>
                            <h3>{{ $article->title }}</h3>
                            <p>{{ $article->excerpt ?: \Illuminate\Support\Str::limit($article->content, 130) }}</p>
                            <span class="guide-meta">{{ $article->published_at?->translatedFormat('d F Y') }}</span>
                        </a>
                    @empty
                        <div class="guides-empty">
                            <span class="guide-empty-icon">↗</span>
                            <div><strong>Belum ada panduan yang diterbitkan</strong><p>Pembaruan energi dari administrator akan muncul di sini.</p></div>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="file-studio-section" id="files">
            <div class="page-shell">
                <div class="section-heading file-heading">
                    <div>
                        <div class="section-kicker"><span>03</span> OLAH DATA &amp; DOKUMEN</div>
                        <h2>Dari data mentah jadi <span>siap digunakan.</span></h2>
                    </div>
                    <p>Unduh dataset untuk spreadsheet, susun laporan PDF, dan catat insight pentingmu—semuanya di browser.</p>
                </div>
                <div class="file-tools-grid">
                    <article class="file-tool-card excel-card">
                        <div class="file-tool-top"><span class="file-icon excel-icon">X</span><span class="file-tool-step">DATA → SPREADSHEET</span></div>
                        <h3>File Excel</h3>
                        <p>Ekspor data sebagai workbook Excel, impor file .xlsx, dan unduh template dengan kolom sesuai dataset yang sedang dipilih.</p>
                        <label class="file-drop" data-drop-zone="xlsx">
                            <input type="file" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" data-xlsx-drop>
                            <span class="drop-icon">↑</span>
                            <span><strong>Pilih file Excel (.xlsx)</strong><small>atau seret file ke sini · maks. 5 MB</small></span>
                            <span class="drop-browse">Jelajahi</span>
                        </label>
                        <button class="button button-light button-small" type="button" data-download-template>Unduh template Excel</button>
                        <span class="file-tool-foot">Baris yang identik dihapus otomatis. File diproses lokal dan tidak diunggah.</span>
                    </article>
                    <article class="file-tool-card pdf-card">
                        <div class="file-tool-top"><span class="file-icon pdf-icon">PDF</span><span class="file-tool-step">DATA → LAPORAN</span></div>
                        <h3>Laporan &amp; dokumen PDF</h3>
                        <p>Simpan tabel sebagai PDF lewat fitur cetak browser, atau buka dokumen PDF lokal untuk melihatnya tanpa mengunggah.</p>
                        <div class="pdf-actions">
                            <button class="button button-primary button-small" type="button" data-pdf-report><span>▤</span> Buat laporan PDF</button>
                            <label class="button button-light button-small pdf-upload-label"><span>↑</span> Buka PDF<input type="file" accept="application/pdf,.pdf" data-pdf-file hidden></label>
                        </div>
                        <div class="pdf-preview" data-pdf-preview hidden>
                            <div><span class="pdf-file-mark">PDF</span><span><strong data-pdf-name></strong><small>Pratinjau lokal · file tidak diunggah</small></span><button type="button" aria-label="Tutup pratinjau PDF" data-pdf-close>×</button></div>
                            <iframe title="Pratinjau file PDF" data-pdf-frame></iframe>
                        </div>
                        <span class="file-tool-foot">File PDF dibuka secara lokal dan tidak dikirim ke server.</span>
                    </article>
                    <article class="file-tool-card notes-card">
                        <div class="file-tool-top"><span class="file-icon notes-icon">✎</span><span class="file-tool-step">INSIGHT → CATATAN</span></div>
                        <h3>Catatan analisis</h3>
                        <p>Simpan observasi tentang perubahan tarif atau hasil perbandingan. Catatan tersimpan di browser ini.</p>
                        <div class="editor-toolbar" role="toolbar" aria-label="Pemformatan catatan">
                            <button type="button" data-format="bold" aria-label="Tebalkan"><strong>B</strong></button>
                            <button type="button" data-format="italic" aria-label="Miring"><em>I</em></button>
                            <span></span>
                            <button type="button" data-format="insertUnorderedList" aria-label="Daftar berpoin">• List</button>
                            <span class="save-state" data-note-state>Tersimpan di perangkat ini</span>
                        </div>
                        <div class="note-editor" contenteditable="true" role="textbox" aria-label="Catatan analisis energi" aria-multiline="true" data-note-editor></div>
                        <span class="file-tool-foot">Catatan tidak dikirim ke API atau disimpan di server.</span>
                    </article>
                </div>
                <div class="privacy-note"><span>⌑</span> File dan catatan diproses di browser. Hanya permintaan data energi yang dikirim ke API Indonesia.</div>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="page-shell footer-inner">
            <a class="brand" href="#" aria-label="Energi Desk, kembali ke atas">
                <span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 32 32" fill="none"><path d="M17.5 3.5 7 18h7l-.5 10.5L25 13h-7l-.5-9.5Z" fill="currentColor"/></svg></span>
                <span class="brand-copy"><strong>energi<span>desk</span></strong><small>INDONESIA ENERGY DATA</small></span>
            </a>
            <p>Data publik energi Indonesia, terhubung dan mudah diolah.</p>
            <a class="footer-top" href="#" aria-label="Kembali ke atas">↑</a>
            <div class="footer-bottom"><span>© {{ date('Y') }} Energy Desk</span><a href="https://apiindonesia.id/produk/energi/" target="_blank" rel="noreferrer">Atribusi: API Indonesia · Sumber regulasi ESDM ↗</a></div>
        </div>
    </footer>
</body>
</html>
