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
    protected $description = '期日を過ぎた未読の読書計画を、自動的に失効状態へ変更する';

    public function handle(): int
    {
        // 期日を過ぎていれば（today未満なら）即座に失効させる。
        $expired = ReadingPlan::query()
            ->where('status', ReadingPlanStatus::InProgress)
            ->whereDate('target_date', '<', Carbon::today())
            ->update(['status' => ReadingPlanStatus::Expired->value]);

        $this->info("{$expired}件の読書計画を失効にしました。");

        return self::SUCCESS;
    }
}
