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
    protected $description = '未読の読書計画に、期日3日前・当日・3日後のリマインダー通知を送信する';

    public function handle(): int
    {
        $today = Carbon::today();

        // 「タイミング名」=> 「その通知を出す期日」
        // 例：three_days_before は、期日が今日から3日後の計画に送る
        $schedule = [
            'three_days_before' => $today->copy()->addDays(3),
            'on_due_date' => $today->copy(),
            'three_days_after' => $today->copy()->subDays(3),
        ];

        $sent = 0;

        foreach ($schedule as $timing => $targetDate) {
            // 主条件：未読（in_progress）の計画だけが対象。
            // 日付は「ちょうどこの日」の完全一致で拾う（範囲にしないことで、日をまたいだ重複送信を防ぐ）。
            $plans = ReadingPlan::query()
                ->where('status', ReadingPlanStatus::InProgress)
                ->whereDate('target_date', $targetDate)
                ->with(['user', 'book'])
                ->get();

            foreach ($plans as $plan) {
                // 同じ計画×同じタイミングの通知が既にあれば送らない
                // （同じ日にバッチを2回叩いた場合の保険。完全一致だけでは同日多重実行を防げないため）
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
