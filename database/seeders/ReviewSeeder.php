<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Review;
use App\Models\Book;
use App\Models\User;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $books = Book::all()->keyBy('isbn');

        // book_id を ISBN 経由で特定し、各書籍に紐づくレビュー（rating, comment）を個別に定義
        $reviewsData = [
            '9784101010014' => [ // 吾輩は猫である（4件）
                ['rating' => 5, 'comment' => '猫の視点から語られる人間観察がとにかく秀逸で、何度読んでも新しい発見があります。'],
                ['rating' => 4, 'comment' => '古い文体に最初は戸惑いましたが、慣れると皮肉の効いたユーモアが癖になります。'],
                ['rating' => 5, 'comment' => '日本文学の名作と言われる理由がよく分かりました。風刺の切れ味が見事です。'],
                ['rating' => 3, 'comment' => '面白いのですが、長編なので途中でやや中だるみを感じる部分もありました。'],
            ],
            '9784422100524' => [ // 人を動かす（3件）
                ['rating' => 5, 'comment' => '人間関係に悩んだ時に何度も読み返している一冊です。具体例が分かりやすいです。'],
                ['rating' => 4, 'comment' => '当たり前のことのようで、実践するのは難しい。実際にやってみて効果を感じました。'],
                ['rating' => 4, 'comment' => '古典的名著として有名なだけあり、内容に説得力があります。'],
            ],
            '9784873115658' => [ // リーダブルコード（4件）
                ['rating' => 5, 'comment' => 'エンジニアなら一度は読むべき本。命名のセンスが格段に良くなりました。'],
                ['rating' => 5, 'comment' => '具体的なコード例が豊富で、すぐに実務に活かせる内容ばかりでした。'],
                ['rating' => 4, 'comment' => '基本的な内容が中心ですが、改めて読むと気づきが多い良書です。'],
                ['rating' => 4, 'comment' => 'チームのコーディング規約を見直すきっかけになりました。'],
            ],
            '9784863940246' => [ // 7つの習慣（3件）
                ['rating' => 4, 'comment' => '内容は深いですが、その分しっかり読み込む必要があり、時間がかかりました。'],
                ['rating' => 5, 'comment' => '人生観が変わるとよく言われますが、まさにその通りだと感じました。'],
                ['rating' => 3, 'comment' => 'ボリュームが多く、最初は読み切るのに苦労しましたが内容は良かったです。'],
            ],
            '9784101010021' => [ // 坊っちゃん（2件）
                ['rating' => 5, 'comment' => 'テンポが良く、あっという間に読み終わりました。主人公の正義感が痛快です。'],
                ['rating' => 4, 'comment' => '江戸っ子気質の主人公が魅力的で、読んでいて爽快な気分になりました。'],
            ],
            '9784309226712' => [ // サピエンス全史（4件）
                ['rating' => 5, 'comment' => '人類史をこれほど壮大かつ分かりやすく描いた本は他にないと思います。'],
                ['rating' => 5, 'comment' => '視点が独特で、当たり前だと思っていたことが覆される感覚が面白かったです。'],
                ['rating' => 4, 'comment' => 'ボリュームはありますが、知的好奇心を刺激される内容で一気に読めました。'],
                ['rating' => 4, 'comment' => '歴史というより人類学に近い視点で、新鮮な学びがありました。'],
            ],
            '9784048930598' => [ // Clean Code（2件）
                ['rating' => 5, 'comment' => 'コードレビューで指摘される内容が、なぜ大事なのか腹落ちしました。'],
                ['rating' => 4, 'comment' => '実践的な内容が多く、すぐに業務に取り入れられる部分が多かったです。'],
            ],
            '9784478025819' => [ // 嫌われる勇気（3件）
                ['rating' => 5, 'comment' => '対話形式で読みやすく、アドラー心理学の入門書として最適だと思います。'],
                ['rating' => 5, 'comment' => '他人の評価を気にしすぎていた自分に気づかされる、心が軽くなる一冊でした。'],
                ['rating' => 4, 'comment' => '少し哲学的で難しい部分もありましたが、納得感のある内容でした。'],
            ],
            '9784163902302' => [ // 火花（2件）
                ['rating' => 4, 'comment' => '芸人の世界のリアルな描写が印象的で、青春小説として楽しめました。'],
                ['rating' => 3, 'comment' => '文学的な表現が多く、好みが分かれる作品だと感じました。'],
            ],
            '9784822289607' => [ // FACTFULNESS（3件）
                ['rating' => 5, 'comment' => '思い込みでいかに世界を誤解していたか、データで突きつけられて衝撃でした。'],
                ['rating' => 4, 'comment' => 'クイズ形式で読みやすく、最後まで飽きずに読み進められました。'],
                ['rating' => 5, 'comment' => 'ニュースの見方が変わる一冊です。多くの人に読んでほしいと思いました。'],
            ],
            '9784822251468' => [ // コンテナ物語（2件）
                ['rating' => 4, 'comment' => '物流という地味なテーマがこんなに面白く読めるとは思いませんでした。'],
                ['rating' => 4, 'comment' => '経済の歴史に興味がある人には特におすすめできる内容です。'],
            ],
        ];

        foreach ($reviewsData as $isbn => $reviews) {
            $book = $books->get($isbn);

            if (! $book) {
                continue;
            }

            $reviewers = $users->random(count($reviews));

            foreach ($reviews as $i => $reviewData) {
                Review::create([
                    'book_id' => $book->id,
                    'user_id' => $reviewers[$i]->id,
                    'rating' => $reviewData['rating'],
                    'comment' => $reviewData['comment'],
                ]);
            }
        }
    }
}
