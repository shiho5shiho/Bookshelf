<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use App\Notifications\ReadingPlanReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_未認証ユーザーはログイン画面にリダイレクトされる(): void
    {
        // Act
        $response = $this->get('/notifications');

        // Assert
        $response->assertRedirect('/login');
    }

    public function test_自分の通知のみ一覧に表示される(): void
    {
        // Arrange
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $plan = ReadingPlan::factory()->for($user)->for(Book::factory())->create();

        $user->notify(new ReadingPlanReminder($plan, 'on_due_date'));
        $otherUser->notify(new ReadingPlanReminder(
            ReadingPlan::factory()->for($otherUser)->for(Book::factory())->create(),
            'on_due_date'
        ));

        // Act
        $response = $this->actingAs($user)->get('/notifications');

        // Assert
        $response->assertViewHas('notifications', function ($notifications) use ($user): bool {
            return $notifications->count() === 1
                && $notifications->first()->notifiable_id === $user->id;
        });
    }

    public function test_通知の内容が正しく保存される(): void
    {
        // Arrange
        $user = User::factory()->create();
        $book = Book::factory()->create(['title' => 'テスト駆動開発']);
        $plan = ReadingPlan::factory()->for($user)->for($book)->create();

        // Act
        $user->notify(new ReadingPlanReminder($plan, 'three_days_before'));

        // Assert
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
            'type' => ReadingPlanReminder::class,
        ]);
        $notification = $user->notifications()->first();
        $this->assertSame('three_days_before', $notification->data['timing']);
        $this->assertStringContainsString('テスト駆動開発', $notification->data['body']);
    }

    public function test_未読の通知を既読にできる(): void
    {
        // Arrange
        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->for($user)->for(Book::factory())->create();
        $user->notify(new ReadingPlanReminder($plan, 'on_due_date'));
        $notification = $user->notifications()->first();

        // Act
        $response = $this->actingAs($user)->post("/notifications/{$notification->id}/read");

        // Assert
        $response->assertRedirect(route('notifications.index'));
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_他人の通知を既読にしようとすると404になる(): void
    {
        // Arrange
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $plan = ReadingPlan::factory()->for($otherUser)->for(Book::factory())->create();
        $otherUser->notify(new ReadingPlanReminder($plan, 'on_due_date'));
        $notification = $otherUser->notifications()->first();

        // Act
        $response = $this->actingAs($user)->post("/notifications/{$notification->id}/read");

        // Assert
        $response->assertNotFound();
        $this->assertNull($notification->fresh()->read_at);
    }
}
