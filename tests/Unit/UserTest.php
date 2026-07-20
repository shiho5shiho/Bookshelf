<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_ユーザーが登録した書籍を取得できる(): void
    {
        // Arrange
        $user = User::factory()->create();
        Book::factory()->count(3)->for($user)->create();
        Book::factory()->count(2)->create(); // 他ユーザーの書籍

        // Act
        $books = $user->books;

        // Assert
        $this->assertCount(3, $books);
        $this->assertTrue($books->every(fn($book) => $book->user_id === $user->id));
    }

    public function test_ユーザーが投稿したレビューを取得できる(): void
    {
        // Arrange
        $user = User::factory()->create();
        $book = Book::factory()->create();
        Review::factory()->count(2)->for($book)->for($user)->create();
        Review::factory()->for($book)->create(); // 別ユーザーのレビュー

        // Act
        $reviews = $user->reviews;

        // Assert
        $this->assertCount(2, $reviews);
        $this->assertTrue($reviews->every(fn($review) => $review->user_id === $user->id));
    }

    public function test_ユーザーがお気に入りにした書籍を取得できる(): void
    {
        // Arrange
        $user = User::factory()->create();
        $expectedBooks = Book::factory()->count(2)->create();
        Book::factory()->create(); // お気に入りにしない書籍
        $user->favoriteBooks()->attach($expectedBooks->pluck('id'));

        // Act
        $favoriteBooks = $user->favoriteBooks;

        // Assert
        $this->assertCount(2, $favoriteBooks);
        $this->assertEqualsCanonicalizing(
            $expectedBooks->pluck('id')->all(),
            $favoriteBooks->pluck('id')->all()
        );
    }

    public function test_ユーザーがいいねしたレビューを取得できる(): void
    {
        // Arrange
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $expectedReviews = Review::factory()->count(2)->for($book)->create();
        Review::factory()->for($book)->create(); // いいねしないレビュー
        $user->likedReviews()->attach($expectedReviews->pluck('id'));

        // Act
        $likedReviews = $user->likedReviews;

        // Assert
        $this->assertCount(2, $likedReviews);
        $this->assertEqualsCanonicalizing(
            $expectedReviews->pluck('id')->all(),
            $likedReviews->pluck('id')->all()
        );
    }

    public function test_お気に入りの中間テーブルにタイムスタンプが記録される(): void
    {
        // Arrange
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $user->favoriteBooks()->attach($book->id);

        // Act
        $pivot = $user->favoriteBooks()->first()->pivot;

        // Assert
        $this->assertNotNull($pivot->created_at);
        $this->assertNotNull($pivot->updated_at);
    }

    public function test_いいねの中間テーブルにタイムスタンプが記録される(): void
    {
        // Arrange
        $user = User::factory()->create();
        $review = Review::factory()->create();
        $user->likedReviews()->attach($review->id);

        // Act
        $pivot = $user->likedReviews()->first()->pivot;

        // Assert
        $this->assertNotNull($pivot->created_at);
        $this->assertNotNull($pivot->updated_at);
    }
}
