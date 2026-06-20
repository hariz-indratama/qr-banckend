<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\TransactionRepository;
use App\Models\Merchant;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

final readonly class MidtransPaymentService
{
    public function __construct(
        private TransactionRepository $repository,
    ) {}

    public function createPayment(int $userId, string $merchantCode, float $amount, string $paymentMethod): array
    {
        $merchant = Merchant::where('qr_identifier_code', $merchantCode)->first();
        if (!$merchant) {
            throw ValidationException::withMessages(['merchant_id' => 'Merchant tidak valid.']);
        }

        $orderId = 'TRX-' . date('Ymd') . '-' . strtoupper(Str::random(5));
        
        // Simpan transaksi di database lokal
        DB::transaction(function () use ($userId, $merchant, $amount, $paymentMethod, $orderId) {
            $this->repository->createPending($userId, $merchant->id, $amount, $paymentMethod, $orderId);
        });

        $serverKey = config('services.midtrans.server_key');
        $isProduction = config('services.midtrans.is_production');
        
        $baseUrl = $isProduction 
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
            
        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $amount,
            ],
            'item_details' => [
                [
                    'id' => $merchantCode,
                    'price' => (int) $amount,
                    'quantity' => 1,
                    'name' => 'Pembayaran QR untuk ' . $merchant->name,
                ]
            ],
            // Asumsi default user detail, idealnya diambil dari relasi User
            'customer_details' => [
                'first_name' => 'Customer',
                'email' => 'user@example.com',
                'phone' => '08123456789',
            ],
        ];

        try {
            $response = Http::withBasicAuth($serverKey, '')
                ->post($baseUrl, $payload);
            
            if ($response->successful() && isset($response['redirect_url'])) {
                return [
                    'order_id' => $orderId,
                    'payment_url' => $response['redirect_url'],
                    'return_url' => url('/api/v1/payment/callback/return'), // opsional
                    'expiry_seconds' => 900 // Default midtrans bisa disesuaikan
                ];
            }
            
            // Log kesalahan dari respons Midtrans
            Log::error('Midtrans API Error', ['res' => $response->json(), 'status' => $response->status()]);
            throw ValidationException::withMessages(['payment' => 'Gagal menginisiasi pembayaran ke Payment Gateway Midtrans.']);
            
        } catch (\Exception $e) {
            Log::error('Midtrans API Exception', ['msg' => $e->getMessage()]);
            throw ValidationException::withMessages(['payment' => 'Sistem pembayaran (Midtrans) sedang tidak tersedia.']);
        }
    }
}
