<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QrValidationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'qr_type' => $this->resource->qr_type ?? 'STATIC',
            'merchant_id' => $this->resource->merchant_id,
            'merchant_name' => $this->resource->merchant_name,
            'is_verified' => $this->resource->is_verified ?? true,
            'amount' => (float) ($this->resource->amount ?? 0),
            'items' => $this->resource->items ?? [],
            'expires_at' => $this->resource->expires_at ?? null,
        ];
    }

    public function with(Request $request): array
    {
        return [
            'status' => 'success',
        ];
    }
}
