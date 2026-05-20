<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('openapi:generate', function () {
    $paths = [];

    foreach (Route::getRoutes() as $route) {
        $uri = '/'.ltrim($route->uri(), '/');

        if (!str_starts_with($uri, '/api/')) {
            continue;
        }

        $normalizedUri = preg_replace('/\{([^}]+)\}/', '{$1}', $uri);
        $methods = array_values(array_diff($route->methods(), ['HEAD']));
        $middlewares = $route->gatherMiddleware();

        foreach ($methods as $method) {
            $methodLower = strtolower($method);
            $responses = [];
            $routeName = $route->getName() ?? '';

            if (in_array('auth:api', $middlewares, true)) {
                $responses[(string) Response::HTTP_UNAUTHORIZED] = ['description' => 'Unauthenticated'];
            }

            if (in_array('throttle:5,1', $middlewares, true)) {
                $responses[(string) Response::HTTP_TOO_MANY_REQUESTS] = ['description' => 'Too many requests'];
            }

            $isCreateAction = str_contains($routeName, '.store') || str_ends_with($normalizedUri, '/register');

            if ($isCreateAction) {
                $responses[(string) Response::HTTP_CREATED] = ['description' => 'Created'];
            } else {
                $responses[(string) Response::HTTP_OK] = ['description' => 'Success'];
            }

            if (str_contains($normalizedUri, '{')) {
                $responses[(string) Response::HTTP_NOT_FOUND] = ['description' => 'Not found'];
            }

            $responses[(string) Response::HTTP_UNPROCESSABLE_ENTITY] = ['description' => 'Validation error'];

            $paths[$normalizedUri][$methodLower] = [
                'summary' => $route->getName() ?: $method.' '.$normalizedUri,
                'responses' => (object) $responses,
            ];
        }
    }

    ksort($paths);

    $spec = [
        'openapi' => '3.0.3',
        'info' => [
            'title' => config('app.name', 'Laravel API'),
            'version' => '1.0.0',
            'generated_at' => now()->toIso8601String(),
        ],
        'paths' => (object) $paths,
    ];

    $outputPath = base_path('openapi/openapi.generated.json');
    File::ensureDirectoryExists(dirname($outputPath));
    File::put($outputPath, json_encode($spec, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL);

    $this->info("OpenAPI generated: {$outputPath}");
})->purpose('Generate OpenAPI JSON from registered API routes');
