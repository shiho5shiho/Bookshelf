<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    /**
     * ログインユーザーのお気に入り書籍一覧を表示する。
     */
    public function index(): View
    {
        /** @var User $user */
        $user = auth()->user();

        $books = $user->favoriteBooks()
            ->with('genres')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->paginate(10);

        return view('favorites.index', compact('books'));
    }

    /**
     * お気に入りの追加・解除を切り替える。
     */
    public function toggle(Book $book): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $user->favoriteBooks()->toggle($book->id);

        return back();
    }
}
