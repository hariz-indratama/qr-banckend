<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Merchant;
use App\Enums\MerchantStatusEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Data User (Dengan ID 1 agar lolos mock di Controller)
        User::updateOrCreate(
            ['email' => 'user@qrpay.com'],
            [
                'name' => 'Hariz (Demo User)',
                'password' => Hash::make('password123'),
                'balance' => 1000000.00, // Saldo awal 1 Juta
                'pin_hash' => Hash::make('123456'), // PIN pembayaran: 123456
            ]
        );

        // 2. Buat Data Merchant (Kode harus persis M-DUITKU-0912 karena ini kode mock QR Scanner kita)
        Merchant::updateOrCreate(
            ['qr_identifier_code' => 'M-DUITKU-0912'],
            [
                'name' => 'Duitku Coffee Shop',
                'status' => MerchantStatusEnum::ACTIVE->value,
            ]
        );
    }
}
