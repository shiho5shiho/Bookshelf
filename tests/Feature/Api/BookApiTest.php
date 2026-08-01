<?php

namespace Tests\Feature\Api;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_書籍一覧を取得できる(): void
    {
        // Arrange
        Book::factory()->count(3)->create();

        // Act
        $response = $this->getJson(route('api.v1.books.index'));

        // Assert
        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'author',
                    'isbn',
                    'published_date',
                    'description',
                    'image_url',
                    'genres',
                    'average_rating',
                    'reviews_count',
                    'created_at',
                    'updated_at',
                ],
            ],
            'links',
            'meta',
        ]);
        $response->assertJsonCount(3, 'data');
    }

    public function test_キーワードで書籍を絞り込める(): void
    {
        // Arrange
        $target = Book::factory()->create(['title' => 'Laravelの教科書']);
        Book::factory()->create(['title' => 'Vue入門', 'author' => '別の著者']);

        // Act
        $response = $this->getJson(route('api.v1.books.index', ['keyword' => 'Laravel']));

        // Assert
        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.id', $target->id);
    }

    public function test_ジャンル_i_dで書籍を絞り込める(): void
    {
        // Arrange
        $genre = Genre::factory()->create();
        $target = Book::factory()->create();
        $target->genres()->attach($genre);
        Book::factory()->create(); // ジャンル紐付けなし

        // Act
        $response = $this->getJson(route('api.v1.books.index', ['genre_id' => $genre->id]));

        // Assert
        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.id', $target->id);
    }

    public function test_per_pageで取得件数を指定できる(): void
    {
        // Arrange
        Book::factory()->count(15)->create();

        // Act
        $response = $this->getJson(route('api.v1.books.index', ['per_page' => 5]));

        // Assert
        $response->assertOk();
        $response->assertJsonCount(5, 'data');
        $response->assertJsonPath('meta.per_page', 5);
    }

    public function test_書籍詳細を取得できる(): void
    {
        // Arrange
        $book = Book::factory()->create();
        Review::factory()->create([
            'book_id' => $book->id,
            'user_id' => User::factory()->create()->id,
        ]);

        // Act
        $response = $this->getJson(route('api.v1.books.show', $book));

        // Assert
        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'user_id',
                'title',
                'author',
                'isbn',
                'published_date',
                'description',
                'image_url',
                'genres',
                'reviews' => [
                    '*' => ['id', 'user_name', 'rating', 'comment', 'created_at'],
                ],
                'created_at',
                'updated_at',
            ],
        ]);
        $response->assertJsonPath('data.id', $book->id);
    }

    public function test_存在しない書籍_i_dを指定すると404になる(): void
    {
        // Arrange
        $nonExistentId = 999;

        // Act
        $response = $this->getJson(route('api.v1.books.show', $nonExistentId));

        // Assert
        $response->assertNotFound();
        $response->assertJson(['error' => '書籍が見つかりませんでした。']);
    }

    public function test_書籍を登録できる(): void
    {
        // Arrange
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();
        $payload = [
            'user_id' => $user->id,
            'title' => 'テスト駆動開発',
            'author' => 'Kent Beck',
            'isbn' => '9781234567890',
            'published_date' => '2023-01-01',
            'description' => '説明文',
            'genres' => $genres->pluck('id')->toArray(),
        ];

        // Act
        $response = $this->postJson(route('api.v1.books.store'), $payload);

        // Assert
        $response->assertCreated();
        $response->assertJsonPath('data.title', 'テスト駆動開発');
        $this->assertDatabaseHas('books', [
            'title' => 'テスト駆動開発',
            'isbn' => '9781234567890',
        ]);
        // genresが紐づいている
        $book = Book::where('isbn', '9781234567890')->first();
        $this->assertCount(2, $book->genres);
    }

    public function test_必須項目が無いと登録に失敗する(): void
    {
        // Arrange
        $payload = []; // 全項目欠落

        // Act
        $response = $this->postJson(route('api.v1.books.store'), $payload);

        // Assert
        $response->assertUnprocessable(); // 422
        $response->assertJsonValidationErrors(['user_id', 'title', 'author', 'isbn', 'published_date', 'genres']);
    }

    public function test_書籍を更新できる(): void
    {
        // Arrange
        $user = User::factory()->create();
        $book = Book::factory()->create(['title' => '更新前']);
        $genre = Genre::factory()->create();
        $payload = [
            'user_id' => $user->id,
            'title' => '更新後',
            'author' => '著者',
            'isbn' => '9789999999999',
            'published_date' => '2024-06-01',
            'genres' => [$genre->id],
        ];

        // Act
        $response = $this->putJson(route('api.v1.books.update', $book), $payload);

        // Assert
        $response->assertOk();
        $response->assertJsonPath('data.title', '更新後');
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新後',
        ]);
    }

    public function test_書籍を削除できる(): void
    {
        // Arrange
        $book = Book::factory()->create();

        // Act
        $response = $this->deleteJson(route('api.v1.books.destroy', $book));

        // Assert
        $response->assertNoContent(); // 204
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }
}
