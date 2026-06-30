<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\News;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminNewsController extends Controller
{
    public function index(): View
    {
        $news = News::with('user', 'animes')
            ->latest()
            ->paginate(20);

        return view('admin.news.index', compact('news'));
    }

    public function create(): View
    {
        $animes = Anime::orderBy('title')->get();

        return view('admin.news.create', compact('animes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'body'         => ['required', 'string'],
            'cover_image'  => ['nullable', 'image', 'max:2048'],
            'animes'       => ['nullable', 'array'],
            'animes.*'     => ['exists:animes,id'],
            'published_at' => ['nullable', 'date'],
        ]);

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('news', 'public');
        }

        $data['slug']    = Str::slug($data['title']);
        $data['user_id'] = auth()->id();

        $news = News::create($data);
        $news->animes()->sync($data['animes'] ?? []);

        return redirect()->route('admin.news.index')
            ->with('status', 'Noticia creada correctamente.');
    }

    public function show(News $news): RedirectResponse
    {
        return redirect()->route('news.show', $news);
    }

    public function edit(News $news): View
    {
        $animes = Anime::orderBy('title')->get();
        $news->load('animes');

        return view('admin.news.edit', compact('news', 'animes'));
    }

    public function update(Request $request, News $news): RedirectResponse
    {
        $data = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'body'         => ['required', 'string'],
            'cover_image'  => ['nullable', 'image', 'max:2048'],
            'animes'       => ['nullable', 'array'],
            'animes.*'     => ['exists:animes,id'],
            'published_at' => ['nullable', 'date'],
        ]);

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('news', 'public');
        }

        $data['slug'] = Str::slug($data['title']);

        $news->update($data);
        $news->animes()->sync($data['animes'] ?? []);

        return redirect()->route('admin.news.index')
            ->with('status', 'Noticia actualizada correctamente.');
    }

    public function destroy(News $news): RedirectResponse
    {
        $news->delete();

        return redirect()->route('admin.news.index')
            ->with('status', 'Noticia eliminada.');
    }
}