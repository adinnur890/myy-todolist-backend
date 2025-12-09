<?php

namespace Database\Seeders;

use App\Models\Voucher;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class VoucherSeeder extends Seeder
{
    public function run(): void
    {
        Voucher::create([
            'code' => 'DISKON50',
            'discount_type' => 'percentage',
            'discount_value' => 50,
            'max_uses' => 100,
            'used_count' => 0,
            'expires_at' => Carbon::now()->addMonths(3),
            'is_active' => true,
        ]);

        Voucher::create([
            'code' => 'HEMAT20K',
            'discount_type' => 'fixed',
            'discount_value' => 20000,
            'max_uses' => 50,
            'used_count' => 0,
            'expires_at' => Carbon::now()->addMonths(1),
            'is_active' => true,
        ]);

        Voucher::create([
            'code' => 'GRATIS100',
            'discount_type' => 'percentage',
            'discount_value' => 100,
            'max_uses' => 10,
            'used_count' => 0,
            'expires_at' => Carbon::now()->addDays(7),
            'is_active' => true,
        ]);
    }
}
