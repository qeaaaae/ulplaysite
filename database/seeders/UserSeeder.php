<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@ulplay.com'],
            [
                'name' => 'Администратор',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'email_verified_at' => Carbon::now(),
            ]
        );

        for ($i = 1; $i <= 25; $i++) {
            $user = User::firstOrCreate(
                ['email' => "user{$i}@example.com"],
                [
                    'name' => "Пользователь {$i}",
                    'password' => Hash::make('password'),
                    'phone' => $i <= 25 ? "+79001234" . str_pad((string) $i, 2, '0', STR_PAD_LEFT) : null,
                    'is_admin' => false,
                    'is_blocked' => $i === 3 ? true : false,
                ]
            );
            if ($user->wasRecentlyCreated && ! $user->is_admin) {
                $daysAgo = random_int(0, 90);
                $createdAt = Carbon::now()->subDays($daysAgo)->subHours(random_int(0, 23));
                $user->created_at = $createdAt;
                $user->updated_at = $createdAt;
                $user->saveQuietly();
            }
        }
    }
}
