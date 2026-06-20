<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Merchant;
use App\Enums\MerchantStatusEnum;
use App\Enums\QrTypeEnum;
use Illuminate\Validation\ValidationException;

final readonly class QrValidationService
{
    public function validate(string $qrPayload): array
    {
        // Mocking ekstraksi payload QRIS
        // Format standar biasanya akan dipecah berdasarkan struktur EMVCo
        
        // Asumsi ekstrak merchant ID dari payload
        $merchantId = 'M-DUITKU-0912'; 
        
        $merchant = Merchant::where('qr_identifier_code', $merchantId)->first();
        
        if (!$merchant || $merchant->status !== MerchantStatusEnum::ACTIVE->value) {
            throw ValidationException::withMessages([
                'qr_payload' => ['Merchant tidak terdaftar atau tidak aktif.']
            ]);
        }

        return [
            'qr_type' => QrTypeEnum::DYNAMIC->value,
            'merchant_id' => $merchant->qr_identifier_code,
            'merchant_name' => $merchant->name,
            'is_verified' => true,
            'amount' => 10000.00,
            'items' => [
                [
                    'item_name' => 'Pembayaran Fixed (Promo)',
                    'quantity' => 1,
                    'price' => 10000.00
                ]
            ],
            'expires_at' => now()->addMinutes(60)->toIso8601ZuluString(),
        ];
    }
}
