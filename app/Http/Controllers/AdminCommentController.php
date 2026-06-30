<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminCommentController extends Controller
{
    public function index(): View
    {
        $comments = Comment::with('user', 'commentable')
            ->latest()
            ->paginate(20);

        return view('admin.comments.index', compact('comments'));
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $comment->delete();

        return redirect()->route('admin.comments.index')
            ->with('status', 'Comentario eliminado.');
    }
}