<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BookPolicy
{


    /**
     * 書籍を更新できるか
     */
    public function update(User $user, Book $book): bool
    {
        return $user->id === $book->user_id;   // 作成者本人か？
    }

    /**
     * 書籍を削除できるか
     */
    public function delete(User $user, Book $book): bool
    {
        return $user->id === $book->user_id;   // 作成者本人か？
    }


}
