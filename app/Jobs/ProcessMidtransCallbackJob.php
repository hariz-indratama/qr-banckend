<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Enums\TransactionStatusEnum;

class ProcessMidtransCallbackJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public array $payload
    ) {}

    public function handle(): void
    {
        $orderId = $this->payload['order_id'] ?? null;
        $statusCode = $this->payload['status_code'] ?? null;
        $grossAmount = $this->payload['gross_amount'] ?? null;
        $incomingSignature = $this->payload['signature_key'] ?? null;
        $transactionStatus = $this->payload['transaction_status'] ?? null;
        
        if (!$orderId || !$statusCode || !$grossAmount || !$incomingSignature || !$transactionStatus) {
            Log::warning('Midtrans Webhook Error: Payload tidak lengkap', $this->payload);
            return;
        }

        $serverKey = config('services.midtrans.server_key');
        
        // Verifikasi Signature Keamanan
        // Aturan Midtrans: SHA512(order_id + status_code + gross_amount + server_key)
        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        
        if ($incomingSignature !== $expectedSignature) {
            Log::error("Midtrans Webhook Security Error: Invalid signature untuk Order {$orderId}. Potensi intrusi/pemalsuan callback.");
            return;
        }

        $amount = (float) $grossAmount;

        DB::transaction(function () use ($orderId, $transactionStatus, $amount) {
            $transaction = Transaction::where('order_id', $orderId)->lockForUpdate()->first();
            
            if (!$transaction || $transaction->status !== TransactionStatusEnum::PENDING->value) {
                return; 
            }

            if ($transactionStatus === 'settlement' || $transactionStatus === 'capture') {
                $transaction->status = TransactionStatusEnum::SUCCESS->value;
                
                $user = User::where('id', $transaction->user_id)->lockForUpdate()->first();
                if ($user) {
                    $user->balance -= $amount;
                    $user->save();
                }
            } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
                $transaction->status = TransactionStatusEnum::FAILED->value;
            }
            
            $transaction->save();
            Log::info("Midtrans Webhook: Order {$orderId} telah tervalidasi dan diubah statusnya menjadi {$transaction->status}");
        });
    }
}
