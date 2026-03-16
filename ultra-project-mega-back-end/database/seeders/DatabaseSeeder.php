<?php

namespace Database\Seeders;

use App\Models\Resource;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Администратор',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Пользователь',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        Resource::create([
            'name' => 'Переговорка А',
            'description' => 'Большая переговорка с проектором',
            'location' => 'Офис А, этаж 3',
            'capacity' => 10,
            'features' => ['projector', 'whiteboard', 'video_conf'],
            'is_active' => true,
        ]);

        Resource::create([
            'name' => 'Переговорка Б',
            'description' => 'Маленькая переговорка',
            'location' => 'Офис А, этаж 2',
            'capacity' => 4,
            'features' => ['whiteboard'],
            'is_active' => true,
        ]);
    }
}