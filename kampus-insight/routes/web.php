<?php

use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\AdminMiddleware;
use App\Models\Article;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

Route::get('/', function (): View {
    $articles = Article::query()
        ->where('status', 'published')
        ->latest('published_at')
        ->limit(3)
        ->get();

    return view('welcome', ['articles' => $articles]);
})->name('home');

Route::get('/guides/{article:slug}', function (Article $article): View {
    abort_unless($article->status === 'published', 404);

    return view('guides.show', ['article' => $article]);
})->name('guides.show');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'createLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1')->name('login.store');
    Route::get('/register', [AuthController::class, 'createRegistration'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:6,1')->name('register.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/notes', [NoteController::class, 'index'])->name('profile.notes.index');
    Route::post('/profile/notes', [NoteController::class, 'store'])->name('profile.notes.store');
    Route::put('/profile/notes/{note}', [NoteController::class, 'update'])->name('profile.notes.update');
    Route::delete('/profile/notes/{note}', [NoteController::class, 'destroy'])->name('profile.notes.destroy');
});

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', AdminMiddleware::class])
    ->group(function (): void {
        Route::resource('guides', AdminArticleController::class)->except('show');
    });

$proxyEnergyEndpoint = static function (string $endpoint) {
    return static function () use ($endpoint) {
        $apiKey = config('services.api_indonesia.key');

        if (blank($apiKey)) {
            return response()->json([
                'message' => 'API Indonesia belum dikonfigurasi untuk data energi.',
            ], 503);
        }

        try {
            $response = Http::acceptJson()
                ->withHeaders(['x-api-key' => $apiKey])
                ->connectTimeout(3)
                ->timeout(12)
                ->get("https://use.apiindonesia.id/api/v1/energi/{$endpoint}");
        } catch (ConnectionException) {
            return response()->json([
                'message' => 'API Indonesia tidak dapat dijangkau untuk data energi.',
            ], 502);
        }

        if (! $response->successful()) {
            return response()->json([
                'message' => "API Indonesia gagal memberikan data energi (HTTP {$response->status()}).",
            ], $response->status());
        }

        $payload = $response->json();

        if (! is_array($payload)) {
            return response()->json([
                'message' => 'API Indonesia memberikan respons yang tidak valid.',
            ], 502);
        }

        return response()->json($payload);
    };
};

Route::get('/api/energi/tarif-listrik', $proxyEnergyEndpoint('tarif-listrik'))
    ->name('api.energi.tarif-listrik');

Route::get('/api/energi/bbm', $proxyEnergyEndpoint('bbm'))
    ->name('api.energi.bbm');
