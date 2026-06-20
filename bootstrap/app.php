<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                $statusCode = 500;
                $code = 'SERVER_ERROR';
                $message = $e->getMessage() ?: 'Internal Server Error';

                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    $statusCode = $e->status;
                    $code = 'VALIDATION_ERROR';
                    $message = collect($e->errors())->flatten()->first();
                } elseif ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                    $statusCode = $e->getStatusCode();
                    $code = 'HTTP_ERROR_' . $statusCode;
                }

                return response()->json([
                    'status' => 'error',
                    'error' => [
                        'code' => $code,
                        'message' => $message,
                        'timestamp' => now()->toIso8601ZuluString(),
                    ]
                ], $statusCode);
            }
        });
    })->create();
