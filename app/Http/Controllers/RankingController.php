<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\View\View;

class RankingController extends Controller
{
    /**
     * レビュー平均評価TOP10の書籍ランキングを表示する。
     */
    public function index(): View
    {
        $rankedBooks = Book::query()
            ->has('reviews')
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->orderByDesc('reviews_avg_rating')
            ->orderBy('id')
            ->take(10)
            ->get();

        return view('ranking.index', compact('rankedBooks'));
    }
}
