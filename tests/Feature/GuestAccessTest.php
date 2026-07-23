<?php

namespace Tests\Feature;

use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class GuestAccessTest extends TestCase
{
    use RefreshDatabase;

    public static function 認証必須GETページ(): array
    {
        return [
            '書籍登録画面' => ['/books/create'],
            '書籍編集画面' => ['/books/1/edit'],
            'ジャンル一覧' => ['/genres'],
            'ジャンル登録' => ['/genres/create'],
            'ジャンル詳細' => ['/genres/1'],
            'ジャンル編集' => ['/genres/1/edit'],
            'レビュー編集' => ['/reviews/1/edit'],
            'お気に入り一覧' => ['/favorites'],
        ];
    }

    #[DataProvider('認証必須GETページ')]
    public function test_未認証ユーザーは認証必須画面にアクセスするとログイン画面にリダイレクトされる(string $url): void
    {
        // Act
        $response = $this->get($url);

        // Assert
        $response->assertRedirect('/login');
    }

    public static function 認証必須更新系(): array
    {
        return [
            '書籍登録' => ['post', '/books'],
            '書籍更新' => ['put', '/books/1'],
            '書籍削除' => ['delete', '/books/1'],
            'ジャンル登録' => ['post', '/genres'],
            'ジャンル更新' => ['put', '/genres/1'],
            'ジャンル削除' => ['delete', '/genres/1'],
            'レビュー投稿' => ['post', '/books/1/reviews'],
            'レビュー更新' => ['put', '/reviews/1'],
            'レビュー削除' => ['delete', '/reviews/1'],
            'お気に入りトグル' => ['post', '/books/1/favorites'],
            'いいねトグル' => ['post', '/reviews/1/like'],
        ];
    }

    #[DataProvider('認証必須更新系')]
    public function test_未認証ユーザーは認証必須の更新系リクエストを実行できない(string $method, string $url): void
    {
        // Act
        $response = $this->$method($url);

        // Assert
        $response->assertRedirect('/login');
    }

    // 200が返る公開ページ。　※トップと書籍詳細は事情が異なるため別メソッドで検証
    public static function 認証不要GETページ(): array
    {
        return [
            '書籍一覧画面' => ['/books'],
            'ランキング画面' => ['/ranking'],
        ];
    }

    #[DataProvider('認証不要GETページ')]
    public function test_未認証ユーザーは認証不要画面にアクセスできる(string $url): void
    {
        // Act
        $response = $this->get($url);

        // Assert
        $response->assertOk();
    }

    // トップは書籍一覧へリダイレクトされる（web.php で redirect）。
    // followingRedirects() で追跡し、最終的に200が返ることを検証する
    public function test_未認証ユーザーはトップページにアクセスできる(): void
    {
        // Act
        $response = $this->followingRedirects()->get('/');

        // Assert
        $response->assertOk();
    }

    // 書籍詳細は実レコードが要るので独立
    public function test_未認証ユーザーは書籍詳細を閲覧できる(): void
    {
        // Arrange
        $book = Book::factory()->create();

        // Act
        $response = $this->get("/books/{$book->id}");

        // Assert
        $response->assertOk();
    }
}
