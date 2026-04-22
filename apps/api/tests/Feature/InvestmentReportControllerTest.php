<?php

namespace Tests\Feature;

use App\Exceptions\AiException;
use App\Services\Ai\AiReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class InvestmentReportControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<string, mixed> */
    private array $validPayload = [
        'property_price'      => 30000000,
        'monthly_rent'        => 100000,
        'occupancy_rate'      => 0.95,
        'annual_expenses'     => 200000,
        'loan_amount'         => 20000000,
        'interest_rate'       => 2.0,
        'loan_years'          => 35,
        'repair_reserve_rate' => 0.05,
    ];

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_有効な入力でHTTP200とレポートが返る(): void
    {
        $report = [
            'riskScore'      => 6,
            'summary'        => 'テスト総合所見',
            'recommendation' => '慎重検討',
            'rationale'      => ['根拠1'],
            'risks'          => ['リスク1'],
            'actionItems'    => ['アクション1'],
            'disclaimer'     => 'テスト免責',
            'id'             => 'test-uuid',
            'propertyId'     => '',
            'status'         => 'completed',
            'createdAt'      => '2026-01-01T00:00:00+00:00',
        ];

        $mock = Mockery::mock(AiReportService::class);
        $mock->shouldReceive('buildReport')->once()->andReturn($report);
        $this->app->instance(AiReportService::class, $mock);

        $response = $this->postJson('/api/v1/investment/report', $this->validPayload);

        $response->assertStatus(200)
            ->assertJsonPath('riskScore', 6)
            ->assertJsonStructure([
                'riskScore', 'summary', 'recommendation',
                'rationale', 'risks', 'actionItems', 'disclaimer',
                'id', 'status', 'createdAt',
            ]);
    }

    public function test_バリデーション失敗でHTTP422が返る(): void
    {
        $response = $this->postJson('/api/v1/investment/report', [
            'property_price' => -1,
        ]);

        $response->assertStatus(422);
    }

    public function test_レート超過でHTTP429が返る(): void
    {
        $mock = Mockery::mock(AiReportService::class);
        $mock->shouldReceive('buildReport')
            ->once()
            ->andThrow(AiException::rateLimitExceeded());
        $this->app->instance(AiReportService::class, $mock);

        $response = $this->postJson('/api/v1/investment/report', $this->validPayload);

        $response->assertStatus(429);
    }

    public function test_AI障害でHTTP502が返る(): void
    {
        $mock = Mockery::mock(AiReportService::class);
        $mock->shouldReceive('buildReport')
            ->once()
            ->andThrow(AiException::serverError());
        $this->app->instance(AiReportService::class, $mock);

        $response = $this->postJson('/api/v1/investment/report', $this->validPayload);

        $response->assertStatus(502);
    }
}
