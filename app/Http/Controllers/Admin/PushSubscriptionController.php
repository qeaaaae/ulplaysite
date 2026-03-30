<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PushSubscription;
use App\Services\WebPushService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'endpoint' => ['required', 'string', 'url'],
            'keys' => ['required', 'array'],
            'keys.p256dh' => ['required', 'string'],
            'keys.auth' => ['required', 'string'],
        ]);

        $user = $request->user();
        if (! $user->is_admin) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $subscription = PushSubscription::updateOrCreate(
            [
                'user_id' => $user->id,
                'endpoint' => $request->endpoint,
            ],
            [
                'public_key' => $request->keys['p256dh'],
                'auth_token' => $request->keys['auth'],
                'content_encoding' => $request->input('contentEncoding', 'aes128gcm'),
            ]
        );

        // До 5 устройств на одного админа: оставляем самые свежие подписки.
        $keepIds = PushSubscription::query()
            ->where('user_id', $user->id)
            ->orderByDesc('updated_at')
            ->limit(5)
            ->pluck('id');

        PushSubscription::query()
            ->where('user_id', $user->id)
            ->whereNotIn('id', $keepIds)
            ->delete();

        return response()->json(['success' => true]);
    }

    public function test(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user->is_admin) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        $sent = app(WebPushService::class)->sendTestToUser((int) $user->id);
        return response()->json(['success' => $sent, 'message' => $sent ? 'Уведомление отправлено' : 'Нет подписки. Разрешите уведомления и обновите страницу.']);
    }
}
