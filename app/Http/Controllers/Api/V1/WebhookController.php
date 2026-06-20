<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessMidtransCallbackJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function handleMidtransCallback(Request $request): JsonResponse
    {
        $payload = $request->all();
        
        // Kirim payload ke latar belakang via Redis/Queue
        ProcessMidtransCallbackJob::dispatch($payload);

        // Mengembalikan 200 OK
        return response()->json([
            'status' => 'success',
            'message' => 'Callback received'
        ], 200);
    }
}
