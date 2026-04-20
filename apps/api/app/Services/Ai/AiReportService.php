<?php

namespace App\Services\Ai;

class AiReportService
{
    public function __construct(
        private readonly GeminiClient $geminiClient,
        private readonly PromptRegistry $promptRegistry,
        private readonly SchemaRegistry $schemaRegistry,
        private readonly RateLimiter $rateLimiter,
    ) {
    }

    /** @return array<string, mixed> */
    public function buildReport(int $recentRequests, int $limitPerMinute): array
    {
        $this->rateLimiter->assertAllowed($recentRequests, $limitPerMinute);

        return $this->geminiClient->generateStructured(
            $this->promptRegistry->get('investment_report'),
            $this->schemaRegistry->reportSchema(),
        );
    }
}
