<?php

namespace Database\Seeders;

use App\Models\Todo;
use Illuminate\Database\Seeder;

class TodoSeeder extends Seeder
{
    public function run(): void
    {
        $user = \App\Models\User::create([
            'name' => 'Admin',
            'email' => 'admin@tododin.com',
            'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
            'is_premium' => true,
        ]);

        $user->todos()->createMany([
            [
                'title' => 'Belajar Laravel',
                'description' => 'Membuat REST API untuk tododin',
                'completed' => false,
            ],
            [
                'title' => 'Belajar React',
                'description' => 'Integrasi frontend dengan backend',
                'completed' => false,
            ],
            [
                'title' => 'Deploy Aplikasi',
                'description' => 'Deploy ke production server',
                'completed' => false,
            ],
        ]);
    }
}
