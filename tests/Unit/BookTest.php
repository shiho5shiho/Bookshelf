<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    public function test_書籍の登録者を取得できる(): void
    {
        // Arrange
        $user = User::factory()->create();
        $book = Book::factory()->for($user)->create();

        // Act
        $registeredUser = $book->user;

        // Assert
        $this->assertInstanceOf(User::class, $registeredUser);
        $this->assertSame($user->id, $registeredUser->id);
    }

    public function test_書籍に紐づくジャンルを取得できる(): void
    {
        // Arrange
        $book = Book::factory()->create();
        $expectedGenres = Genre::factory()->count(2)->create();
        Genre::factory()->create(); // 紐づけないジャンル
        $book->genres()->attach($expectedGenres->pluck('id'));

        // Act
        $genres = $book->genres;

        // Assert
        $this->assertCount(2, $genres);
        $this->assertEqualsCanonicalizing(
            $expectedGenres->pluck('id')->all(),
            $genres->pluck('id')->all()
        );
    }

    public function test_書籍に紐づくレビューを取得できる(): void
    {
        // Arrange
        $book = Book::factory()->create();
        Review::factory()->count(3)->for($book)->create();
        Review::factory()->create(); // 別書籍のレビュー

        // Act
        $reviews = $book->reviews;

        // Assert
        $this->assertCount(3, $reviews);
        $this->assertTrue($reviews->every(fn($review) => $review->book_id === $book->id));
    }

    public function test_書籍をお気に入りにしているユーザーを取得できる(): void
    {
        // Arrange
        $book = Book::factory()->create();
        $expectedUsers = User::factory()->count(2)->create();
        User::factory()->create(); // お気に入りにしないユーザー
        $book->favoritedByUsers()->attach($expectedUsers->pluck('id'));

        // Act
        $users = $book->favoritedByUsers;

        // Assert
        $this->assertCount(2, $users);
        $this->assertEqualsCanonicalizing(
            $expectedUsers->pluck('id')->all(),
            $users->pluck('id')->all()
        );
    }

    /**
     * rating 5・4・3 の3件 → 平均 4.0
     *
     * assertSame ではなく assertEquals を使う。
     * 集計値の型は DB エンジンによって異なるため（MySQL は文字列、SQLite は float）。
     */
    public function test_書籍の平均評価を取得できる(): void
    {
        // Arrange
        $book = Book::factory()->create();
        Review::factory()->for($book)->create(['rating' => 5]);
        Review::factory()->for($book)->create(['rating' => 4]);
        Review::factory()->for($book)->create(['rating' => 3]);

        // Act
        $result = Book::withAvg('reviews', 'rating')->find($book->id);

        // Assert
        $this->assertEquals(4.0, $result->reviews_avg_rating);
    }

    public function test_書籍のレビュー件数を取得できる(): void
    {
        // Arrange
        $book = Book::factory()->create();
        Review::factory()->count(3)->for($book)->create();
        Review::factory()->create(); // 別書籍のレビューはカウントされない

        // Act
        $result = Book::withCount('reviews')->find($book->id);

        // Assert
        $this->assertSame(3, $result->reviews_count);
    }

    /**
     * ランキングの「レビューがない書籍は対象外」仕様の根拠となる挙動。
     */
    public function test_レビューがない書籍の平均評価はnullになる(): void
    {
        // Arrange
        $book = Book::factory()->create();

        // Act
        $result = Book::withAvg('reviews', 'rating')->withCount('reviews')->find($book->id);

        // Assert
        $this->assertNull($result->reviews_avg_rating);
        $this->assertSame(0, $result->reviews_count);
    }

    public function test_出版日が日付型にキャストされる(): void
    {
        // Arrange
        $book = Book::factory()->create(['published_date' => '2012-06-23']);

        // Act
        $publishedDate = $book->published_date;

        // Assert
        $this->assertInstanceOf(Carbon::class, $publishedDate);
        $this->assertSame('2012-06-23', $publishedDate->toDateString());
    }
}
