<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\Comment;
use App\Models\Episode;
use App\Models\News;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminCommentController extends Controller
{
    public function index(Request $request): View
    {
        $animes = Anime::orderBy('title')->get(['id', 'title', 'slug']);
        $currentAnime = $animes->firstWhere('slug', $request->query('anime'));

        $type = in_array($request->query('type'), ['news', 'episodes'], true)
            ? $request->query('type')
            : null;

        $comments = Comment::with([
                'user',
                'commentable' => fn (MorphTo $morphTo) => $morphTo->morphWith([
                    Episode::class => ['animeSeason.anime'],
                ]),
            ])
            ->when($type === 'news', fn ($query) => $query->where('commentable_type', News::class))
            ->when($type === 'episodes', fn ($query) => $query->where('commentable_type', Episode::class))
            ->when($currentAnime, fn ($query, $anime) => $query->where(fn ($q) => $q
                ->whereHasMorph('commentable', [News::class], fn ($news) => $news
                    ->whereHas('animes', fn ($a) => $a->where('animes.id', $anime->id))
                )
                ->orWhereHasMorph('commentable', [Episode::class], fn ($episode) => $episode
                    ->whereHas('animeSeason', fn ($s) => $s->where('anime_id', $anime->id))
                )
            ))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.comments.index', compact('comments', 'animes', 'currentAnime', 'type'));
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $comment->delete();

        return back()->with('status', 'Comentario eliminado.');
    }
}