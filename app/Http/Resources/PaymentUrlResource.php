<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentUrlResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'order_id' => $this->resource->order_id,
            'payment_url' => $this->resource->payment_url,
            'return_url' => $this->resource->return_url,
            'expiry_seconds' => $this->resource->expiry_seconds ?? 900,
        ];
    }

    public function with(Request $request): array
    {
        return [
            'status' => 'success',
        ];
    }
}
