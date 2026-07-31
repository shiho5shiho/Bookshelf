<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteLikeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_お気に入りの追加_解除_再追加が正しく動作する(): void
    {
        // Arrange
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $url = route('favorites.toggle', $book);
        $from = route('books.show', $book);

        // Act & Assert（1回目: 追加）
        $this->actingAs($user)->from($from)->post($url)->assertRedirect($from);
        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        // Act & Assert（2回目: 解除）
        $this->actingAs($user)->from($from)->post($url)->assertRedirect($from);
        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        // Act & Assert（3回目: 再追加）
        $this->actingAs($user)->from($from)->post($url)->assertRedirect($from);
        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    public function test_認証ユーザーはお気に入り一覧を閲覧できる(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)->get(route('favorites.index'));

        // Assert
        $response->assertOk();
    }

    public function test_お気に入り一覧は10件ずつページネーションされる(): void
    {
        // Arrange
        $user = User::factory()->create();
        $books = Book::factory()->count(11)->create();
        $user->favoriteBooks()->attach($books);

        // Act
        $response = $this->actingAs($user)->get(route('favorites.index'));

        // Assert
        $response->assertOk();
        $response->assertViewHas('books', function ($books) {
            return $books->count() === 10
                && $books->total() === 11
                && $books->lastPage() === 2;
        });
    }

    public function test_お気に入り一覧に他ユーザーのお気に入りは表示されない(): void
    {
        // Arrange
        $user = User::factory()->create();
        $other = User::factory()->create();
        $myBook = Book::factory()->create();
        $othersBook = Book::factory()->create();
        $user->favoriteBooks()->attach($myBook);
        $other->favoriteBooks()->attach($othersBook);

        // Act
        $response = $this->actingAs($user)->get(route('favorites.index'));

        // Assert
        $response->assertOk();
        $response->assertViewHas('books', function ($books) use ($myBook, $othersBook) {
            return $books->contains($myBook) && ! $books->contains($othersBook);
        });
    }

    public function test_いいねの追加_解除_再追加が正しく動作する(): void
    {
        // Arrange
        $user = User::factory()->create();
        $review = Review::factory()->create([
            'user_id' => User::factory()->create()->id,
            'book_id' => Book::factory()->create()->id,
        ]);
        $url = route('reviews.like', $review);
        $from = route('books.show', $review->book_id);

        // Act & Assert（1回目: 追加）
        $this->actingAs($user)->from($from)->post($url)->assertRedirect($from);
        $this->assertDatabaseHas('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);

        // Act & Assert（2回目: 解除）
        $this->actingAs($user)->from($from)->post($url)->assertRedirect($from);
        $this->assertDatabaseMissing('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);

        // Act & Assert（3回目: 再追加）
        $this->actingAs($user)->from($from)->post($url)->assertRedirect($from);
        $this->assertDatabaseHas('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }

    public function test_いいね数がレビューに反映される(): void
    {
        // Arrange
        $review = Review::factory()->create([
            'user_id' => User::factory()->create()->id,
            'book_id' => Book::factory()->create()->id,
        ]);
        $likers = User::factory()->count(3)->create();
        $review->likedByUsers()->attach($likers);

        // Act
        $result = Review::withCount('likedByUsers')->find($review->id);

        // Assert
        $this->assertSame(3, $result->liked_by_users_count);
    }
}
