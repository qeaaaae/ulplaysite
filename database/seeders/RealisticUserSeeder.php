<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RealisticUserSeeder extends Seeder
{
    private const DOMAINS = ['gmail.com', 'mail.ru', 'yandex.ru', 'inbox.ru', 'bk.ru', 'ya.ru', 'list.ru'];

    private function makeEmailLocalPart(\Faker\Generator $faker, string $firstName, string $lastName): string
    {
        $f = strtolower(preg_replace('/[^a-z]/', '', Str::ascii($firstName)) ?: 'u');
        $l = strtolower(preg_replace('/[^a-z]/', '', Str::ascii($lastName)) ?: 'user');
        $init = mb_substr($f, 0, 1);
        $year = $faker->numberBetween(85, 2005);
        $digits = $faker->numberBetween(1, 999);
        $digits2 = $faker->numberBetween(11, 99);

        return match ($faker->numberBetween(1, 12)) {
            1 => "{$f}.{$l}",                           // ivan.petrov
            2 => "{$f}_{$l}",                           // ivan_petrov
            3 => "{$l}.{$f}",                           // petrov.ivan
            4 => "{$f}.{$l}{$digits2}",                 // ivan.petrov92
            5 => "{$f}{$year}",                         // ivan1992
            6 => "{$init}{$l}",                         // ipetrov
            7 => "{$init}.{$l}",                        // i.petrov
            8 => "{$l}_{$f}_{$year}",                   // petrov_ivan_92
            9 => "{$f}.{$l}.{$digits}",                 // ivan.petrov.777
            10 => "{$f}_{$digits}",                     // ivan_123
            11 => "{$l}{$digits2}",                     // petrov99
            default => "{$f}{$l}{$digits}",             // ivanpetrov42
        };
    }

    public function run(): void
    {
        $faker = Factory::create('ru_RU');
        $usedEmails = [];

        for ($i = 0; $i < 100; $i++) {
            $firstName = $faker->firstName;
            $lastName = $faker->lastName;
            $name = "{$firstName} {$lastName}";

            $local = $this->makeEmailLocalPart($faker, $firstName, $lastName);
            $local = preg_replace('/[^a-z0-9._-]/', '', $local) ?: 'user' . $i;
            if (isset($usedEmails[$local])) {
                $usedEmails[$local]++;
                $local .= '.' . $usedEmails[$local];
            } else {
                $usedEmails[$local] = 0;
            }
            $email = $local . '@' . $faker->randomElement(self::DOMAINS);

            $phone = '+7' . $faker->numberBetween(900, 999) . $faker->numerify('#######');

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('password'),
                    'phone' => $phone,
                    'email_verified_at' => Carbon::now(),
                    'is_admin' => false,
                    'is_blocked' => $i === 2,
                    'is_bot' => true,
                ]
            );

            if ($user->wasRecentlyCreated) {
                $daysAgo = $faker->numberBetween(0, 90);
                $createdAt = Carbon::now()->subDays($daysAgo)->subHours($faker->numberBetween(0, 23));
                $user->created_at = $createdAt;
                $user->updated_at = $createdAt;
                $user->saveQuietly();
            }
        }
    }
}
