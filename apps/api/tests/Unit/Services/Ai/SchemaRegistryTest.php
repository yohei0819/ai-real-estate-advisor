<?php

namespace Tests\Unit\Services\Ai;

use App\Services\Ai\SchemaRegistry;
use PHPUnit\Framework\TestCase;

class SchemaRegistryTest extends TestCase
{
    private SchemaRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new SchemaRegistry();
    }

    public function test_reportSchemaがtype_objectを返す(): void
    {
        $schema = $this->registry->reportSchema();
        $this->assertSame('object', $schema['type']);
    }

    public function test_reportSchemaのrequiredフィールドがAiReportと一致する(): void
    {
        $schema   = $this->registry->reportSchema();
        $required = $schema['required'];

        foreach (['riskScore', 'summary', 'recommendation', 'rationale', 'risks', 'actionItems', 'disclaimer'] as $field) {
            $this->assertContains($field, $required, "required に {$field} が含まれていません");
        }
    }

    public function test_reportSchemaのpropertiesにriskScoreがintegerで定義される(): void
    {
        $schema = $this->registry->reportSchema();
        $this->assertSame('integer', $schema['properties']['riskScore']['type']);
        $this->assertSame(1,  $schema['properties']['riskScore']['minimum']);
        $this->assertSame(10, $schema['properties']['riskScore']['maximum']);
    }

    public function test_reportSchemaのpropertiesにsummaryとrecommendationがstringで定義される(): void
    {
        $properties = $this->registry->reportSchema()['properties'];
        $this->assertSame('string', $properties['summary']['type']);
        $this->assertSame('string', $properties['recommendation']['type']);
    }

    public function test_reportSchemaにscoreキーが存在しない(): void
    {
        $schema = $this->registry->reportSchema();
        $this->assertArrayNotHasKey('score', $schema['properties']);
        $this->assertNotContains('score', $schema['required']);
    }
}
