<?php

namespace Tests\Feature\Console;

use Illuminate\Console\Scheduling\Schedule;
use Tests\TestCase;

class KernelTest extends TestCase
{
    public function test_reading_plans関連のコマンドが毎日実行されるようスケジュール登録されている(): void
    {
        // Arrange
        $schedule = app(Schedule::class);
        $commands = collect($schedule->events())->map(fn ($event) => $event->command);

        // Assert
        $this->assertTrue($commands->contains(fn ($command) => str_contains($command, 'reading-plans:send-reminders')));
        $this->assertTrue($commands->contains(fn ($command) => str_contains($command, 'reading-plans:expire')));
    }
}
