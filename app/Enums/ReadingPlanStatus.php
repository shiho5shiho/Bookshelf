<?php

namespace App\Enums;

enum ReadingPlanStatus: string
{
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Expired = 'expired';

    /**
     * 画面表示用の日本語ラベルを返す。
     */
    public function label(): string
    {
        return match ($this) {
            self::InProgress => '未読',
            self::Completed => '読了',
            self::Expired => '失効',
        };
    }

    /**
     * 状態バッジに使うTailwindクラスを返す。
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::InProgress => 'bg-blue-100 text-blue-800',
            self::Completed => 'bg-green-100 text-green-800',
            self::Expired => 'bg-gray-100 text-gray-800',
        };
    }
}
