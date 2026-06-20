<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionStatusResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'order_id' => $this->resource->order_id,
            'transaction_status' => $this->resource->status,
            'payment_method_used' => $this->resource->payment_method ?? '-',
            'amount_paid' => (float) $this->resource->amount,
            'reference_code' => $this->resource->order_id,
            'updated_at' => $this->resource->updated_at->toIso8601ZuluString(),
        ];
    }

    public function with(Request $request): array
    {
        return [
            'status' => 'success',
        ];
    }
}
