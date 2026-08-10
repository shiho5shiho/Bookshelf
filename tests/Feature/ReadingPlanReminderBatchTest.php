<?php

namespace Tests\Feature;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReadingPlanReminderBatchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // 「今日」を固定して、期日の相対計算を安定させる
        $this->travelTo(Carbon::parse('2026-01-15'));
    }

    public function test_期日3日前の未読計画にリマインダー通知が送られる(): void
    {
        $plan = ReadingPlan::factory()->create([
            'status' => ReadingPlanStatus::InProgress,
            'target_date' => Carbon::today()->addDays(3),
        ]);

        $this->artisan('reading-plans:send-reminders')->assertSuccessful();

        $notifications = $plan->user->notifications()->get();
        $this->assertCount(1, $notifications);
        $this->assertSame('three_days_before', $notifications->first()->data['timing']);
    }

    public function test_期日当日の未読計画にリマインダー通知が送られる(): void
    {
        $plan = ReadingPlan::factory()->create([
            'status' => ReadingPlanStatus::InProgress,
            'target_date' => Carbon::today(),
        ]);

        $this->artisan('reading-plans:send-reminders')->assertSuccessful();

        $this->assertSame('on_due_date', $plan->user->notifications()->first()->data['timing']);
    }

    public function test_期日3日後の未読計画にリマインダー通知が送られる(): void
    {
        $plan = ReadingPlan::factory()->create([
            'status' => ReadingPlanStatus::InProgress,
            'target_date' => Carbon::today()->subDays(3),
        ]);

        $this->artisan('reading-plans:send-reminders')->assertSuccessful();

        $this->assertSame('three_days_after', $plan->user->notifications()->first()->data['timing']);
    }

    public function test_対象日でない未読計画には通知が送られない(): void
    {
        $plan = ReadingPlan::factory()->create([
            'status' => ReadingPlanStatus::InProgress,
            'target_date' => Carbon::today()->addDays(5),
        ]);

        $this->artisan('reading-plans:send-reminders')->assertSuccessful();

        $this->assertCount(0, $plan->user->notifications()->get());
    }

    public function test_読了済みの計画には通知が送られない(): void
    {
        $plan = ReadingPlan::factory()->create([
            'status' => ReadingPlanStatus::Completed,
            'target_date' => Carbon::today(),
        ]);

        $this->artisan('reading-plans:send-reminders')->assertSuccessful();

        $this->assertCount(0, $plan->user->notifications()->get());
    }

    public function test_同じ日に2回実行しても通知は重複しない(): void
    {
        $plan = ReadingPlan::factory()->create([
            'status' => ReadingPlanStatus::InProgress,
            'target_date' => Carbon::today()->addDays(3),
        ]);

        $this->artisan('reading-plans:send-reminders')->assertSuccessful();
        $this->artisan('reading-plans:send-reminders')->assertSuccessful();

        $this->assertCount(1, $plan->user->notifications()->get());
    }
}
