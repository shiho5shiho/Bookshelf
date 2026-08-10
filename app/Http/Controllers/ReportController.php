<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * ログインユーザーの読書統計レポートを表示する。
     */
    public function index(): View
    {
        $reviews = Review::query()
            ->where('user_id', auth()->id())
            ->with(['book.genres'])
            ->get();

        $stats = [
            'summary' => [
                'total_reviews' => $reviews->count(),
                'books_read' => $reviews->pluck('book_id')->unique()->count(),
                'average_rating' => (float) ($reviews->avg('rating') ?? 0),
            ],
            'rating_distribution' => $this->buildRatingDistribution($reviews),
            'top_rated_books' => $this->buildTopRatedBooks($reviews),
            'genre_ratings' => $this->buildGenreRatings($reviews),
        ];

        return view('reports.index', compact('stats'));
    }

    /**
     * 1〜5星ごとのレビュー件数を返す（必ず5要素）。
     *
     * @param  Collection<int, Review>  $reviews
     * @return Collection<int, int>
     */
    private function buildRatingDistribution(Collection $reviews): Collection
    {
        return collect(range(1, 5))
            ->map(fn (int $star): int => $reviews->where('rating', $star)->count());
    }

    /**
     * 4星以上の書籍を高い順に最大5件返す（同一書籍は最高評価に集約）。
     *
     * @param  Collection<int, Review>  $reviews
     * @return Collection<int, array{id: int, title: string, author: string, rating: int}>
     */
    private function buildTopRatedBooks(Collection $reviews): Collection
    {
        return $reviews
            ->groupBy('book_id')
            ->map(fn (Collection $group): Review => $group->sortByDesc('rating')->first())
            ->filter(fn (Review $review): bool => $review->rating >= 4)
            ->sortByDesc('rating')
            ->take(5)
            ->map(fn (Review $review): array => [
                'id' => $review->book->id,
                'title' => $review->book->title,
                'author' => $review->book->author,
                'rating' => $review->rating,
            ])
            ->values();
    }

    /**
     * ジャンル別の平均評価を高い順に最大5件返す。
     *
     * @param  Collection<int, Review>  $reviews
     * @return Collection<int, array{id: int, name: string, count: int, average_rating: float}>
     */
    private function buildGenreRatings(Collection $reviews): Collection
    {
        return $reviews
            ->flatMap(fn (Review $review) => $review->book->genres->map(fn ($genre): array => [
                'genre' => $genre,
                'rating' => $review->rating,
            ]))
            ->groupBy(fn (array $item) => $item['genre']->id)
            ->map(fn (Collection $group): array => [
                'id' => $group->first()['genre']->id,
                'name' => $group->first()['genre']->name,
                'count' => $group->count(),
                'average_rating' => (float) $group->avg('rating'),
            ])
            ->sortByDesc('average_rating')
            ->take(5)
            ->values();
    }
}
