<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Models\PushSubscription;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class WebPushService
{
    private function webPush(): WebPush
    {
        $config = config('webpush.vapid');
        if (empty($config['public_key']) || empty($config['private_key'])) {
            throw new \RuntimeException('VAPID keys not configured. Run php artisan webpush:vapid and set VAPID_PUBLIC_KEY, VAPID_PRIVATE_KEY in .env');
        }

        return new WebPush([
            'VAPID' => [
                'subject' => $config['subject'],
                'publicKey' => $config['public_key'],
                'privateKey' => $config['private_key'],
            ],
        ]);
    }

    private function buildPayload(string $title, string $body, ?string $url = null): string
    {
        return json_encode([
            'title' => $title,
            'body' => $body,
            'icon' => asset('favicon.svg'),
            'url' => $url ?? url('/admin/orders'),
            'sound' => asset('sounds/notification.mp3'),
        ], JSON_THROW_ON_ERROR);
    }

    public function sendToAdmins(string $title, string $body, ?string $url = null): void
    {
        $payload = $this->buildPayload($title, $body, $url);

        $subscriptions = PushSubscription::whereHas('user', fn ($q) => $q->where('is_admin', true))
            ->get();

        if ($subscriptions->isEmpty()) {
            return;
        }

        $webPush = $this->webPush();

        foreach ($subscriptions as $model) {
            try {
                $subscription = Subscription::create($model->toWebPushSubscription());
                $report = $webPush->sendOneNotification($subscription, $payload);
                if ($report->isSubscriptionExpired()) {
                    $model->delete();
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }

    public function notifyNewOrder(Order $order): void
    {
        $this->sendToAdmins(
            'Новый заказ',
            'Заказ ' . $order->order_number . ' на ' . number_format((float) $order->total, 0, ',', ' ') . ' ₽',
            url('/admin/orders/' . $order->id)
        );
    }

    public function sendTestToUser(int $userId): bool
    {
        $subscriptions = PushSubscription::where('user_id', $userId)->get();
        if ($subscriptions->isEmpty()) {
            return false;
        }
        $payload = $this->buildPayload('Тест уведомлений', 'Если вы видите это — пуш работает.');
        $webPush = $this->webPush();
        $sent = false;
        foreach ($subscriptions as $model) {
            try {
                $sub = Subscription::create($model->toWebPushSubscription());
                $report = $webPush->sendOneNotification($sub, $payload);
                if ($report->isSubscriptionExpired()) {
                    $model->delete();
                } else {
                    $sent = true;
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }
        return $sent;
    }
}
