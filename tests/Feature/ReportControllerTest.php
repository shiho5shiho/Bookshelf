<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_未認証ユーザーはログイン画面にリダイレクトされる(): void
    {
        // Act
        $response = $this->get('/reports');

        // Assert
        $response->assertRedirect('/login');
    }

    public function test_レビューが1件も無いユーザーでも表示が崩れない(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)->get('/reports');

        // Assert
        $response->assertOk();
        $response->assertViewHas('stats', function (array $stats): bool {
            return $stats['summary']['total_reviews'] === 0
                && $stats['summary']['books_read'] === 0
                && $stats['summary']['average_rating'] === 0.0
                && $stats['rating_distribution']->count() === 5
                && $stats['top_rated_books']->isEmpty()
                && $stats['genre_ratings']->isEmpty();
        });
    }

    public function test_基本サマリーが正しく集計される(): void
    {
        // Arrange
        $user = User::factory()->create();
        $bookA = Book::factory()->create();
        $bookB = Book::factory()->create();
        Review::factory()->for($user)->for($bookA)->create(['rating' => 5]);
        Review::factory()->for($user)->for($bookB)->create(['rating' => 3]);

        // Act
        $response = $this->actingAs($user)->get('/reports');

        // Assert
        $response->assertViewHas('stats', function (array $stats): bool {
            return $stats['summary']['total_reviews'] === 2
                && $stats['summary']['books_read'] === 2
                && $stats['summary']['average_rating'] === 4.0;
        });
    }

    public function test_評価分布は星ごとの件数を持ち必ず5要素になる(): void
    {
        // Arrange
        $user = User::factory()->create();
        Review::factory()->for($user)->for(Book::factory())->create(['rating' => 5]);
        Review::factory()->for($user)->for(Book::factory())->create(['rating' => 5]);
        Review::factory()->for($user)->for(Book::factory())->create(['rating' => 2]);

        // Act
        $response = $this->actingAs($user)->get('/reports');

        // Assert
        $response->assertViewHas('stats', function (array $stats): bool {
            $distribution = $stats['rating_distribution'];

            return $distribution->count() === 5
                && $distribution[4] === 2  // index4 = 5★
                && $distribution[1] === 1  // index1 = 2★
                && $distribution[0] === 0; // index0 = 1★、0件でも欠けない
        });
    }

    public function test_同一書籍への複数レビューは高評価書籍で1件に集約される(): void
    {
        // Arrange
        $user = User::factory()->create();
        $book = Book::factory()->create();
        Review::factory()->for($user)->for($book)->create(['rating' => 4]);
        Review::factory()->for($user)->for($book)->create(['rating' => 5]);

        // Act
        $response = $this->actingAs($user)->get('/reports');

        // Assert
        $response->assertViewHas('stats', function (array $stats) use ($book): bool {
            $topRatedBooks = $stats['top_rated_books'];

            return $topRatedBooks->count() === 1
                && $topRatedBooks->first()['id'] === $book->id
                && $topRatedBooks->first()['rating'] === 5; // 高い方が採用される
        });
    }

    public function test_3星以下の書籍は高評価書籍に含まれない(): void
    {
        // Arrange
        $user = User::factory()->create();
        Review::factory()->for($user)->for(Book::factory())->create(['rating' => 3]);

        // Act
        $response = $this->actingAs($user)->get('/reports');

        // Assert
        $response->assertViewHas('stats', function (array $stats): bool {
            return $stats['top_rated_books']->isEmpty();
        });
    }

    public function test_ジャンル別評価が平均評価の高い順に集計される(): void
    {
        // Arrange
        $user = User::factory()->create();
        $fiction = Genre::factory()->create(['name' => '小説']);
        $tech = Genre::factory()->create(['name' => '技術書']);

        $bookA = Book::factory()->create();
        $bookA->genres()->attach($fiction);
        $bookB = Book::factory()->create();
        $bookB->genres()->attach($tech);

        Review::factory()->for($user)->for($bookA)->create(['rating' => 3]);
        Review::factory()->for($user)->for($bookB)->create(['rating' => 5]);

        // Act
        $response = $this->actingAs($user)->get('/reports');

        // Assert
        $response->assertViewHas('stats', function (array $stats) use ($tech): bool {
            $genreRatings = $stats['genre_ratings'];

            return $genreRatings->count() === 2
                && $genreRatings->first()['id'] === $tech->id // 平均5の技術書が先頭
                && $genreRatings->first()['average_rating'] === 5.0;
        });
    }
}
