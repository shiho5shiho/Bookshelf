<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_正常な入力で会員登録できる(): void
    {
        // Arrange
        $data = [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // Act
        $response = $this->post('/register', $data);

        // Assert
        $this->assertDatabaseHas('users', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
        ]);
        $this->assertAuthenticated();
    }

    public function test_会員登録後は書籍一覧にリダイレクトされる(): void
    {
        // Arrange
        $data = [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // Act
        $response = $this->post('/register', $data);

        // Assert
        $response->assertRedirect('/books');
    }

    public static function 会員登録の異常系(): array
    {
        return [
            '名前が空' => [
                ['name' => '', 'email' => 'test@example.com', 'password' => 'password123', 'password_confirmation' => 'password123'],
                ['name' => 'お名前を入力してください。'],
            ],
            'メールが空' => [
                ['name' => 'テスト太郎', 'email' => '', 'password' => 'password123', 'password_confirmation' => 'password123'],
                ['email' => 'メールアドレスを入力してください。'],
            ],
            'メール形式が不正' => [
                ['name' => 'テスト太郎', 'email' => 'invalid', 'password' => 'password123', 'password_confirmation' => 'password123'],
                ['email' => '正しいメールアドレスを入力してください。'],
            ],
            'パスワードが8文字未満' => [
                ['name' => 'テスト太郎', 'email' => 'test@example.com', 'password' => 'pass', 'password_confirmation' => 'pass'],
                ['password' => 'パスワードは8文字以上で入力してください。'],
            ],
            'パスワード確認が不一致' => [
                ['name' => 'テスト太郎', 'email' => 'test@example.com', 'password' => 'password123', 'password_confirmation' => 'different'],
                ['password' => 'パスワードが（確認用と）一致しません。'],
            ],
        ];
    }

    #[DataProvider('会員登録の異常系')]
    public function test_不正な入力では会員登録できない(array $data, array $errors): void
    {
        // Act
        $response = $this->post('/register', $data);

        // Assert
        $response->assertSessionHasErrors($errors);
        $this->assertGuest();
    }

    public function test_既存と重複するメールアドレスでは登録できない(): void
    {
        // Arrange
        User::factory()->create(['email' => 'existing@example.com']);  // 先に1人作る

        $data = [
            'name' => 'テスト太郎',
            'email' => 'existing@example.com',  // 同じメールで登録を試みる
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // Act
        $response = $this->post('/register', $data);

        // Assert
        $response->assertSessionHasErrors(['email' => 'このメールアドレスは既に登録されています。']);
        $this->assertGuest();
    }

    public function test_正しい認証情報でログインできる(): void
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Act
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // Assert
        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/books');
    }

    public function test_誤った認証情報ではログインできない(): void
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Act
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        // Assert
        $this->assertGuest();
        $response->assertSessionHasErrors([
            'email' => 'メールアドレスまたはパスワードが正しくありません。',
        ]);
    }

    public function test_ログイン状態からログアウトできる(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)->post('/logout');

        // Assert
        $this->assertGuest();
        $response->assertRedirect('/login');
    }

    public static function 認証画面(): array
    {
        return [
            'ログイン画面' => ['/login'],
            '会員登録画面' => ['/register'],
        ];
    }

    #[DataProvider('認証画面')]
    public function test_ログイン済みユーザーが認証画面にアクセスするとリダイレクトされる(string $url): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)->get($url);

        // Assert
        $response->assertRedirect('/books');
    }
}
