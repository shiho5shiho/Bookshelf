<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GenreController;

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
// TODO: 各Issue実装時に本実装へ置き換える(navigation.blade.phpが依存しているための一時対応)
Route::get('/books', fn() => '書籍一覧は未実装です(Issue #6で実装予定)')->name('books.index');
Route::get('/books/create', fn() => '書籍登録は未実装です(Issue #6で実装予定)')->name('books.create');
Route::get('/books/{book}', fn($book) => "書籍詳細は未実装です(Issue #6で実装予定・ID: {$book})")->name('books.show');
Route::get('/ranking', fn() => 'ランキングは未実装です(Issue #12で実装予定)')->name('ranking.index');

Route::middleware('auth')->group(function () {
    // ジャンルのCRUDルート
    Route::resource('genres', GenreController::class);
    //TODO: お気に入り一覧の仮ルート（#10で本実装に置き換え）
    Route::get('/favorites', fn() => 'お気に入り一覧は未実装です(Issue #10で実装予定)')->name('favorites.index');
});
