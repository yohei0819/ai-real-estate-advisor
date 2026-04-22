<?php

namespace App\Http\Controllers;

use App\Http\Requests\InvestmentReportRequest;
use App\Services\Ai\AiReportService;
use Illuminate\Http\JsonResponse;

class InvestmentReportController extends Controller
{
    public function __construct(
        private readonly AiReportService $aiReportService,
    ) {}

    public function __invoke(InvestmentReportRequest $request): JsonResponse
    {
        $report = $this->aiReportService->buildReport($request->validated());

        return response()->json($report);
    }
}
