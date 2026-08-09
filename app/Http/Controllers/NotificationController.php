<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * ログインユーザーの通知一覧を表示する。
     */
    public function index(Request $request): View
    {
        $notifications = $request->user()->notifications;

        return view('notifications.index', compact('notifications'));
    }

    /**
     * 通知を既読にする。
     */
    public function read(Request $request, string $id): RedirectResponse
    {
        $notification = $request->user()->notifications()->findOrFail($id);

        $notification->markAsRead();

        return redirect()
            ->route('notifications.index')
            ->with('success', '既読にしました。');
    }
}
