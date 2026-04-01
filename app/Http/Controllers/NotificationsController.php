<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class NotificationsController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        abort_unless($user, 403);

        UserNotification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $notifications = UserNotification::query()
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(15);

        return view('notifications.index', [
            'metaTitle' => 'Уведомления',
            'notifications' => $notifications,
        ]);
    }
}

