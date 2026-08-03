<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $books = Book::all()->keyBy('isbn');

        // 評価別コメントテンプレート（★1〜★5・汎用文言）
        $commentTemplates = [
            1 => [
                '期待していたほどではありませんでした。',
                '自分には合わない内容でした。',
                '途中で読むのが辛くなってしまいました。',
            ],
            2 => [
                '可もなく不可もなくという印象です。',
                'いまひとつ入り込めませんでした。',
                'もう少し期待していました。',
            ],
            3 => [
                '普通に楽しめる内容でした。',
                '特別な感動はありませんが、悪くない一冊です。',
                '読んで損はないと思います。',
            ],
            4 => [
                'とても勉強になる良い本でした。',
                '読んで良かったと思える一冊です。',
                '多くの人におすすめできます。',
            ],
            5 => [
                '期待以上の内容で大満足でした。',
                '非常に素晴らしく、多くの人に勧めたい一冊です。',
                '今まで読んだ中で最高の一冊です。',
            ],
        ];

        // 書籍ごとの評価配分（要素数=レビュー件数、各値=rating。全書籍2〜4件・★1〜5が全range登場）
        $ratingPlan = [
            '9784101010014' => [5, 5, 4, 4], // 吾輩は猫である：高評価
            '9784422100524' => [4, 3, 4],    // 人を動かす：中
            '9784873115658' => [5, 5, 5, 4], // リーダブルコード：技術書＝高
            '9784863940246' => [2, 1, 1, 2], // 7つの習慣：自己啓発＝低
            '9784101010021' => [5, 4],       // 坊っちゃん：高評価
            '9784309226712' => [5, 4, 3, 2], // サピエンス全史：割れる
            '9784048930598' => [5, 4],       // Clean Code：技術書＝高
            '9784478025819' => [2, 3, 1],    // 嫌われる勇気：自己啓発＝低
            '9784163902302' => [5, 3, 1],    // 火花：割れる
            '9784822289607' => [5, 4, 5],    // FACTFULNESS：高評価
            '9784822251468' => [3, 2, 1],    // コンテナ物語：低め
        ];

        foreach ($ratingPlan as $isbn => $ratings) {
            $book = $books->get($isbn);

            if (! $book) {
                continue;
            }

            // 同一書籍内で投稿者が重複しないよう、件数分のユーザーをランダム抽出
            $reviewers = $users->random(count($ratings));

            foreach ($ratings as $i => $rating) {
                Review::create([
                    'book_id' => $book->id,
                    'user_id' => $reviewers[$i]->id,
                    'rating' => $rating,
                    'comment' => $commentTemplates[$rating][array_rand($commentTemplates[$rating])],
                ]);
            }
        }
    }
}
