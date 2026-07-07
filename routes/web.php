<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\BookController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('books.index');
});

Route::middleware('auth')->group(function () {
    // ジャンルのCRUDルート
    Route::resource('genres', GenreController::class);
    Route::resource('books', BookController::class)->except(['index', 'show']);
    
    // TODO: お気に入り関連の仮ルート(#10で本実装に置き換え)
    Route::get('/favorites', fn() => 'お気に入り一覧は未実装です(Issue #10で実装予定)')->name('favorites.index');
    Route::post('/favorites/{book}', fn($book) => 'お気に入り機能は未実装です(Issue #10で実装予定)')->name('favorites.toggle');

    // TODO: レビュー関連の仮ルート(#8, #9で本実装に置き換え)
    Route::post('/books/{book}/reviews', fn($book) => 'レビュー投稿は未実装です(Issue #8/#9で実装予定)')->name('reviews.store');
    Route::get('/reviews/{review}/edit', fn($review) => 'レビュー編集は未実装です(Issue #8/#9で実装予定)')->name('reviews.edit');
    Route::delete('/reviews/{review}', fn($review) => 'レビュー削除は未実装です(Issue #8/#9で実装予定)')->name('reviews.destroy');

    // TODO: いいね機能の仮ルート(#11で本実装に置き換え)
    Route::post('/reviews/{review}/like', fn($review) => 'いいね機能は未実装です(Issue #11で実装予定)')->name('reviews.like');
});

Route::resource('books', BookController::class)->only(['index', 'show']);
Route::get('/ranking', fn() => 'ランキングは未実装です(Issue #12で実装予定)')->name('ranking.index');


