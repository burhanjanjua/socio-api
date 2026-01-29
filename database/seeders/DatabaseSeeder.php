<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => env('ADMIN_EMAIL', 'admin@socio-api.test'),
            'password' => Hash::make(env('ADMIN_PASSWORD', 'password')),
            'role' => 'admin',
        ]);

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => env('USER_EMAIL', 'user@socio-api.test'),
            'password' => Hash::make(env('USER_PASSWORD', 'password')),
            'role' => 'user',
        ]);

        Post::factory(5)->for($admin, 'author')->create([
            'published_at' => now()->subDays(2),
        ]);

        Post::factory(3)->for($user, 'author')->create([
            'published_at' => now()->subDay(),
        ]);
    }
}
