<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        apiPrefix: '',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // CORS: Vercel フロントエンドからのリクエストを許可
        $middleware->prepend(\Illuminate\Http\Middleware\HandleCors::class);
        // API ルート用ミドルウェアグループを明示定義
        $middleware->api(append: []);
    })
    ->withProviders([
        App\Providers\AiServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (App\Exceptions\AiException $e, \Illuminate\Http\Request $request) {
            // code が HTTP ステータスとして有効な範囲なら使用、それ以外は 502
            $code = $e->getCode();
            $status = ($code >= 400 && $code < 600) ? $code : 502;
            return response()->json(['error' => $e->getMessage()], $status);
        });
    })->create();
