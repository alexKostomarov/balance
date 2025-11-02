<?php

use App\BalanceApp\Application\Exception\BalanceNotFoundException;
use App\BalanceApp\Application\Exception\TransactionFailedException;
use App\BalanceApp\Application\Exception\UserNotFoundException;
use App\BalanceApp\Domain\Exception\InsufficientFundsException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ValidationException $e, $request) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        });

        $exceptions->render(function (BalanceNotFoundException $e, $request) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 404);
        });

        $exceptions->render(function (UserNotFoundException $e, $request) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 404);
        });

        $exceptions->render(function (InsufficientFundsException $e, $request) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 409);
        });

        $exceptions->render(function (TransactionFailedException $e, $request) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        });
    })
    ->create();
