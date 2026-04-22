<?php

namespace Tests\Unit\Services\Ai;

use App\Exceptions\AiException;
use App\Services\Ai\AiReportService;
use App\Services\Ai\GeminiClient;
use App\Services\Ai\PromptRegistry;
use App\Services\Ai\RateLimiter;
use App\Services\Ai\SchemaRegistry;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class AiReportServiceTest extends TestCase
{
    // Unit テストは DB Facade をモックするため RefreshDatabase 不要
    private GeminiClient $geminiClient;
    private PromptRegistry $promptRegistry;
    private SchemaRegistry $schemaRegistry;
    private RateLimiter $rateLimiter;
    private AiReportService $service;

    /** @var array<string, mixed> */
    private array $validInput = [
        'property_price'      => 30000000,
        'monthly_rent'        => 100000,
        'occupancy_rate'      => 0.95,
        'annual_expenses'     => 200000,
        'loan_amount'         => 20000000,
        'interest_rate'       => 2.0,
        'loan_years'          => 35,
        'repair_reserve_rate' => 0.05,
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->geminiClient   = Mockery::mock(GeminiClient::class);
        $this->promptRegistry = Mockery::mock(PromptRegistry::class);
        $this->schemaRegistry = Mockery::mock(SchemaRegistry::class);
        $this->rateLimiter    = Mockery::mock(RateLimiter::class);

        $this->service = new AiReportService(
            $this->geminiClient,
            $this->promptRegistry,
            $this->schemaRegistry,
            $this->rateLimiter,
        );

        DB::shouldReceive('table->insert')->andReturn(true)->byDefault();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_正常系でGeminiClientの戻り値を返す(): void
    {
        $aiResult = [
            'riskScore'      => 6,
            'summary'        => 'テスト総合所見',
            'recommendation' => '慎重検討',
            'rationale'      => ['根拠1'],
            'risks'          => ['リスク1'],
            'actionItems'    => ['アクション1'],
            'disclaimer'     => 'テスト免責',
        ];

        $this->rateLimiter->shouldReceive('assertAllowed')->once()->with('investment_report');
        $this->promptRegistry->shouldReceive('build')->once()->andReturn('テストプロンプト');
        $this->schemaRegistry->shouldReceive('reportSchema')->once()->andReturn(['type' => 'object']);
        $this->geminiClient->shouldReceive('generateStructured')->once()->andReturn($aiResult);

        $result = $this->service->buildReport($this->validInput);

        // AiReport 形状に必須フィールドが付与されていること
        $this->assertSame(6, $result['riskScore']);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('status', $result);
        $this->assertArrayHasKey('createdAt', $result);
        $this->assertSame('completed', $result['status']);
    }

    public function test_monthlyCashflowが修繕積立費を控除した値になる(): void
    {
        // input: annualIncome = 100000 * 12 * 0.95 = 1_140_000
        //        annualRepairReserve = 1_140_000 * 0.05 = 57_000
        //        annualExpenses = 200_000
        //        annualNoi = 1_140_000 - 200_000 - 57_000 = 883_000
        //        monthlyCashflow = round(883_000 / 12) = 73583
        $capturedPrompt = '';

        $this->rateLimiter->shouldReceive('assertAllowed')->once();
        $this->promptRegistry->shouldReceive('build')
            ->once()
            ->andReturnUsing(function (string $name, array $vars) use (&$capturedPrompt) {
                $capturedPrompt = $vars['monthly_cashflow'];
                return 'テストプロンプト';
            });
        $this->schemaRegistry->shouldReceive('reportSchema')->once()->andReturn(['type' => 'object']);
        $this->geminiClient->shouldReceive('generateStructured')->once()->andReturn([
            'riskScore' => 5, 'summary' => '', 'recommendation' => '',
            'rationale' => [], 'risks' => [], 'actionItems' => [], 'disclaimer' => '',
        ]);

        $this->service->buildReport($this->validInput);

        // number_format(73583) = "73,583"
        $this->assertSame(number_format(73583), $capturedPrompt);
    }

    public function test_RateLimiter例外が伝播する(): void
    {
        $this->expectException(AiException::class);
        $this->expectExceptionMessage('rate limit exceeded');

        $this->rateLimiter->shouldReceive('assertAllowed')
            ->once()
            ->andThrow(AiException::rateLimitExceeded());

        $this->service->buildReport($this->validInput);
    }
}
