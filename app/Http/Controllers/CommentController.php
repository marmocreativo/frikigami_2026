<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'body'             => ['required', 'string', 'max:1000'],
            'commentable_type' => ['required', 'string', 'in:App\Models\News,App\Models\Episode'],
            'commentable_id'   => ['required', 'integer'],
        ]);

        $commentable = $request->commentable_type::findOrFail($request->commentable_id);

        $commentable->comments()->create([
            'user_id' => auth()->id(),
            'body'    => $request->body,
        ]);

        return back()->with('status', 'Comentario publicado.');
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        if ($comment->user_id !== auth()->id()) {
            abort(403);
        }

        $comment->delete();

        return back()->with('status', 'Comentario eliminado.');
    }
}