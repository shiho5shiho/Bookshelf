<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_キーワードでタイトルに一致する本が検索できる(): void
    {
        // Arrange
        $bookA = Book::factory()->create(['title' => '吾輩は猫である']);
        $bookB = Book::factory()->create(['title' => '火花']);

        // Act
        $response = $this->get('/books?keyword=猫');

        // Assert
        $response->assertOk();
        $response->assertSee($bookA->title);
        $response->assertDontSee($bookB->title);
    }

    public function test_著者名で検索できる(): void
    {
        // Arrange
        $bookA = Book::factory()->create(['author' => '夏目漱石']);
        $bookB = Book::factory()->create(['author' => '又吉直樹']);

        // Act
        $response = $this->get('/books?keyword=夏目漱石');

        // Assert
        $response->assertOk();
        $response->assertSee($bookA->author);
        $response->assertDontSee($bookB->author);
    }

    public function test_ジャンルで絞り込める(): void
    {
        // Arrange
        $bookA = Book::factory()->create(['title' => '吾輩は猫である']);
        $bookB = Book::factory()->create(['title' => '7つの習慣']);
        $genreA = Genre::factory()->create(['name' => '小説']);
        $genreB = Genre::factory()->create(['name' => 'ビジネス']);
        $genreA->books()->attach($bookA);
        $genreB->books()->attach($bookB);

        // Act
        $response = $this->get('/books?genre='.$genreA->id);

        // Assert
        $response->assertOk();
        $response->assertSee($bookA->title);
        $response->assertDontSee($bookB->title);
    }

    public function test_キーワードとジャンルの組み合わせ検索(): void
    {
        // Arrange
        $bookA = Book::factory()->create(['title' => '吾輩は猫である']);
        $bookB = Book::factory()->create(['title' => '世界中で愛される美しすぎる猫図鑑']);
        $genreA = Genre::factory()->create(['name' => '小説']);
        $genreB = Genre::factory()->create(['name' => '図鑑']);
        $genreA->books()->attach($bookA);
        $genreB->books()->attach($bookB);

        // Act
        $response = $this->get('/books?keyword='.'猫'.'&genre='.$genreA->id);

        // Assert
        $response->assertOk();
        $response->assertSee($bookA->title);
        $response->assertDontSee($bookB->title);
    }

    public function test_新しい順に並び替えできる(): void
    {
        // Arrange
        $bookA = Book::factory()->create(['created_at' => now()->subDay(3)]);
        $bookB = Book::factory()->create(['created_at' => now()->subDay(2)]);
        $bookC = Book::factory()->create(['created_at' => now()->subDay(1)]);
        $bookD = Book::factory()->create(['created_at' => now()]);

        // Act
        $response = $this->get('/books?sort=newest');

        // Assert
        $response->assertOk();
        $response->assertSeeInOrder([$bookD->title, $bookC->title, $bookB->title, $bookA->title]);
    }

    public function test_古い順に並び替えできる(): void
    {
        // Arrange
        $bookA = Book::factory()->create(['created_at' => now()->subDay(3)]);
        $bookB = Book::factory()->create(['created_at' => now()->subDay(2)]);
        $bookC = Book::factory()->create(['created_at' => now()->subDay(1)]);
        $bookD = Book::factory()->create(['created_at' => now()]);

        // Act
        $response = $this->get('/books?sort=oldest');

        // Assert
        $response->assertOk();
        $response->assertSeeInOrder([$bookA->title, $bookB->title, $bookC->title, $bookD->title]);
    }

    public function test_タイトル昇順に並び替えできる(): void
    {
        // Arrange
        $bookA = Book::factory()->create(['title' => 'あああああ']);
        $bookB = Book::factory()->create(['title' => 'いいいいい']);
        $bookC = Book::factory()->create(['title' => 'ううううう']);
        $bookD = Book::factory()->create(['title' => 'えええええ']);

        // Act
        $response = $this->get('/books?sort=title');

        // Assert
        $response->assertOk();
        $response->assertSeeInOrder([$bookA->title, $bookB->title, $bookC->title, $bookD->title]);
    }

    public function test_評価順に並び替えできる(): void
    {
        // Arrange
        $bookA = Book::factory()->create(['title' => 'タイトルX']);
        $bookB = Book::factory()->create(['title' => 'タイトルY']);
        $bookC = Book::factory()->create(['title' => 'タイトルZ']);
        $bookD = Book::factory()->create(['title' => 'タイトルW']);

        $reviewA = Review::factory()->create(['book_id' => $bookA->id, 'rating' => 5]);
        $reviewB = Review::factory()->create(['book_id' => $bookB->id, 'rating' => 3]);
        $reviewC = Review::factory()->create(['book_id' => $bookC->id, 'rating' => 1]);

        // Act
        $response = $this->get('/books?sort=rating');

        // Assert
        $response->assertOk();
        $response->assertSeeInOrder([$bookA->title, $bookB->title, $bookC->title, $bookD->title]);
    }

    public function test_不正なsort値でもエラーにならずデフォルトnewestになる(): void
    {
        // Arrange
        $bookA = Book::factory()->create(['created_at' => now()->subDay(3)]);
        $bookB = Book::factory()->create(['created_at' => now()->subDay(2)]);
        $bookC = Book::factory()->create(['created_at' => now()->subDay(1)]);
        $bookD = Book::factory()->create(['created_at' => now()]);

        // Act
        $response = $this->get('/books?sort=hoge');

        // Assert
        $response->assertOk();
        $response->assertSeeInOrder([$bookD->title, $bookC->title, $bookB->title, $bookA->title]);
    }

    public function test_keywordが255文字の場合は_ok(): void
    {
        // Arrange
        $keywordB = str_repeat('a', 255); // 255文字のキーワード
        // Act
        $responseB = $this->get("/books?keyword={$keywordB}");
        // Assert
        $responseB->assertOk();
    }

    public function test_keywordが256文字以上の場合はバリデーションエラーになる(): void
    {
        // Arrange
        $keywordA = str_repeat('a', 256); // 256文字のキーワード
        // Act
        $responseA = $this->get("/books?keyword={$keywordA}");
        // Assert
        $responseA->assertSessionHasErrors('keyword');
    }

    public function test_ジャンル名が不正な値の場合はバリデーションエラーになる(): void
    {
        // Act
        $response = $this->get('/books?genre=hoge');

        // Assert
        $response->assertSessionHasErrors('genre');
    }

    public function test_存在しないジャンル_i_dの場合はバリデーションエラーになる(): void
    {
        // Arrange
        $genre = Genre::factory()->create();

        // Act
        $response = $this->get('/books?genre='.($genre->id + 1));

        // Assert
        $response->assertSessionHasErrors('genre');
    }

    public function test_書籍検索一覧2ページ目のリンク先urlにkeywordが含まれている(): void
    {
        // Arrange
        $book = Book::factory()->count(11)->create(['title' => '吾輩は猫である']);

        // Act
        $response = $this->get('/books?keyword=猫&page=2');

        // Assert
        $response->assertSee('keyword='.urlencode('猫'), false);
    }
}
