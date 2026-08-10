<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BookController extends Controller
{
    /**
     * 書籍一覧を表示する（10件ずつページネーション）。
     */
    public function index(): View
    {
        $books = Book::with('genres')->latest()->paginate(10);

        return view('books.index', compact('books'));
    }

    /**
     * 書籍登録フォームを表示する。
     */
    public function create(): View
    {
        $genres = Genre::all();

        return view('books.create', compact('genres'));
    }

    /**
     * 書籍を新規登録し、ジャンルを紐付ける。
     */
    public function store(StoreBookRequest $request): RedirectResponse
    {
        $book = Book::create($request->validated() + ['user_id' => auth()->id()]);
        $book->genres()->sync($request->input('genres'));

        return redirect()
            ->route('books.show', $book)
            ->with('success', '書籍を登録しました。');
    }

    /**
     * 書籍詳細を表示する（ジャンル・レビュー・いいね情報を合わせて取得）。
     */
    public function show(Book $book): View
    {
        $book->load('genres', 'reviews.user', 'reviews.likedByUsers');

        return view('books.show', compact('book'));
    }

    /**
     * 書籍編集フォームを表示する。作成者本人のみ許可。
     */
    public function edit(Book $book): View
    {
        $this->authorize('update', $book);

        $genres = Genre::all();

        return view('books.edit', compact('book', 'genres'));
    }

    /**
     * 書籍情報とジャンル紐付けを更新する。作成者本人のみ許可。
     */
    public function update(UpdateBookRequest $request, Book $book): RedirectResponse
    {
        $this->authorize('update', $book);

        $book->update($request->validated());
        $book->genres()->sync($request->input('genres'));

        return redirect()
            ->route('books.show', $book)
            ->with('success', '書籍情報を更新しました。');
    }

    /**
     * 書籍を削除する。作成者本人のみ許可。
     */
    public function destroy(Book $book): RedirectResponse
    {
        $this->authorize('delete', $book);

        $book->delete();

        return redirect()
            ->route('books.index')
            ->with('success', '書籍を削除しました。');
    }
}
