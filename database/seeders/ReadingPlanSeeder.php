<?php

namespace Database\Seeders;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReadingPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userA = User::where('email', 'yamada@example.com')->first();
        $userB = User::where('email', 'suzuki@example.com')->first();
        $books = Book::whereIn('isbn', [
            '9784101010014', // 吾輩は猫である
            '9784422100524', // 人を動かす
            '9784873115658', // リーダブルコード
            '9784863940246', // 7つの習慣
            '9784101010021', // 坊っちゃん
            '9784309226712', // サピエンス全史
            '9784048930598', // Clean Code
            '9784478025819', // 嫌われる勇気
        ])->get()->keyBy('isbn');
        $today = today();
        $plans = [
            // 1. リマインダー「3日前」が発火するパターン
            [
                'user_id' => $userA->id,
                'book_id' => $books['9784101010014']->id,
                'target_date' => $today->copy()->addDays(3),
                'status' => ReadingPlanStatus::InProgress,
            ],
            // 2. リマインダー「当日」が発火するパターン
            [
                'user_id' => $userA->id,
                'book_id' => $books['9784422100524']->id,
                'target_date' => $today->copy(),
                'status' => ReadingPlanStatus::InProgress,
            ],
            // 3. リマインダー「失効から3日後（再エンゲージメント）」が発火するパターン
            [
                'user_id' => $userA->id,
                'book_id' => $books['9784873115658']->id,
                'target_date' => $today->copy()->subDays(4),
                'status' => ReadingPlanStatus::Expired,
            ],
            // 4. 失効バッチ実行でExpiredに変わることを確認するパターン（期日は昨日＝まだ未処理）
            [
                'user_id' => $userA->id,
                'book_id' => $books['9784863940246']->id,
                'target_date' => $today->copy()->subDays(1),
                'status' => ReadingPlanStatus::InProgress,
            ],
            // 5. まだ何も発火しない、通常の進行中パターン
            [
                'user_id' => $userA->id,
                'book_id' => $books['9784101010021']->id,
                'target_date' => $today->copy()->addDays(10),
                'status' => ReadingPlanStatus::InProgress,
            ],
            // 6. 日付は一致するが、ステータスがInProgressでないため発火しないパターン
            [
                'user_id' => $userA->id,
                'book_id' => $books['9784309226712']->id,
                'target_date' => $today->copy(),
                'status' => ReadingPlanStatus::Completed,
                'completed_at' => $today->copy()->subDays(1),
            ],
            // 7. 最初から失効済み、画面表示（バッジ等）確認用
            [
                'user_id' => $userA->id,
                'book_id' => $books['9784048930598']->id,
                'target_date' => $today->copy()->subDays(10),
                'status' => ReadingPlanStatus::Expired,
            ],
            // 8. 認可確認用：山田太郎以外（鈴木花子）のデータ
            //    リマインダー・失効どちらの条件にも一致しない日付にする
            [
                'user_id' => $userB->id,
                'book_id' => $books['9784478025819']->id,
                'target_date' => $today->copy()->addDays(20),
                'status' => ReadingPlanStatus::InProgress,
            ],
        ];

        foreach ($plans as $plan) {
            ReadingPlan::firstOrCreate(
                [
                    'user_id' => $plan['user_id'],
                    'book_id' => $plan['book_id'],
                ],
                $plan
            );
        }
    }
}
