<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BookIsbnControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_有効な_isb_nで書籍情報が5キーで返る(): void
    {
        // Arrange
        $user = User::factory()->create();
        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([
                'items' => [
                    [
                        'volumeInfo' => [
                            'title' => 'テスト駆動開発',
                            'authors' => ['Kent Beck'],
                            'description' => 'TDDの入門書。',
                            'imageLinks' => ['thumbnail' => 'https://example.com/image.jpg'],
                            'publishedDate' => '2003-01-01',
                        ],
                    ],
                ],
            ], 200),
        ]);

        // Act
        $response = $this->actingAs($user)
            ->getJson('/books/isbn/9784798124582');

        // Assert
        $response->assertOk();
        $response->assertJson([
            'title' => 'テスト駆動開発',
            'author' => 'Kent Beck',
            'description' => 'TDDの入門書。',
            'image_url' => 'https://example.com/image.jpg',
            'published_date' => '2003-01-01',
        ]);
    }

    public function test_該当書籍がない場合エラーを返す(): void
    {
        // Arrange
        $user = User::factory()->create();
        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response(['items' => []], 200),
        ]);

        // Act
        $response = $this->actingAs($user)
            ->getJson('/books/isbn/9784798124582');

        // Assert
        $response->assertStatus(404);
        $response->assertJsonStructure(['error']);
    }

    public function test_外部_ap_i障害時にエラーを返す(): void
    {
        // Arrange
        $user = User::factory()->create();
        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([], 500),
        ]);

        // Act
        $response = $this->actingAs($user)
            ->getJson('/books/isbn/9784798124582');

        // Assert
        $response->assertStatus(502);
        $response->assertJsonStructure(['error']);
    }

    public function test_桁数が不正な場合は外部_ap_iを呼ばずにエラーを返す(): void
    {
        // Arrange
        $user = User::factory()->create();
        Http::fake(); // 呼ばれたら失敗させる（未定義URLはfakeが例外を出す設定にはしていないが、assertNothingSentで検証する）

        // Act
        $response = $this->actingAs($user)
            ->getJson('/books/isbn/12345');

        // Assert
        $response->assertStatus(422);
        $response->assertJsonStructure(['error']);
        Http::assertNothingSent();
    }
}
