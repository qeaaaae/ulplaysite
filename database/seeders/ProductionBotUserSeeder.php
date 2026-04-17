<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProductionBotUserSeeder extends Seeder
{
    public function run(): void
    {
        $bots = [
            [
                'email' => 'bot-news@ulplay.com',
                'name' => 'ULPlay News Bot',
            ],
            [
                'email' => 'bot-support@ulplay.com',
                'name' => 'ULPlay Support Bot',
            ],
            [
                'email' => 'bot-content@ulplay.com',
                'name' => 'ULPlay Content Bot',
            ],
        ];

        foreach ($bots as $bot) {
            User::updateOrCreate(
                ['email' => $bot['email']],
                [
                    'name' => $bot['name'],
                    'password' => Hash::make('password'),
                    'is_admin' => false,
                    'is_blocked' => false,
                    'is_bot' => true,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
