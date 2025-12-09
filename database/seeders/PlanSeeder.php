<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        Plan::create([
            'name' => 'Premium 1 Bulan',
            'description' => 'Akses premium selama 1 bulan',
            'price' => 50000,
            'duration_days' => 30,
        ]);

        Plan::create([
            'name' => 'Premium 3 Bulan',
            'description' => 'Akses premium selama 3 bulan',
            'price' => 120000,
            'duration_days' => 90,
        ]);

        Plan::create([
            'name' => 'Premium 1 Tahun',
            'description' => 'Akses premium selama 1 tahun',
            'price' => 400000,
            'duration_days' => 365,
        ]);
    }
}
