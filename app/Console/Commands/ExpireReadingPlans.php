<?php

namespace App\Console\Commands;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ExpireReadingPlans extends Command
{
    /**
     * @var string
     */
    protected $signature = 'reading-plans:expire';

    /**
     * @var string
     */
    protected $description = '期日を4日以上過ぎた未読の読書計画を、自動的に失効状態へ変更する';

    public function handle(): int
    {
        // 期日+3日（＝3日後リマインダーの日）はまだ失効させない。
        // その翌日（期日+4日）以降を失効対象にする。
        // target_date < (今日 - 3日)  ⇔  期日を4日以上過ぎている
        $threshold = Carbon::today()->subDays(3);

        $expired = ReadingPlan::query()
            ->where('status', ReadingPlanStatus::InProgress)
            ->whereDate('target_date', '<', $threshold)
            ->update(['status' => ReadingPlanStatus::Expired->value]);

        $this->info("{$expired}件の読書計画を失効にしました。");

        return self::SUCCESS;
    }
}
