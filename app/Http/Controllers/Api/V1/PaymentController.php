<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\RequestPaymentUrlRequest;
use App\Http\Resources\PaymentUrlResource;
use App\Http\Resources\TransactionStatusResource;
use App\Services\MidtransPaymentService;
use App\Repositories\TransactionRepository;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    public function __construct(
        private readonly MidtransPaymentService $paymentService,
        private readonly TransactionRepository $transactionRepository
    ) {}

    public function requestUrl(RequestPaymentUrlRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        // Karena ini Headless API, user_id biasanya diambil dari token Sanctum via $request->user()->id
        $userId = 1; // Mocking sementara
        
        $result = $this->paymentService->createPayment(
            $userId, 
            $validated['merchant_id'], 
            (float) $validated['amount'], 
            $validated['payment_method']
        );
        
        return (new PaymentUrlResource((object) $result))
            ->response()
            ->setStatusCode(200);
    }

    public function checkStatus(string $orderId): JsonResponse
    {
        $transaction = $this->transactionRepository->findByOrderId($orderId);
        
        if (!$transaction) {
            return response()->json([
                'status' => 'error',
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Transaction not found',
                    'timestamp' => now()->toIso8601ZuluString(),
                ]
            ], 404);
        }

        return (new TransactionStatusResource($transaction))
            ->response()
            ->setStatusCode(200);
    }
}
