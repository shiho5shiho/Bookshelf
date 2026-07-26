<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_書籍一覧をゲストも閲覧できる(): void
    {
        // Arrange
        $book = Book::factory()->create(['title' => 'テスト駆動開発']);

        // Act
        $response = $this->get('/books');

        // Assert
        $response->assertOk();
        $response->assertSee('テスト駆動開発');
    }

    public function test_書籍一覧は10件ずつページネーションされる(): void
    {
        // Arrange
        Book::factory()->count(11)->create();

        // Act
        $response = $this->get('/books');

        // Assert
        $response->assertOk();
        $response->assertViewHas('books', function ($books) {
            return $books->count() === 10;
        });
    }

    public function test_書籍一覧は最新順に表示される(): void
    {
        // Arrange
        $old = Book::factory()->create(['created_at' => now()->subDays(2)]);
        $new = Book::factory()->create(['created_at' => now()]);

        // Act
        $response = $this->get('/books');

        // Assert
        $response->assertOk();
        $response->assertViewHas('books', function ($books) use ($old, $new) {
            return $books->first()->id === $new->id
                && $books->last()->id === $old->id;
        });
    }

    public function test_書籍詳細をゲストも閲覧できる(): void
    {
        // Arrange
        $book = Book::factory()->create(['title' => 'テスト駆動開発']);
        $genre = Genre::factory()->create(['name' => '技術書']);
        $book->genres()->attach($genre);

        $reviewer = User::factory()->create(['name' => 'レビュー太郎']);
        Review::factory()->for($book)->for($reviewer)->create(['comment' => 'とても良い本です']);

        // Act
        $response = $this->get("/books/{$book->id}");

        // Assert
        $response->assertOk();
        $response->assertSee('テスト駆動開発');   // 書籍タイトル
        $response->assertSee('技術書');           // ジャンル
        $response->assertSee('レビュー太郎');      // レビュー投稿者
        $response->assertSee('とても良い本です');  // レビューコメント
    }

    public function test_存在しない書籍IDを指定すると404になる(): void
    {
        // Arrange
        //RefreshDatabase でDBは空。 だからID=999の書籍は存在しない。「存在しない」状態は「何も作らない」ことで自然に作れる。
        // Act
        $response = $this->get('/books/999');

        // Assert
        $response->assertNotFound();
    }
}
