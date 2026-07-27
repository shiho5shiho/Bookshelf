<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
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

    public function test_存在しない書籍_i_dを指定すると404になる(): void
    {
        // Arrange
        // RefreshDatabase でDBは空。 だからID=999の書籍は存在しない。「存在しない」状態は「何も作らない」ことで自然に作れる。
        // Act
        $response = $this->get('/books/999');

        // Assert
        $response->assertNotFound();
    }

    public function test_認証ユーザーは書籍登録画面を表示できる(): void
    {
        // Arrange
        $user = User::factory()->create();
        Genre::factory()->count(3)->create();

        // Act
        $response = $this->actingAs($user)->get('/books/create');

        // Assert
        $response->assertOk();
        $response->assertViewHas('genres', function ($genres) {
            return $genres->count() === 3;
        });
    }

    public function test_書籍を登録できる(): void
    {
        // Arrange
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $data = [
            'title' => 'テスト駆動開発',
            'author' => 'Kent Beck',
            'isbn' => '9781234567890',
            'published_date' => '2012-06-23',
            'description' => 'テストの本',
            'image_url' => 'https://example.com/image.jpg',
            'genres' => $genres->pluck('id')->toArray(),
        ];

        // Act
        $response = $this->actingAs($user)->post('/books', $data);

        // Assert
        $this->assertDatabaseHas('books', [
            'title' => 'テスト駆動開発',
            'isbn' => '9781234567890',
            'user_id' => $user->id,
        ]);

        $book = Book::where('isbn', '9781234567890')->first();
        $this->assertEqualsCanonicalizing(
            $genres->pluck('id')->all(),
            $book->genres->pluck('id')->all()
        );

        $response->assertRedirect(route('books.show', $book));
    }

    public static function 書籍登録の異常系(): array
    {
        // 正しいデータ一式を基準にして、1項目ずつ壊す
        $valid = [
            'title' => 'テスト駆動開発',
            'author' => 'Kent Beck',
            'isbn' => '9781234567890',
            'published_date' => '2012-06-23',
            'genres' => [1],
        ];

        return [
            'タイトルが空' => [
                ['title' => ''] + $valid,
                ['title' => 'タイトルを入力してください。'],
            ],
            '著者が空' => [
                ['author' => ''] + $valid,
                ['author' => '著者名を入力してください。'],
            ],
            'ISBNが13桁でない' => [
                ['isbn' => '123'] + $valid,
                ['isbn' => 'ISBNは13桁の数字で入力してください。'],
            ],
            '出版日が空' => [
                ['published_date' => ''] + $valid,
                ['published_date' => '出版日を入力してください。'],
            ],
            'ジャンル未選択' => [
                ['genres' => []] + $valid,
                ['genres' => 'ジャンルを1つ以上選択してください。'],
            ],
        ];
    }

    #[DataProvider('書籍登録の異常系')]
    public function test_不正な入力では書籍を登録できない(array $data, array $errors): void
    {
        // Arrange
        $user = User::factory()->create();
        Genre::factory()->create();  // genres:[1] が exists を通るように1件用意

        // Act
        $response = $this->actingAs($user)->post('/books', $data);

        // Assert
        $response->assertSessionHasErrors($errors);
        $this->assertDatabaseEmpty('books');
    }

    public function test_重複する_isb_nは登録できない(): void
    {
        // Arrange
        Book::factory()->create(['isbn' => '9781234567890']);  // 先に1件作る

        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $data = [
            'title' => 'テスト駆動開発',
            'author' => 'Kent Beck',
            'isbn' => '9781234567890',  // 既に存在するISBN
            'published_date' => '2012-06-23',
            'description' => 'テストの本',
            'image_url' => 'https://example.com/image.jpg',
            'genres' => $genres->pluck('id')->toArray(),
        ];

        // Act
        $response = $this->actingAs($user)->post('/books', $data);

        // Assert
        $response->assertSessionHasErrors(['isbn' => 'このISBNは既に登録されています。']);
        $this->assertDatabaseCount('books', 1);
    }

    public function test_作成者は書籍編集画面を表示できる(): void
    {
        // Arrange
        $user = User::factory()->create();
        $book = Book::factory()->for($user)->create(['title' => '編集前タイトル']);

        // Act
        $response = $this->actingAs($user)->get("/books/{$book->id}/edit");

        // Assert
        $response->assertOk();
        $response->assertSee('編集前タイトル');
    }

    public function test_作成者以外が書籍編集画面を開くと403になる(): void
    {
        // Arrange
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $book = Book::factory()->for($owner)->create();

        // Act
        $response = $this->actingAs($other)->get("/books/{$book->id}/edit");

        // Assert
        $response->assertForbidden();
    }

    public function test_作成者は書籍を更新できる(): void
    {
        // Arrange
        $user = User::factory()->create();
        $book = Book::factory()->for($user)->create(['title' => '更新前']);

        $oldGenre = Genre::factory()->create();
        $book->genres()->attach($oldGenre);           // 最初は oldGenre が付いている

        $newGenres = Genre::factory()->count(2)->create();

        $data = [
            'title' => '更新後',
            'author' => 'Kent Beck',
            'isbn' => '9781234567890',
            'published_date' => '2012-06-23',
            'genres' => $newGenres->pluck('id')->toArray(),  // newGenres に入れ替え
        ];

        // Act
        $response = $this->actingAs($user)->put("/books/{$book->id}", $data);

        // Assert
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新後',
        ]);

        $this->assertEqualsCanonicalizing(
            $newGenres->pluck('id')->all(),
            $book->fresh()->genres->pluck('id')->all()
        );

        $response->assertRedirect(route('books.show', $book));
    }

    public function test_更新時に自身の_isb_nはそのまま使える(): void
    {
        // Arrange
        $user = User::factory()->create();
        $book = Book::factory()->for($user)->create(['isbn' => '9781234567890']);
        $genre = Genre::factory()->create();
        $book->genres()->attach($genre);

        $data = [
            'title' => '更新後タイトル',
            'author' => 'Kent Beck',
            'isbn' => '9781234567890',   // 自分のISBNのまま
            'published_date' => '2012-06-23',
            'genres' => [$genre->id],
        ];

        // Act
        $response = $this->actingAs($user)->put("/books/{$book->id}", $data);

        // Assert
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新後タイトル',
        ]);
    }

    public function test_更新時に他の書籍の_isb_nには変更できない(): void
    {
        // Arrange
        $user = User::factory()->create();
        $bookA = Book::factory()->for($user)->create(['isbn' => '9781111111111']);
        $bookB = Book::factory()->create(['isbn' => '9782222222222']);  // 別の本
        $genre = Genre::factory()->create();
        $bookA->genres()->attach($genre);

        $data = [
            'title' => '更新後',
            'author' => 'Kent Beck',
            'isbn' => '9782222222222',   // bookB のISBNに変えようとする
            'published_date' => '2012-06-23',
            'genres' => [$genre->id],
        ];

        // Act
        $response = $this->actingAs($user)->put("/books/{$bookA->id}", $data);

        // Assert
        $response->assertSessionHasErrors(['isbn' => 'このISBNは既に登録されています。']);
    }

    public function test_作成者以外が更新すると403(): void
    {
        // Arrange
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $book = Book::factory()->for($owner)->create();

        $data = [
            'title' => '更新後',
            'author' => 'Kent Beck',
            'isbn' => '9781234567890',
            'published_date' => '2012-06-23',
            'genres' => [Genre::factory()->create()->id],
        ];

        // Act
        $response = $this->actingAs($other)->put("/books/{$book->id}", $data);

        // Assert
        $response->assertForbidden();
    }

    public function test_作成者は削除できる(): void
    {
        // Arrange
        $user = User::factory()->create();
        $book = Book::factory()->for($user)->create();
        $review = Review::factory()->for($book)->create();
        $genre = Genre::factory()->create();
        $book->genres()->attach($genre);
        $user->favoriteBooks()->attach($book);

        // Act
        $response = $this->actingAs($user)->delete("/books/{$book->id}");

        // Assert
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
        $this->assertDatabaseMissing('book_genre', ['book_id' => $book->id]);
        $this->assertDatabaseMissing('favorites', ['book_id' => $book->id]);
        $response->assertRedirect(route('books.index'));
    }

    public function test_作成者以外が削除すると403(): void
    {
        // Arrange
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $book = Book::factory()->for($owner)->create();

        // Act
        $response = $this->actingAs($other)->delete("/books/{$book->id}");

        // Assert
        $response->assertForbidden();
    }
}
