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
    public function index(Request $request): View
    {
        $animes = Anime::orderBy('title')->get(['id', 'title', 'slug']);
        $currentAnime = $animes->firstWhere('slug', $request->query('anime'));

        $sortOptions = [
            'created_desc'   => ['label' => 'Creadas: más recientes',    'column' => 'created_at',   'direction' => 'desc'],
            'created_asc'    => ['label' => 'Creadas: más antiguas',     'column' => 'created_at',   'direction' => 'asc'],
            'published_desc' => ['label' => 'Publicación: más recientes', 'column' => 'published_at', 'direction' => 'desc'],
            'published_asc'  => ['label' => 'Publicación: más antiguas',  'column' => 'published_at', 'direction' => 'asc'],
        ];

        $sort = in_array($request->query('sort'), array_keys($sortOptions), true)
            ? $request->query('sort')
            : 'created_desc';

        $status = in_array($request->query('status'), ['published', 'scheduled', 'draft'], true)
            ? $request->query('status')
            : null;

        $news = News::with('user', 'animes')
            ->when($currentAnime, fn ($query, $anime) => $query->whereHas('animes', fn ($a) => $a->where('animes.id', $anime->id)))
            ->when($request->query('q'), fn ($query, $q) => $query->where('title', 'like', "%{$q}%"))
            ->when($status === 'published', fn ($query) => $query->published())
            ->when($status === 'scheduled', fn ($query) => $query->where('published_at', '>', now()))
            ->when($status === 'draft', fn ($query) => $query->whereNull('published_at'))
            ->orderBy($sortOptions[$sort]['column'], $sortOptions[$sort]['direction'])
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $sortLabels = collect($sortOptions)->map(fn ($option) => $option['label'])->all();

        return view('admin.news.index', compact('news', 'animes', 'currentAnime', 'sort', 'sortLabels', 'status'));
    }

    public function create(Request $request): View
    {
        $animes = Anime::orderBy('title')->get(['id', 'title', 'slug']);

        // ?anime=slug preselecciona el anime y, al guardar, regresa a su listado
        $preselected = $animes->firstWhere('slug', $request->query('anime'));
        $selectedAnimeIds = $preselected ? [$preselected->id] : [];
        $returnAnime = $preselected?->slug;

        return view('admin.news.create', compact('animes', 'selectedAnimeIds', 'returnAnime'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'body'         => ['required', 'string'],
            'cover_image'  => ['nullable', 'image', 'max:2048'],
            'animes'       => ['nullable', 'array'],
            'animes.*'     => ['nullable', 'distinct', 'exists:animes,id'],
            'published_at' => ['nullable', 'date'],
            'meta_title'       => ['nullable', 'string', 'max:70'],
            'meta_description' => ['nullable', 'string', 'max:200'],
        ]);

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('news', 'public');
        }

        $data['slug']    = Str::slug($data['title']);
        $data['user_id'] = auth()->id();

        $news = News::create($data);
        $news->animes()->sync($this->animeIds($data));

        return redirect()
            ->route('admin.news.index', array_filter(['anime' => $request->input('return_anime')]))
            ->with('status', 'Noticia creada correctamente.');
    }

    public function show(News $news): RedirectResponse
    {
        return redirect()->route('news.show', $news);
    }

    public function edit(News $news): View
    {
        $animes = Anime::orderBy('title')->get(['id', 'title', 'slug']);
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
            'animes.*'     => ['nullable', 'distinct', 'exists:animes,id'],
            'published_at' => ['nullable', 'date'],
            'meta_title'       => ['nullable', 'string', 'max:70'],
            'meta_description' => ['nullable', 'string', 'max:200'],
        ]);

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('news', 'public');
        }

        $data['slug'] = Str::slug($data['title']);

        $news->update($data);
        $news->animes()->sync($this->animeIds($data));

        return redirect()->route('admin.news.index')
            ->with('status', 'Noticia actualizada correctamente.');
    }

    public function destroy(News $news): RedirectResponse
    {
        $news->delete();

        return redirect()->route('admin.news.index')
            ->with('status', 'Noticia eliminada.');
    }

    /**
     * Ids de anime sin vacíos ni repetidos (los selects sin elegir llegan como null).
     */
    private function animeIds(array $data): array
    {
        return array_values(array_unique(array_filter($data['animes'] ?? [])));
    }
}