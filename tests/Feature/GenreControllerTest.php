<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_認証ユーザーはジャンル一覧を閲覧できる(): void
    {
        // Arrange
        $user = User::factory()->create();
        $genre = Genre::factory()->create(['name' => '技術書']);
        $genre->books()->attach(Book::factory()->count(2)->create());

        // Act
        $response = $this->actingAs($user)->get('/genres');

        // Assert
        $response->assertOk();
        $response->assertSee('技術書');
        $response->assertViewHas('genres', function ($genres) {
            return $genres->first()->books_count === 2;
        });
    }

    public function test_ジャンル詳細に紐づく書籍が表示される(): void
    {
        // Arrange
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $genre->books()->attach(Book::factory()->count(11)->create());

        // Act
        $response = $this->actingAs($user)->get("/genres/{$genre->id}");

        // Assert
        $response->assertOk();
        $response->assertViewHas('books', function ($books) {
            return $books->count() === 10           // このページは10件
                && $books->total() === 11           // 全体では11件
                && $books->lastPage() === 2;        // 全2ページ
        });
    }

    public function test_ジャンル登録画面を表示できる(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)->get('/genres/create');

        // Assert
        $response->assertOk();
    }

    public function test_ジャンルを登録できる(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)->post('/genres', ['name' => '技術書']);

        // Assert
        $this->assertDatabaseHas('genres', [
            'name' => '技術書',
        ]);

        $response->assertRedirect(route('genres.index'));
    }

    public function test_ジャンル名が未入力の場合はバリデーションエラーになる(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)->post('/genres', ['name' => '']);

        // Assert
        $response->assertSessionHasErrors(['name' => 'ジャンル名を入力してください。']);
        $this->assertDatabaseEmpty('genres');
    }

    public function test_重複するジャンル名は登録できない(): void
    {
        // Arrange
        Genre::factory()->create(['name' => '技術書']);  // 先に1件作る

        $user = User::factory()->create();
        $data = [
            'name' => '技術書',  // 同じジャンル名で登録を試みる
        ];

        // Act
        $response = $this->actingAs($user)->post('/genres', $data);

        // Assert
        $response->assertSessionHasErrors(['name' => 'このジャンル名は既に登録されています。']);
        $this->assertDatabaseCount('genres', 1);
    }

    public function test_ジャンル編集画面に現在の値が初期表示される(): void
    {
        // Arrange
        $user = User::factory()->create();
        $genre = Genre::factory()->create(['name' => '技術書']);

        // Act
        $response = $this->actingAs($user)->get("/genres/{$genre->id}/edit");

        // Assert
        $response->assertOk();
        $response->assertSee('技術書');
    }

    public function test_ジャンルを更新できる(): void
    {
        // Arrange
        $user = User::factory()->create();
        $genre = Genre::factory()->create(['name' => '更新前']);

        $data = [
            'name' => '更新後',
        ];

        // Act
        $response = $this->actingAs($user)->put("/genres/{$genre->id}", $data);

        // Assert
        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => '更新後',
        ]);

        $response->assertRedirect(route('genres.index'));
    }

    public function test_更新時の重複チェックは自身を除外する(): void
    {
        // Arrange
        $user = User::factory()->create();
        $genre1 = Genre::factory()->create(['name' => 'ジャンル1']);
        $genre2 = Genre::factory()->create(['name' => 'ジャンル2']);

        $data = [
            'name' => 'ジャンル1',  // genre1 と同じ名前で更新を試みる
        ];

        // Act
        $response = $this->actingAs($user)->put("/genres/{$genre2->id}", $data);

        // Assert
        $response->assertSessionHasErrors(['name' => 'このジャンル名は既に登録されています。']);
        $this->assertDatabaseHas('genres', [
            'id' => $genre2->id,
            'name' => 'ジャンル2',  // 更新されていないことを確認
        ]);
    }

    public function test_書籍が紐づいていないジャンルは削除できる(): void
    {
        // Arrange
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        // Act
        $response = $this->actingAs($user)->delete("/genres/{$genre->id}");

        // Assert
        $this->assertDatabaseMissing('genres', [
            'id' => $genre->id,
        ]);

        $response->assertRedirect(route('genres.index'));
    }

    public function test_書籍が紐づいているジャンルは削除できない(): void
    {
        // Arrange
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $genre->books()->attach(Book::factory()->create());

        // Act
        $response = $this->actingAs($user)->delete("/genres/{$genre->id}");

        // Assert
        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
        ]);

        $response->assertSessionHas('error', 'このジャンルには書籍が紐づいているため削除できません。');
        $response->assertRedirect(route('genres.index'));
    }
}
