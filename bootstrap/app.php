<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->appendToGroup('api', [
            \App\Http\Middleware\AssignRequestId::class,
            \App\Http\Middleware\ApiRequestLogger::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
                $message = 'Internal Server Error';
                $errorItems = [];

                if ($e instanceof ValidationException) {
                    $statusCode = Response::HTTP_UNPROCESSABLE_ENTITY;
                    $message = 'Error validation';
                    $errorItems = collect($e->errors())->flatten()->all();
                } elseif ($e instanceof AuthorizationException) {
                    $statusCode = Response::HTTP_FORBIDDEN;
                    $message = 'Forbidden';
                } elseif ($e instanceof AuthenticationException) {
                    $statusCode = Response::HTTP_UNAUTHORIZED;
                    $message = $e->getMessage() ?: 'Unauthenticated';
                } elseif ($e instanceof TooManyRequestsHttpException) {
                    $statusCode = Response::HTTP_TOO_MANY_REQUESTS;
                    $message = 'Too many requests';
                } elseif ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
                    $statusCode = Response::HTTP_NOT_FOUND;
                    $message = 'Resource not found';
                } elseif ($e instanceof HttpExceptionInterface) {
                    $statusCode = $e->getStatusCode();
                    $message = $e->getMessage() ?: Response::$statusTexts[$statusCode] ?? 'HTTP Error';
                    if ($statusCode === Response::HTTP_FORBIDDEN) {
                        $message = 'Forbidden';
                    }
                } else {
                    if (config('app.debug')) {
                        $message = $e->getMessage();
                        $errorItems = [
                            'file' => $e->getFile(),
                            'line' => $e->getLine()
                        ];
                    }
                }

                return response()->json([
                    'status' => false,
                    'status_code' => $statusCode,
                    'message' => $message,
                    'error_items' => $errorItems,
                ], $statusCode);
            }
        });
    })->create();
