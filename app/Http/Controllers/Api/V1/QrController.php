<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ValidateQrRequest;
use App\Http\Resources\QrValidationResource;
use App\Services\QrValidationService;
use Illuminate\Http\JsonResponse;

class QrController extends Controller
{
    public function __construct(
        private readonly QrValidationService $qrValidationService
    ) {}

    public function validatePayload(ValidateQrRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        $result = $this->qrValidationService->validate($validated['qr_payload']);
        
        return (new QrValidationResource((object) $result))
            ->response()
            ->setStatusCode(200);
    }
}
