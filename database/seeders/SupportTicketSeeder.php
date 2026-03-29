<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\SupportTicketTypeEnum;
use App\Models\Service;
use App\Models\SupportTicket;
use App\Models\UserNotification;
use App\Models\User;
use Illuminate\Database\Seeder;

class SupportTicketSeeder extends Seeder
{
    private const STATUSES = ['new', 'in_progress', 'resolved', 'closed'];

    private const IMAGES = [
        'https://avatars.mds.yandex.net/get-mpic/5347553/2a00000192cd09d4b4cbb9bb28497c637e4a/optimize',
        'https://avatars.mds.yandex.net/get-mpic/5347553/2a00000192cd09d4b4cbb9bb28497c637e4a/optimize',
        'https://avatars.mds.yandex.net/get-mpic/5347553/2a00000192cd09d4b4cbb9bb28497c637e4a/optimize',
        'https://avatars.mds.yandex.net/get-mpic/5347553/2a00000192cd09d4b4cbb9bb28497c637e4a/optimize',
        'https://avatars.mds.yandex.net/get-mpic/5347553/2a00000192cd09d4b4cbb9bb28497c637e4a/optimize',
    ];

    public function run(): void
    {
        $users = User::query()->where('is_admin', false)->limit(20)->get();
        $admins = User::query()->where('is_admin', true)->limit(5)->get();

        for ($i = 1; $i <= 40; $i++) {
            $type = fake()->randomElement(SupportTicketTypeEnum::cases());
            $status = fake()->randomElement(self::STATUSES);
            $user = $users->isNotEmpty() ? $users->random() : null;

            $serviceId = null;
            if ($type === SupportTicketTypeEnum::SERVICE_INQUIRY) {
                $serviceId = Service::query()->inRandomOrder()->value('id');
            }

            $ticket = SupportTicket::create([
                'user_id' => $user?->id,
                'service_id' => $serviceId,
                'type' => $type->value,
                'title' => $this->titleFor($type, $i),
                'description' => fake()->paragraphs(asText: true),
                'status' => $status,
                'ip_address' => fake()->ipv4(),
                'user_agent' => substr((string) fake()->userAgent(), 0, 1000),
            ]);

            // Author initial message.
            $ticket->messages()->create([
                'sender_role' => 'user',
                'sender_user_id' => $user?->id,
                'content' => $ticket->description,
            ]);

            // Some admin replies for UI testing.
            if ($admins->isNotEmpty()) {
                $repliesCount = random_int(0, 2);
                for ($r = 0; $r < $repliesCount; $r++) {
                    $message = $ticket->messages()->create([
                        'sender_role' => 'admin',
                        'sender_user_id' => $admins->random()->id,
                        'content' => fake()->paragraphs(asText: true),
                    ]);

                    if ($ticket->user_id !== null) {
                        UserNotification::query()->create([
                            'user_id' => $ticket->user_id,
                            'type' => 'ticket_reply',
                            'title' => 'Ответ по вашему обращению',
                            'body' => $message->content,
                            'support_ticket_id' => $ticket->id,
                            'url' => '/my-tickets/' . $ticket->id,
                            'read_at' => null,
                        ]);
                    }
                }
            }

            $imagesCount = random_int(0, 3);
            for ($pos = 0; $pos < $imagesCount; $pos++) {
                $ticket->images()->create([
                    'path' => self::IMAGES[($i + $pos) % count(self::IMAGES)],
                    'is_cover' => $pos === 0,
                    'position' => $pos,
                ]);
            }
        }
    }

    private function titleFor(SupportTicketTypeEnum $type, int $index): string
    {
        return match ($type) {
            SupportTicketTypeEnum::TECHNICAL_ISSUE => "Ошибка на сайте #{$index}",
            SupportTicketTypeEnum::ORDER_ISSUE => "Проблема с заказом #{$index}",
            SupportTicketTypeEnum::DELIVERY => "Вопрос по доставке #{$index}",
            SupportTicketTypeEnum::SERVICE_REPAIR => "Заявка по ремонту #{$index}",
            SupportTicketTypeEnum::RETURN_EXCHANGE => "Возврат/обмен #{$index}",
            SupportTicketTypeEnum::SUGGESTION => "Предложение по улучшению #{$index}",
            SupportTicketTypeEnum::SERVICE_INQUIRY => "Вопрос по услуге #{$index}",
        };
    }
}


