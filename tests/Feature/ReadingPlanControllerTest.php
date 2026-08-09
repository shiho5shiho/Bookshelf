<?php

namespace Tests\Feature;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingPlanControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_未認証ユーザーはログイン画面にリダイレクトされる(): void
    {
        // Act
        $response = $this->get('/reading-plans');

        // Assert
        $response->assertRedirect('/login');
    }

    public function test_自分の読書計画のみ一覧に表示される(): void
    {
        // Arrange
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $myPlan = ReadingPlan::factory()->for($user)->for(Book::factory())->create();
        ReadingPlan::factory()->for($otherUser)->for(Book::factory())->create();

        // Act
        $response = $this->actingAs($user)->get('/reading-plans');

        // Assert
        $response->assertViewHas('readingPlans', function ($readingPlans) use ($myPlan): bool {
            return $readingPlans->count() === 1
                && $readingPlans->first()->id === $myPlan->id;
        });
    }

    public function test_statusで絞り込める(): void
    {
        // Arrange
        $user = User::factory()->create();
        ReadingPlan::factory()->for($user)->for(Book::factory())->create(['status' => ReadingPlanStatus::InProgress]);
        ReadingPlan::factory()->for($user)->for(Book::factory())->create(['status' => ReadingPlanStatus::Completed]);

        // Act
        $response = $this->actingAs($user)->get('/reading-plans?status=completed');

        // Assert
        $response->assertViewHas('readingPlans', function ($readingPlans): bool {
            return $readingPlans->count() === 1
                && $readingPlans->first()->status === ReadingPlanStatus::Completed;
        });
    }

    public function test_読書計画を登録できる(): void
    {
        // Arrange
        $user = User::factory()->create();
        $book = Book::factory()->create();

        // Act
        $response = $this->actingAs($user)->post('/reading-plans', [
            'book_id' => $book->id,
            'target_date' => now()->addDays(7)->format('Y-m-d'),
        ]);

        // Assert
        $response->assertRedirect(route('reading-plans.index'));
        $this->assertDatabaseHas('reading_plans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => ReadingPlanStatus::InProgress->value,
        ]);
    }

    public function test_過去日は登録できない(): void
    {
        // Arrange
        $user = User::factory()->create();
        $book = Book::factory()->create();

        // Act
        $response = $this->actingAs($user)->post('/reading-plans', [
            'book_id' => $book->id,
            'target_date' => now()->subDay()->format('Y-m-d'),
        ]);

        // Assert
        $response->assertSessionHasErrors('target_date');
        $this->assertDatabaseMissing('reading_plans', ['book_id' => $book->id]);
    }

    public function test_進行中の計画がある同一書籍は重複登録できない(): void
    {
        // Arrange
        $user = User::factory()->create();
        $book = Book::factory()->create();
        ReadingPlan::factory()->for($user)->for($book)->create(['status' => ReadingPlanStatus::InProgress]);

        // Act
        $response = $this->actingAs($user)->post('/reading-plans', [
            'book_id' => $book->id,
            'target_date' => now()->addDays(7)->format('Y-m-d'),
        ]);

        // Assert
        $response->assertSessionHasErrors('book_id');
        $this->assertDatabaseCount('reading_plans', 1);
    }

    public function test_読了済みの書籍は再度計画を作成できる(): void
    {
        // Arrange
        $user = User::factory()->create();
        $book = Book::factory()->create();
        ReadingPlan::factory()->for($user)->for($book)->create(['status' => ReadingPlanStatus::Completed]);

        // Act
        $response = $this->actingAs($user)->post('/reading-plans', [
            'book_id' => $book->id,
            'target_date' => now()->addDays(7)->format('Y-m-d'),
        ]);

        // Assert
        $response->assertRedirect(route('reading-plans.index'));
        $this->assertDatabaseCount('reading_plans', 2);
    }

    public function test_作成者は期日を更新できる(): void
    {
        // Arrange
        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->for($user)->for(Book::factory())->create(['status' => ReadingPlanStatus::InProgress]);
        $newDate = now()->addDays(14)->format('Y-m-d');

        // Act
        $response = $this->actingAs($user)->put("/reading-plans/{$plan->id}", [
            'target_date' => $newDate,
        ]);

        // Assert
        $response->assertRedirect(route('reading-plans.index'));
        $this->assertTrue(
            $plan->fresh()->target_date->isSameDay($newDate)
        );
    }

    public function test_失効した計画を更新すると進行中に復帰する(): void
    {
        // Arrange
        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->for($user)->for(Book::factory())->create(['status' => ReadingPlanStatus::Expired]);

        // Act
        $response = $this->actingAs($user)->put("/reading-plans/{$plan->id}", [
            'target_date' => now()->addDays(7)->format('Y-m-d'),
        ]);

        // Assert
        $response->assertRedirect(route('reading-plans.index'));
        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'status' => ReadingPlanStatus::InProgress->value,
        ]);
    }

    public function test_読了済みの計画は更新できず403になる(): void
    {
        // Arrange
        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->for($user)->for(Book::factory())->create(['status' => ReadingPlanStatus::Completed]);

        // Act
        $response = $this->actingAs($user)->put("/reading-plans/{$plan->id}", [
            'target_date' => now()->addDays(7)->format('Y-m-d'),
        ]);

        // Assert
        $response->assertForbidden();
    }

    public function test_作成者以外は更新すると403になる(): void
    {
        // Arrange
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $plan = ReadingPlan::factory()->for($otherUser)->for(Book::factory())->create(['status' => ReadingPlanStatus::InProgress]);

        // Act
        $response = $this->actingAs($user)->put("/reading-plans/{$plan->id}", [
            'target_date' => now()->addDays(7)->format('Y-m-d'),
        ]);

        // Assert
        $response->assertForbidden();
    }

    public function test_作成者は削除できる(): void
    {
        // Arrange
        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->for($user)->for(Book::factory())->create();

        // Act
        $response = $this->actingAs($user)->delete("/reading-plans/{$plan->id}");

        // Assert
        $response->assertRedirect(route('reading-plans.index'));
        $this->assertDatabaseMissing('reading_plans', ['id' => $plan->id]);
    }

    public function test_作成者以外は削除すると403になる(): void
    {
        // Arrange
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $plan = ReadingPlan::factory()->for($otherUser)->for(Book::factory())->create();

        // Act
        $response = $this->actingAs($user)->delete("/reading-plans/{$plan->id}");

        // Assert
        $response->assertForbidden();
        $this->assertDatabaseHas('reading_plans', ['id' => $plan->id]);
    }

    public function test_進行中の計画を読了にできる(): void
    {
        // Arrange
        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->for($user)->for(Book::factory())->create(['status' => ReadingPlanStatus::InProgress]);

        // Act
        $response = $this->actingAs($user)->post("/reading-plans/{$plan->id}/complete");

        // Assert
        $response->assertRedirect(route('reading-plans.index'));
        $plan->refresh();
        $this->assertSame(ReadingPlanStatus::Completed, $plan->status);
        $this->assertNotNull($plan->completed_at);
    }

    public function test_失効した計画も直接読了にできる(): void
    {
        // Arrange
        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->for($user)->for(Book::factory())->create(['status' => ReadingPlanStatus::Expired]);

        // Act
        $response = $this->actingAs($user)->post("/reading-plans/{$plan->id}/complete");

        // Assert
        $response->assertRedirect(route('reading-plans.index'));
        $plan->refresh();
        $this->assertSame(ReadingPlanStatus::Completed, $plan->status);
    }

    public function test_作成者以外は読了操作すると403になる(): void
    {
        // Arrange
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $plan = ReadingPlan::factory()->for($otherUser)->for(Book::factory())->create(['status' => ReadingPlanStatus::InProgress]);

        // Act
        $response = $this->actingAs($user)->post("/reading-plans/{$plan->id}/complete");

        // Assert
        $response->assertForbidden();
    }
}
