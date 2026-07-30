<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_認証ユーザーはレビューを投稿できる(): void
    {
        // Arrange
        $user = User::factory()->create();
        $book = Book::factory()->create();

        // Act
        $response = $this->actingAs($user)->post(route('reviews.store', $book), [
            'rating' => 4,
            'comment' => 'とても良い本でした。',
        ]);

        // Assert
        $response->assertRedirect(route('books.show', $book));
        $this->assertDatabaseHas('reviews', [
            'book_id' => $book->id,
            'user_id' => $user->id,
            'rating' => 4,
            'comment' => 'とても良い本でした。',
        ]);
        $this->get(route('books.show', $book))->assertSee('とても良い本でした。');
    }

    public function test_評価が未選択の場合はバリデーションエラーになる(): void
    {
        // Arrange
        $user = User::factory()->create();
        $book = Book::factory()->create();

        // Act
        $response = $this->actingAs($user)->post(route('reviews.store', $book), [
            'comment' => 'コメントは有効',
        ]);

        // Assert
        $response->assertSessionHasErrors('rating');
        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_評価が範囲外の場合はバリデーションエラーになる(): void
    {
        // Arrange
        $user = User::factory()->create();
        $book = Book::factory()->create();

        // Act & Assert（範囲外の境界値 0 と 6 をそれぞれ検証）
        foreach ([0, 6] as $invalidRating) {
            $response = $this->actingAs($user)->post(route('reviews.store', $book), [
                'rating' => $invalidRating,
                'comment' => 'テストコメント',
            ]);
            $response->assertSessionHasErrors('rating');
        }
        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_コメントが未入力の場合はバリデーションエラーになる(): void
    {
        // Arrange
        $user = User::factory()->create();
        $book = Book::factory()->create();

        // Act
        $response = $this->actingAs($user)->post(route('reviews.store', $book), [
            'rating' => 3,
        ]);

        // Assert
        $response->assertSessionHasErrors('comment');
        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_コメントが1000文字を超える場合はバリデーションエラーになる(): void
    {
        // Arrange
        $user = User::factory()->create();
        $book = Book::factory()->create();

        // Act & Assert（1000文字ちょうどは通過）
        $this->actingAs($user)->post(route('reviews.store', $book), [
            'rating' => 3,
            'comment' => str_repeat('あ', 1000),
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('reviews', 1);

        // Act & Assert（1001文字はエラー・レビューは増えない）
        $this->actingAs($user)->post(route('reviews.store', $book), [
            'rating' => 3,
            'comment' => str_repeat('あ', 1001),
        ])->assertSessionHasErrors('comment');
        $this->assertDatabaseCount('reviews', 1);
    }

    public function test_投稿者はレビュー編集画面を表示できる(): void
    {
        // Arrange
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => '編集前のコメント',
        ]);

        // Act
        $response = $this->actingAs($user)->get(route('reviews.edit', $review));

        // Assert
        $response->assertOk();
        $response->assertViewHas('review', fn ($viewReview) => $viewReview->is($review));
        $response->assertSee('編集前のコメント');
    }

    public function test_投稿者以外がレビュー編集画面を開くと403になる(): void
    {
        // Arrange
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $owner->id,
            'book_id' => Book::factory()->create()->id,
        ]);

        // Act
        $response = $this->actingAs($other)->get(route('reviews.edit', $review));

        // Assert
        $response->assertForbidden();
    }

    public function test_投稿者はレビューを更新できる(): void
    {
        // Arrange
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 2,
            'comment' => '更新前',
        ]);

        // Act
        $response = $this->actingAs($user)->put(route('reviews.update', $review), [
            'rating' => 5,
            'comment' => '更新後のコメント',
        ]);

        // Assert
        $response->assertRedirect(route('books.show', $book));
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 5,
            'comment' => '更新後のコメント',
        ]);
    }

    public function test_投稿者以外がレビューを更新すると403になる(): void
    {
        // Arrange
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $owner->id,
            'book_id' => Book::factory()->create()->id,
            'rating' => 3,
            'comment' => '元のコメント',
        ]);

        // Act
        $response = $this->actingAs($other)->put(route('reviews.update', $review), [
            'rating' => 1,
            'comment' => '不正な更新',
        ]);

        // Assert
        $response->assertForbidden();
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 3,
            'comment' => '元のコメント',
        ]);
    }

    public function test_投稿者はレビューを削除できる_関連するいいねも削除される(): void
    {
        // Arrange
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
        // このレビューに別ユーザーからのいいねを付与（中間テーブル review_likes に登録）
        $review->likedByUsers()->attach(User::factory()->create());

        // Act
        $response = $this->actingAs($user)->delete(route('reviews.destroy', $review));

        // Assert
        $response->assertRedirect(route('books.show', $book));
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
        $this->assertDatabaseMissing('review_likes', ['review_id' => $review->id]);
    }

    public function test_投稿者以外がレビューを削除すると403になる(): void
    {
        // Arrange
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $owner->id,
            'book_id' => Book::factory()->create()->id,
        ]);

        // Act
        $response = $this->actingAs($other)->delete(route('reviews.destroy', $review));

        // Assert
        $response->assertForbidden();
        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
    }
}
