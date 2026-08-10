<?php

namespace App\Notifications;

use App\Models\ReadingPlan;
use Illuminate\Notifications\Notification;

class ReadingPlanReminder extends Notification
{
    /**
     * @param  'three_days_before'|'on_due_date'|'three_days_after'  $timing
     */
    public function __construct(
        private readonly ReadingPlan $readingPlan,
        private readonly string $timing,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, string>
     */
    public function toArray(object $notifiable): array
    {
        $bookTitle = $this->readingPlan->book->title;
        $targetDate = $this->readingPlan->target_date->format('Y年n月j日');

        $message = match ($this->timing) {
            'three_days_before' => [
                'title' => '読了期限が近づいています',
                'body' => "「{$bookTitle}」の読了期限は{$targetDate}です（あと3日）。",
            ],
            'on_due_date' => [
                'title' => '本日が読了期限です',
                'body' => "「{$bookTitle}」の読了期限は本日（{$targetDate}）です。",
            ],
            'three_days_after' => [
                'title' => '読了期限を過ぎています',
                'body' => "「{$bookTitle}」は読了期限（{$targetDate}）を過ぎています。",
            ],
        };

        return [
            'reading_plan_id' => $this->readingPlan->id,
            'timing' => $this->timing,
            ...$message,
        ];
    }
}
