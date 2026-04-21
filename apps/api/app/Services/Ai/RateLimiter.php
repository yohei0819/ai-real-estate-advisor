<?php

namespace App\Services\Ai;

use App\Exceptions\AiException;

class RateLimiter
{
    public function assertAllowed(int $recentRequests, int $limitPerMinute): void
    {
        if ($recentRequests >= $limitPerMinute) {
            throw new AiException('AI rate limit exceeded.');
        }
    }
}
