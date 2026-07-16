<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\RedirectResponse;

class ReviewLikeController extends Controller
{
    public function toggle(Review $review): RedirectResponse
    {
        $userId = auth()->id();

        if ($review->user_id === $userId) {
            return back();
        }

        $review->likedByUsers()->toggle($userId);

        return back();
    }
}
