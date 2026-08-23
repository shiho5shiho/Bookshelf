<?php

namespace Tests\Feature;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use App\Models\User;
use Database\Seeders\BookSeeder;
use Database\Seeders\GenreSeeder;
use Database\Seeders\ReadingPlanSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingPlanSeederTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ReadingPlanSeederの実行に必要な前提データ（ユーザー・ジャンル・書籍）を用意する。
     */
    private function seedPrerequisites(): void
    {
        $this->seed(UserSeeder::class);
        $this->seed(GenreSeeder::class);
        $this->seed(BookSeeder::class);
    }

    public function test_reading_plan_seeder実行後に8件のデータが作成される(): void
    {
        // Arrange
        $this->seedPrerequisites();

        // Act
        $this->seed(ReadingPlanSeeder::class);

        // Assert
        $this->assertSame(8, ReadingPlan::count());
    }

    public function test_山田太郎に7件_鈴木花子に1件のデータが割り当てられる(): void
    {
        // Arrange
        $this->seedPrerequisites();
        $yamada = User::where('email', 'yamada@example.com')->first();
        $suzuki = User::where('email', 'suzuki@example.com')->first();

        // Act
        $this->seed(ReadingPlanSeeder::class);

        // Assert
        $this->assertSame(7, ReadingPlan::where('user_id', $yamada->id)->count());
        $this->assertSame(1, ReadingPlan::where('user_id', $suzuki->id)->count());
    }

    public function test_in_progressのデータが5件作成される(): void
    {
        // Arrange
        $this->seedPrerequisites();

        // Act
        $this->seed(ReadingPlanSeeder::class);

        // Assert
        $this->assertSame(5, ReadingPlan::where('status', ReadingPlanStatus::InProgress)->count());
    }

    public function test_completedのデータが1件_completed_atも設定されている(): void
    {
        // Arrange
        $this->seedPrerequisites();

        // Act
        $this->seed(ReadingPlanSeeder::class);

        // Assert
        $completed = ReadingPlan::where('status', ReadingPlanStatus::Completed)->get();
        $this->assertCount(1, $completed);
        $this->assertNotNull($completed->first()->completed_at);
    }

    public function test_expiredのデータが2件作成される(): void
    {
        // Arrange
        $this->seedPrerequisites();

        // Act
        $this->seed(ReadingPlanSeeder::class);

        // Assert
        $this->assertSame(2, ReadingPlan::where('status', ReadingPlanStatus::Expired)->count());
    }

    public function test_リマインダー3パターンの期日が正しく設定されている(): void
    {
        // Arrange
        $this->seedPrerequisites();
        $yamada = User::where('email', 'yamada@example.com')->first();
        $today = today();

        // Act
        $this->seed(ReadingPlanSeeder::class);

        // Assert
        $this->assertSame(
            1,
            ReadingPlan::where('user_id', $yamada->id)
                ->where('status', ReadingPlanStatus::InProgress)
                ->whereDate('target_date', $today->copy()->addDays(3))
                ->count()
        );
        $this->assertSame(
            1,
            ReadingPlan::where('user_id', $yamada->id)
                ->where('status', ReadingPlanStatus::InProgress)
                ->whereDate('target_date', $today->copy())
                ->count()
        );
        $this->assertSame(
            1,
            ReadingPlan::where('user_id', $yamada->id)
                ->where('status', ReadingPlanStatus::Expired)
                ->whereDate('target_date', $today->copy()->subDays(4))
                ->count()
        );
    }

    public function test_失効バッチ対象の期日が正しく設定されている(): void
    {
        // Arrange
        $this->seedPrerequisites();
        $yamada = User::where('email', 'yamada@example.com')->first();
        $today = today();

        // Act
        $this->seed(ReadingPlanSeeder::class);

        // Assert
        $this->assertSame(
            1,
            ReadingPlan::where('user_id', $yamada->id)
                ->where('status', ReadingPlanStatus::InProgress)
                ->whereDate('target_date', '<', $today->copy())
                ->count()
        );
    }

    public function test_鈴木花子の期日はリマインダー_失効どちらの条件にも一致しない(): void
    {
        // Arrange
        $this->seedPrerequisites();
        $suzuki = User::where('email', 'suzuki@example.com')->first();
        $today = today();

        // Act
        $this->seed(ReadingPlanSeeder::class);

        // Assert
        $suzukiPlan = ReadingPlan::where('user_id', $suzuki->id)->first();
        $this->assertNotEquals($today->copy()->addDays(3)->toDateString(), $suzukiPlan->target_date->toDateString());
        $this->assertNotEquals($today->copy()->toDateString(), $suzukiPlan->target_date->toDateString());
        $this->assertNotEquals($today->copy()->subDays(4)->toDateString(), $suzukiPlan->target_date->toDateString());
        $this->assertFalse($suzukiPlan->target_date->lt($today->copy()));
    }

    public function test_同じuser_idとbook_idの組み合わせは重複作成されない(): void
    {
        // Arrange
        $this->seedPrerequisites();

        // Act
        $this->seed(ReadingPlanSeeder::class);
        $this->seed(ReadingPlanSeeder::class); // 2回実行

        // Assert
        $this->assertSame(8, ReadingPlan::count());
    }
}
