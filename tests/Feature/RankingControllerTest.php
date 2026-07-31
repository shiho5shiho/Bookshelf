<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankingControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 指定した平均評価になるよう、書籍にレビューを付けるヘルパー。
     */
    private function createBookWithRating(float $rating): Book
    {
        $book = Book::factory()->create();
        Review::factory()->create([
            'book_id' => $book->id,
            'user_id' => User::factory()->create()->id,
            'rating' => $rating,
        ]);

        return $book;
    }

    public function test_ランキングページを表示できる(): void
    {
        // Arrange
        // （未認証で表示できることを確認するため、ログインしない）

        // Act
        $response = $this->get(route('ranking.index'));

        // Assert
        $response->assertOk();
    }

    public function test_平均評価の高い順に並ぶ(): void
    {
        // Arrange
        $low = $this->createBookWithRating(2);
        $high = $this->createBookWithRating(5);
        $mid = $this->createBookWithRating(3);

        // Act
        $response = $this->get(route('ranking.index'));

        // Assert
        $response->assertOk();
        $response->assertViewHas('rankedBooks', function ($books) use ($high, $mid, $low) {
            return $books->pluck('id')->toArray() === [$high->id, $mid->id, $low->id];
        });
    }

    public function test_ランキングは上位10件までに絞られる(): void
    {
        // Arrange
        for ($i = 0; $i < 11; $i++) {
            $this->createBookWithRating(4);
        }

        // Act
        $response = $this->get(route('ranking.index'));

        // Assert
        $response->assertViewHas('rankedBooks', function ($books) {
            return $books->count() === 10;
        });
    }

    public function test_レビューが無い本はランキングに表示されない(): void
    {
        // Arrange
        $reviewed = $this->createBookWithRating(4);
        $noReview = Book::factory()->create();

        // Act
        $response = $this->get(route('ranking.index'));

        // Assert
        $response->assertViewHas('rankedBooks', function ($books) use ($reviewed, $noReview) {
            return $books->contains($reviewed) && ! $books->contains($noReview);
        });
    }

    public function test_平均評価が同点の場合はid昇順で並ぶ(): void
    {
        // Arrange
        $first = $this->createBookWithRating(5);
        $second = $this->createBookWithRating(5);

        // Act
        $response = $this->get(route('ranking.index'));

        // Assert
        $response->assertViewHas('rankedBooks', function ($books) use ($first, $second) {
            return $books->pluck('id')->toArray() === [$first->id, $second->id];
        });
    }
}
