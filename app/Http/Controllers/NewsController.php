<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(): View
    {
        $news = News::with('user', 'animes')
            ->published()
            ->when(request('q'), fn ($query, $q) => $query->where(
                fn ($w) => $w->where('title', 'like', "%{$q}%")->orWhere('body', 'like', "%{$q}%")
            ))
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        return view('news.index', compact('news'));
    }

    public function show(News $news): View
    {
        $news->load('user', 'animes', 'comments.user');

        $relatedNews = News::whereHas('animes', function ($q) use ($news) {
                $q->whereIn('animes.id', $news->animes->pluck('id'));
            })
            ->where('id', '!=', $news->id)
            ->published()
            ->latest('published_at')
            ->take(5)
            ->get();

        $latestNews = News::where('id', '!=', $news->id)
            ->published()
            ->latest('published_at')
            ->take(5)
            ->get();

        return view('news.show', compact('news', 'relatedNews', 'latestNews'));
    }
}