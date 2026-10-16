<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@example.com');

        if (! User::where('email', $email)->exists()) {
            $password = env('ADMIN_PASSWORD') ?: Str::password(16, symbols: false);

            User::create([
                'name' => env('ADMIN_NAME', 'Aubri Porter'),
                'email' => $email,
                'password' => $password,
            ]);

            if (! env('ADMIN_PASSWORD')) {
                $this->command?->warn("Admin user {$email} created with password: {$password}");
            }
        }

        $this->call(StarterContentSeeder::class);
    }
}
