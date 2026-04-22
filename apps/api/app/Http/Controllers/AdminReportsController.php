<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AdminReportsController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $reports = DB::table('ai_reports')
            ->select('id', 'model', 'status', 'created_at')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        return response()->json($reports);
    }
}
