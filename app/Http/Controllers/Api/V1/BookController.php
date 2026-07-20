<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\BookIndexRequest;
use App\Http\Requests\Api\V1\BookStoreRequest;
use App\Http\Requests\Api\V1\BookUpdateRequest;
use App\Http\Resources\BookDetailResource;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\Response;

class BookController extends Controller
{
    /**
     * AP01: 書籍一覧を取得する
     */
    public function index(BookIndexRequest $request)
    {
        $books = Book::query()
            ->with('genres')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->when($request->keyword, function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('title', 'like', "%{$keyword}%")
                        ->orWhere('author', 'like', "%{$keyword}%");
                });
            })
            ->when($request->genre_id, function ($query, $genreId) {
                $query->whereHas('genres', fn ($q) => $q->where('genres.id', $genreId));
            })
            ->orderByDesc('id')
            ->paginate($request->input('per_page', 20));

        return BookResource::collection($books);
    }

    /**
     * AP02: 書籍詳細を取得する
     */
    public function show(Book $book)
    {
        $book->load([
            'genres',
            'reviews' => fn ($query) => $query->with('user')->latest(),
        ]);

        return new BookDetailResource($book);
    }

    /**
     * AP03: 書籍を新規登録する
     */
    public function store(BookStoreRequest $request)
    {
        $book = Book::create($request->safe()->except('genres'));

        $book->genres()->sync($request->validated('genres'));

        $book->load('genres')->loadAvg('reviews', 'rating')->loadCount('reviews');

        return (new BookResource($book))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * AP04: 書籍を更新する
     */
    public function update(BookUpdateRequest $request, Book $book)
    {
        $book->update($request->safe()->except('genres'));

        $book->genres()->sync($request->validated('genres'));

        $book->load('genres')->loadAvg('reviews', 'rating')->loadCount('reviews');

        return new BookResource($book);
    }

    /**
     * AP05: 書籍を削除する
     */
    public function destroy(Book $book)
    {
        $book->delete();

        return response()->noContent();
    }
}
