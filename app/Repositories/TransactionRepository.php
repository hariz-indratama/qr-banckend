<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Transaction;

final readonly class TransactionRepository
{
    public function createPending(int $userId, int $merchantId, float $amount, string $paymentMethod, string $orderId): Transaction
    {
        return Transaction::create([
            'user_id' => $userId,
            'merchant_id' => $merchantId,
            'amount' => $amount,
            'admin_fee' => 0.0,
            'payment_method' => $paymentMethod,
            'order_id' => $orderId,
            'status' => 'PENDING',
        ]);
    }

    public function findByOrderId(string $orderId): ?Transaction
    {
        return Transaction::where('order_id', $orderId)->first();
    }
}
