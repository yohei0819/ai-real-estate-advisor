<?php

namespace App\Services\Ai;

class SchemaRegistry
{
    /** @return array<string, mixed> */
    public function reportSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['riskScore', 'summary', 'recommendation', 'rationale', 'risks', 'actionItems', 'disclaimer'],
            'properties' => [
                'riskScore'      => ['type' => 'integer', 'minimum' => 1, 'maximum' => 10],
                'summary'        => ['type' => 'string'],
                'recommendation' => ['type' => 'string'],
                'rationale'      => ['type' => 'array', 'items' => ['type' => 'string']],
                'risks'          => ['type' => 'array', 'items' => ['type' => 'string']],
                'actionItems'    => ['type' => 'array', 'items' => ['type' => 'string']],
                'disclaimer'     => ['type' => 'string'],
            ],
        ];
    }
}
