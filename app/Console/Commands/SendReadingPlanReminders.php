<?php

namespace App\Console\Commands;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use App\Notifications\ReadingPlanReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendReadingPlanReminders extends Command
{
    /**
     * @var string
     */
    protected $signature = 'reading-plans:send-reminders';

    /**
     * @var string
     */
    protected $description = '読書計画に、期日3日前・当日のリマインダーと、失効から3日後の再エンゲージメント通知を送信する';

    public function handle(): int
    {
        $today = Carbon::today();

        // 「タイミング名」=> ['status' => 対象ステータス, 'date' => 対象となる日付]
        $schedule = [
            'three_days_before' => [
                'status' => ReadingPlanStatus::InProgress,
                'date' => $today->copy()->addDays(3),
            ],
            'on_due_date' => [
                'status' => ReadingPlanStatus::InProgress,
                'date' => $today->copy(),
            ],
            'three_days_after' => [
                'status' => ReadingPlanStatus::Expired,
                'date' => $today->copy()->subDays(4), // 失効(期日+1)から3日後 = 期日+4
            ],
        ];

        $sent = 0;

        foreach ($schedule as $timing => $condition) {
            $plans = ReadingPlan::query()
                ->where('status', $condition['status'])
                ->whereDate('target_date', $condition['date'])
                ->with(['user', 'book'])
                ->get();

            foreach ($plans as $plan) {
                $alreadySent = $plan->user->notifications()
                    ->where('type', ReadingPlanReminder::class)
                    ->where('data->reading_plan_id', $plan->id)
                    ->where('data->timing', $timing)
                    ->exists();

                if ($alreadySent) {
                    continue;
                }

                $plan->user->notify(new ReadingPlanReminder($plan, $timing));
                $sent++;
            }
        }

        $this->info("リマインダー通知を{$sent}件送信しました。");

        return self::SUCCESS;
    }
}
