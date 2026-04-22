<?php

namespace App\Http\Controllers;

use App\Services\Ai\RateLimiter;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AdminStatsController extends Controller
{
    public function __construct(
        private readonly RateLimiter $rateLimiter,
    ) {}

    public function __invoke(): JsonResponse
    {
        $total   = DB::table('ai_reports')->count();
        $last24h = DB::table('ai_reports')
            ->where('created_at', '>=', now()->subHours(24))
            ->count();

        return response()->json([
            'total'               => $total,
            'last_24h'            => $last24h,
            'rate_limit_remaining' => $this->rateLimiter->remaining('investment_report'),
        ]);
    }
}
