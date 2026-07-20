<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    public function test_ジャンルに紐づく書籍を取得できる(): void
    {
        // Arrange
        $genre = Genre::factory()->create();
        $expectedBooks = Book::factory()->count(3)->create();
        Book::factory()->create(); // 紐づけない書籍
        $genre->books()->attach($expectedBooks->pluck('id'));

        // Act
        $books = $genre->books;

        // Assert
        $this->assertCount(3, $books);
        $this->assertEqualsCanonicalizing(
            $expectedBooks->pluck('id')->all(),
            $books->pluck('id')->all()
        );
    }

    /**
     * ジャンル一覧画面での表示、および削除制限の判定に使う。
     */
    public function test_ジャンルに紐づく書籍数を取得できる(): void
    {
        // Arrange
        $genre = Genre::factory()->create();
        $genre->books()->attach(Book::factory()->count(2)->create()->pluck('id'));

        // Act
        $result = Genre::withCount('books')->find($genre->id);

        // Assert
        $this->assertSame(2, $result->books_count);
    }

    /**
     * 「紐づく書籍がある場合は削除を制限する」仕様の境界値。
     */
    public function test_書籍が紐づいていないジャンルの書籍数は0になる(): void
    {
        // Arrange
        $genre = Genre::factory()->create();

        // Act
        $result = Genre::withCount('books')->find($genre->id);

        // Assert
        $this->assertSame(0, $result->books_count);
    }
}
