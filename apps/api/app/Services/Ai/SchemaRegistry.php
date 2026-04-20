<?php

namespace App\Services\Ai;

class SchemaRegistry
{
    /** @return array<string, mixed> */
    public function reportSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['score', 'rationale', 'risks', 'actionItems', 'disclaimer'],
            'properties' => [
                'score' => ['type' => 'integer'],
                'rationale' => ['type' => 'array', 'items' => ['type' => 'string']],
                'risks' => ['type' => 'array', 'items' => ['type' => 'string']],
                'actionItems' => ['type' => 'array', 'items' => ['type' => 'string']],
                'disclaimer' => ['type' => 'string'],
            ],
        ];
    }
}
