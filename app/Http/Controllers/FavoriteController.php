<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\User;

class FavoriteController extends Controller
{
    public function index()
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

    public function toggle(Book $book)
    {
        /** @var User $user */
        $user = auth()->user();

        $user->favoriteBooks()->toggle($book->id);

        return back();
    }
}
