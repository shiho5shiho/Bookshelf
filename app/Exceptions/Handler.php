<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    /** API（JSON）用: ステータス→日本語 */
    private const API_MESSAGES = [
        401 => '認証が必要です。',
        403 => 'この操作を行う権限がありません。',
        404 => '書籍が見つかりませんでした。',
    ];

    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e)
    {
        $wantsJson = $request->expectsJson() || $request->is('api/*');

        // --- API（JSON） ---
        if ($wantsJson) {
            // バリデーション(422)はLaravel標準に任せる（message + errors 構造）
            if ($e instanceof ValidationException) {
                return parent::render($request, $e);
            }

            $status = $this->resolveStatus($e);

            if (isset(self::API_MESSAGES[$status])) {
                return response()->json(['error' => self::API_MESSAGES[$status]], $status);
            }
        }

        // --- Web（HTMLページ） ---
        // Laravel標準に任せる（resources/views/errors/{コード}.blade.php を自動解決）
        return parent::render($request, $e);
    }

    private function resolveStatus(Throwable $e): int
    {
        return match (true) {
            $e instanceof AuthenticationException => 401,
            $e instanceof AuthorizationException => 403,
            $e instanceof ModelNotFoundException => 404,
            $e instanceof HttpExceptionInterface => $e->getStatusCode(),
            default => 500,
        };
    }
}
