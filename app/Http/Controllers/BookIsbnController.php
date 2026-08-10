<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class BookIsbnController extends Controller
{
    /**
     * ISBN から Google Books API で書籍情報を取得し、書籍登録フォーム用にJSONで返す。
     */
    public function show(string $isbn): JsonResponse
    {
        if (! preg_match('/^\d{13}$/', $isbn)) {
            return response()->json([
                'error' => 'ISBNは13桁の数字で入力してください。',
            ], 422);
        }

        $response = Http::get(config('services.google_books.base_uri'), [
            'q' => "isbn:{$isbn}",
            'key' => config('services.google_books.key'),
        ]);

        if ($response->failed()) {
            return response()->json([
                'error' => '書籍情報の取得に失敗しました。しばらくしてから再度お試しください。',
            ], 502);
        }

        $items = $response->json('items', []);

        if (empty($items)) {
            return response()->json([
                'error' => '該当する書籍が見つかりませんでした。',
            ], 404);
        }

        $volumeInfo = $items[0]['volumeInfo'] ?? [];

        return response()->json([
            'title' => $volumeInfo['title'] ?? '',
            'author' => isset($volumeInfo['authors']) ? implode(', ', $volumeInfo['authors']) : '',
            'description' => $volumeInfo['description'] ?? '',
            'image_url' => $volumeInfo['imageLinks']['thumbnail'] ?? '',
            'published_date' => $volumeInfo['publishedDate'] ?? '',
        ]);
    }
}
