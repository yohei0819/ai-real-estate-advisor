<?php

namespace App\Providers;

use App\Services\Ai\AiReportService;
use App\Services\Ai\GeminiClient;
use App\Services\Ai\PromptRegistry;
use App\Services\Ai\RateLimiter;
use App\Services\Ai\SchemaRegistry;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\ServiceProvider;

class AiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // デフォルト値は config/ai.php で一元管理する
        $geminiModel = config('ai.gemini.model');

        $this->app->singleton(GeminiClient::class, function () use ($geminiModel): GeminiClient {
            return new GeminiClient(
                apiKey:     config('ai.gemini.api_key'),
                model:      $geminiModel,
                endpoint:   config('ai.gemini.endpoint'),
                httpClient: new HttpClient(['timeout' => 30]),
            );
        });

        $this->app->singleton(RateLimiter::class, function ($app): RateLimiter {
            return new RateLimiter(
                cache:          $app->make(\Illuminate\Cache\Repository::class),
                limitPerMinute: (int) config('ai.rate_limit_per_minute'),
            );
        });

        $this->app->singleton(PromptRegistry::class);
        $this->app->singleton(SchemaRegistry::class);

        $this->app->singleton(AiReportService::class, function ($app) use ($geminiModel): AiReportService {
            return new AiReportService(
                geminiClient:   $app->make(GeminiClient::class),
                promptRegistry: $app->make(PromptRegistry::class),
                schemaRegistry: $app->make(SchemaRegistry::class),
                rateLimiter:    $app->make(RateLimiter::class),
                geminiModel:    $geminiModel,
            );
        });
    }
}
