<?php

namespace App\Services\Ai;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AiReportService
{
    public function __construct(
        private readonly GeminiClient $geminiClient,
        private readonly PromptRegistry $promptRegistry,
        private readonly SchemaRegistry $schemaRegistry,
        private readonly RateLimiter $rateLimiter,
        private readonly string $geminiModel = 'gemini-2.5-flash',
    ) {}

    /**
     * 投資分析レポートを生成してDBに保存する
     *
     * @param  array<string, mixed>  $input  バリデーション済み入力値
     * @return array<string, mixed>  AiReport 形状の配列
     */
    public function buildReport(array $input): array
    {
        $this->rateLimiter->assertAllowed('investment_report');

        $metrics  = $this->calcMetrics($input);
        $prompt   = $this->promptRegistry->build('investment_report', $this->buildPromptVars($input, $metrics));
        $schema   = $this->schemaRegistry->reportSchema();
        $aiResult = $this->geminiClient->generateStructured($prompt, $schema);

        $now = now();
        $this->persistReport($aiResult, $now);

        return $this->toAiReport($aiResult, $now->toIso8601String());
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * 財務指標を一括計算する（shared-utils の calculateInvestment と同一式）
     *
     * annualNoi = annualIncome − annualExpenses − annualRepairReserve
     *
     * @param  array<string, mixed>  $input
     * @return array{annualIncome: float, annualExpenses: int, propertyPrice: int, annualNoi: float, grossYield: float, netYield: float, monthlyCashflow: int}
     */
    private function calcMetrics(array $input): array
    {
        $annualIncome        = (int) $input['monthly_rent'] * 12 * (float) $input['occupancy_rate'];
        $annualRepairReserve = $annualIncome * (float) $input['repair_reserve_rate'];
        $annualExpenses      = (int) $input['annual_expenses'];
        $propertyPrice       = (int) $input['property_price'];
        $annualNoi           = $annualIncome - $annualExpenses - $annualRepairReserve;

        return [
            'annualIncome'    => $annualIncome,
            'annualExpenses'  => $annualExpenses,
            'propertyPrice'   => $propertyPrice,
            'annualNoi'       => $annualNoi,
            'grossYield'      => $propertyPrice > 0 ? round($annualIncome / $propertyPrice * 100, 2) : 0.0,
            'netYield'        => $propertyPrice > 0 ? round($annualNoi / $propertyPrice * 100, 2) : 0.0,
            'monthlyCashflow' => (int) round($annualNoi / 12),
        ];
    }

    /**
     * プロンプトへ埋め込む表示用変数を組み立てる
     *
     * @param  array<string, mixed>  $input
     * @param  array<string, mixed>  $metrics  calcMetrics() の戻り値
     * @return array<string, mixed>
     */
    private function buildPromptVars(array $input, array $metrics): array
    {
        return [
            'property_price'      => number_format($metrics['propertyPrice']),
            'monthly_rent'        => number_format((int) $input['monthly_rent']),
            'occupancy_rate'      => ((float) $input['occupancy_rate'] * 100) . '%',
            'annual_expenses'     => number_format($metrics['annualExpenses']),
            'loan_amount'         => number_format((int) $input['loan_amount']),
            'interest_rate'       => $input['interest_rate'],
            'loan_years'          => $input['loan_years'],
            'repair_reserve_rate' => ((float) $input['repair_reserve_rate'] * 100) . '%',
            'gross_yield'         => $metrics['grossYield'],
            'net_yield'           => $metrics['netYield'],
            'monthly_cashflow'    => number_format($metrics['monthlyCashflow']),
        ];
    }

    /**
     * AI結果と生成日時を ai_reports テーブルへ記録する
     *
     * @param  array<string, mixed>  $aiResult
     */
    private function persistReport(array $aiResult, Carbon $now): void
    {
        DB::table('ai_reports')->insert([
            'model'           => $this->geminiModel,
            'prompt_tokens'   => 0,
            'response_tokens' => 0,
            'status'          => 'success',
            'report_payload'  => json_encode($aiResult),
            'created_at'      => $now,
            'updated_at'      => $now,
        ]);
    }

    /**
     * AI結果にメタフィールドを付与して AiReport 形状に整形する
     *
     * @param  array<string, mixed>  $aiResult
     * @return array<string, mixed>
     */
    private function toAiReport(array $aiResult, string $createdAt): array
    {
        return array_merge($aiResult, [
            'id'         => (string) Str::uuid(),
            'propertyId' => '',
            'status'     => 'completed',
            'createdAt'  => $createdAt,
        ]);
    }
}
