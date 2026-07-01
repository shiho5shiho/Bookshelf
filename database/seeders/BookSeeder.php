<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Book;
use App\Models\Genre;
use App\Models\User;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();

        $books = [
            [
                'title' => '吾輩は猫である',
                'author' => '夏目漱石',
                'isbn' => '9784101010014',
                'published_date' => '1905-01-01',
                'description' => '猫の視点から人間社会を風刺的に描いた、夏目漱石の代表作。教師の家に飼われる名もなき猫が、周囲の人間模様を皮肉たっぷりに観察する。',
                'genres' => ['小説'],
            ],
            [
                'title' => '人を動かす',
                'author' => 'D・カーネギー',
                'isbn' => '9784422100524',
                'published_date' => '1936-10-01',
                'description' => '人間関係の原則を説いた自己啓発書の金字塔。相手の立場に立つこと、誠実な関心を持つことの大切さを、豊富な実例とともに解説する。',
                'genres' => ['ビジネス', '自己啓発'],
            ],
            [
                'title' => 'リーダブルコード',
                'author' => 'Dustin Boswell',
                'isbn' => '9784873115658',
                'published_date' => '2012-06-23',
                'description' => '読みやすく保守しやすいコードを書くための実践的な技法を解説する、エンジニア必読の一冊。命名規則からコメントの書き方まで網羅。',
                'genres' => ['技術書'],
            ],
            [
                'title' => '7つの習慣',
                'author' => 'スティーブン・R・コヴィー',
                'isbn' => '9784863940246',
                'published_date' => '2013-08-30',
                'description' => '人格主義に基づいた、効果的な人生を送るための7つの習慣を提唱する世界的ベストセラー。主体性、目的、優先順位の重要性を説く。',
                'genres' => ['ビジネス', '自己啓発'],
            ],
            [
                'title' => '坊っちゃん',
                'author' => '夏目漱石',
                'isbn' => '9784101010021',
                'published_date' => '1906-04-01',
                'description' => '江戸っ子気質の青年教師が、四国の中学校で繰り広げる痛快な物語。正義感あふれる主人公の奮闘を、軽快な文体で描く。',
                'genres' => ['小説'],
            ],
            [
                'title' => 'サピエンス全史',
                'author' => 'ユヴァル・ノア・ハラリ',
                'isbn' => '9784309226712',
                'published_date' => '2016-09-08',
                'description' => '認知革命から農業革命、科学革命まで、人類の歴史を壮大なスケールで描く。ホモ・サピエンスがいかにして世界を支配したかを問う。',
                'genres' => ['歴史', '科学'],
            ],
            [
                'title' => 'Clean Code',
                'author' => 'Robert C. Martin',
                'isbn' => '9784048930598',
                'published_date' => '2017-12-18',
                'description' => '優れたソフトウェア開発者になるための原則とプラクティスをまとめた一冊。クリーンなコードを書くための具体的な技法を紹介する。',
                'genres' => ['技術書'],
            ],
            [
                'title' => '嫌われる勇気',
                'author' => '岸見一郎・古賀史健',
                'isbn' => '9784478025819',
                'published_date' => '2013-12-13',
                'description' => 'アドラー心理学を対話形式で分かりやすく解説したベストセラー。他者の評価を気にせず生きる、その勇気の持ち方を説く。',
                'genres' => ['自己啓発'],
            ],
            [
                'title' => '火花',
                'author' => '又吉直樹',
                'isbn' => '9784163902302',
                'published_date' => '2015-03-11',
                'description' => '芸人としての生き様を描いた芥川賞受賞作。先輩芸人との出会いを通じて、お笑いと人生の本質を問う青春小説。',
                'genres' => ['小説'],
            ],
            [
                'title' => 'FACTFULNESS',
                'author' => 'ハンス・ロスリング',
                'isbn' => '9784822289607',
                'published_date' => '2019-01-11',
                'description' => '思い込みを排除し、データに基づいて世界を正しく見る方法を説く。世界はあなたが思うより良くなっている、というメッセージを伝える。',
                'genres' => ['ビジネス', '科学'],
            ],
            [
                'title' => 'コンテナ物語',
                'author' => 'マルク・レビンソン',
                'isbn' => '9784822251468',
                'published_date' => '2007-01-18',
                'description' => 'コンテナという箱がいかにして世界経済を変えたかを描くノンフィクション。物流革命の知られざる歴史を紐解く。',
                'genres' => ['ビジネス', '歴史'],
            ],
        ];

        foreach ($books as $index => $bookData) {
            $number = $index + 1;

            $book = Book::firstOrCreate(
                ['isbn' => $bookData['isbn']],
                [
                    'user_id' => $user->id,
                    'title' => $bookData['title'],
                    'author' => $bookData['author'],
                    'published_date' => $bookData['published_date'],
                    'description' => $bookData['description'],
                    'image_url' => "https://placehold.co/200x300/e2e8f0/475569?text={$number}",
                ]
            );

            $genreIds = Genre::whereIn('name', $bookData['genres'])->pluck('id');
            $book->genres()->sync($genreIds);
        }
    }
}
