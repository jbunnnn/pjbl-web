<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(): View
    {
        return view('admin.guides.index', [
            'articles' => Article::query()->with('user')->latest('updated_at')->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('admin.guides.form', ['article' => new Article]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        $status = $validated['status'];
        $article = Article::create([
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'slug' => $this->uniqueSlug(($validated['slug'] ?? null) ?: $validated['title']),
            'excerpt' => $validated['excerpt'] ?? null,
            'content' => $validated['content'],
            'status' => $status,
            'published_at' => $status === 'published' ? now() : null,
        ]);

        return redirect()->route('admin.guides.edit', $article)
            ->with('status', 'Panduan energi berhasil dibuat.');
    }

    public function edit(Article $article): View
    {
        return view('admin.guides.form', ['article' => $article]);
    }

    public function update(Request $request, Article $article): RedirectResponse
    {
        $validated = $request->validate($this->rules($article));
        $status = $validated['status'];
        $article->update([
            'title' => $validated['title'],
            'slug' => $this->uniqueSlug(($validated['slug'] ?? null) ?: $validated['title'], $article),
            'excerpt' => $validated['excerpt'] ?? null,
            'content' => $validated['content'],
            'status' => $status,
            'published_at' => $status === 'published' ? ($article->published_at ?? now()) : null,
        ]);

        return back()->with('status', 'Panduan energi berhasil diperbarui.');
    }

    public function destroy(Article $article): RedirectResponse
    {
        $article->delete();

        return redirect()->route('admin.guides.index')
            ->with('status', 'Panduan energi berhasil dihapus.');
    }

    /**
     * @return array<string, array<int, string|Unique>>
     */
    private function rules(?Article $article = null): array
    {
        $slugRule = 'unique:articles,slug';

        if ($article !== null) {
            $slugRule .= ','.$article->id;
        }

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', $slugRule],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string', 'max:30000'],
            'status' => ['required', 'in:draft,published'],
        ];
    }

    private function uniqueSlug(string $value, ?Article $article = null): string
    {
        $baseSlug = Str::slug($value) ?: 'panduan-energi';
        $slug = $baseSlug;
        $suffix = 2;

        while (Article::query()
            ->where('slug', $slug)
            ->when($article !== null, fn ($query) => $query->where('id', '!=', $article->id))
            ->exists()) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
