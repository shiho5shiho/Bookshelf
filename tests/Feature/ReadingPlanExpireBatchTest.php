<?php

namespace Tests\Feature;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReadingPlanExpireBatchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::parse('2026-01-15'));
    }

    public function test_期日を過ぎた未読計画は失効する(): void
    {
        $plan = ReadingPlan::factory()->create([
            'status' => ReadingPlanStatus::InProgress,
            'target_date' => Carbon::today()->subDay(1),
        ]);

        $this->artisan('reading-plans:expire')->assertSuccessful();

        $this->assertSame(ReadingPlanStatus::Expired, $plan->fresh()->status);
    }

    public function test_期日当日の計画はまだ失効しない(): void
    {
        $plan = ReadingPlan::factory()->create([
            'status' => ReadingPlanStatus::InProgress,
            'target_date' => Carbon::today(),
        ]);

        $this->artisan('reading-plans:expire')->assertSuccessful();

        $this->assertSame(ReadingPlanStatus::InProgress, $plan->fresh()->status);
    }

    public function test_期日が未来の計画は失効しない(): void
    {
        $plan = ReadingPlan::factory()->create([
            'status' => ReadingPlanStatus::InProgress,
            'target_date' => Carbon::today()->addDays(1),
        ]);

        $this->artisan('reading-plans:expire')->assertSuccessful();

        $this->assertSame(ReadingPlanStatus::InProgress, $plan->fresh()->status);
    }

    public function test_読了済みの計画は失効しない(): void
    {
        $plan = ReadingPlan::factory()->create([
            'status' => ReadingPlanStatus::Completed,
            'target_date' => Carbon::today()->subDays(10),
        ]);

        $this->artisan('reading-plans:expire')->assertSuccessful();

        $this->assertSame(ReadingPlanStatus::Completed, $plan->fresh()->status);
    }
}
