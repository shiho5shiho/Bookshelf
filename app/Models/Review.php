<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'user_id',
        'rating',
        'comment',
    ];

    // レビューが紐づく書籍
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    // レビューの投稿者
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // このレビューにいいねしたユーザー（中間テーブル: review_likes）
    public function likedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'review_likes')->withTimestamps();
    }
}
