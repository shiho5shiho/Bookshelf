<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Book;
use App\Models\User;

class FavoriteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $books = Book::all();

        $favorites = [
            'yamada@example.com'    => [1, 2, 3, 4, 5],
            'suzuki@example.com'    => [2, 4, 6, 8],
            'tanaka@example.com'    => [1, 3, 5, 7, 9],
            'sato@example.com'      => [3, 6, 9, 11],
            'takahashi@example.com' => [2, 5, 8, 10, 11],
        ];

        foreach ($favorites as $email => $bookNumbers) {
            $user = $users->firstWhere('email', $email);
            // books は通常のIndex(0から始まる)で取得されるため、1から始まる番号を0から始まるIndexに変換するため$n-1を使用
            $bookIds = collect($bookNumbers)->map(fn($n) => $books[$n - 1]->id);
            $user->favoriteBooks()->syncWithoutDetaching($bookIds->toArray());
        }
    }
}
