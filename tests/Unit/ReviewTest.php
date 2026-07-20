<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_レビュー対象の書籍を取得できる(): void
    {
        // Arrange
        $book = Book::factory()->create();
        $review = Review::factory()->for($book)->create();

        // Act
        $targetBook = $review->book;

        // Assert
        $this->assertInstanceOf(Book::class, $targetBook);
        $this->assertSame($book->id, $targetBook->id);
    }

    public function test_レビューの投稿者を取得できる(): void
    {
        // Arrange
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->for($book)->for($user)->create();

        // Act
        $postedUser = $review->user;

        // Assert
        $this->assertInstanceOf(User::class, $postedUser);
        $this->assertSame($user->id, $postedUser->id);
    }

    public function test_レビューにいいねしたユーザーを取得できる(): void
    {
        // Arrange
        $review = Review::factory()->create();
        $expectedUsers = User::factory()->count(2)->create();
        User::factory()->create(); // いいねしないユーザー
        $review->likedByUsers()->attach($expectedUsers->pluck('id'));

        // Act
        $users = $review->likedByUsers;

        // Assert
        $this->assertCount(2, $users);
        $this->assertEqualsCanonicalizing(
            $expectedUsers->pluck('id')->all(),
            $users->pluck('id')->all()
        );
    }

    public function test_レビューのいいね数を取得できる(): void
    {
        // Arrange
        $review = Review::factory()->create();
        $review->likedByUsers()->attach(User::factory()->count(3)->create()->pluck('id'));
        Review::factory()->create(); // 別レビューはカウントされない

        // Act
        $result = Review::withCount('likedByUsers')->find($review->id);

        // Assert
        $this->assertSame(3, $result->liked_by_users_count);
    }
}
