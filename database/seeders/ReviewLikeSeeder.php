<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewLikeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $reviews = Review::all();

        foreach ($reviews as $review) {
            // 自分以外のユーザーからランダムで0〜3人を選ぶ
            $otherUsers = $users->where('id', '!=', $review->user_id);
            $likers = $otherUsers->random(min(rand(0, 3), $otherUsers->count()));
            $review->likedByUsers()->syncWithoutDetaching($likers->pluck('id')->toArray());
        }
    }
}
