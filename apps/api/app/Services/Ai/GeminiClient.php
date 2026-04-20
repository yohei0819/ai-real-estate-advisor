<?php

namespace App\Services\Ai;

use App\Exceptions\AiException;

class GeminiClient
{
    /**
     * @param array<string, mixed> $schema
     * @return array<string, mixed>
     */
    public function generateStructured(string $prompt, array $schema): array
    {
        if ($prompt === '') {
            throw new AiException('Prompt must not be empty.');
        }

        if (!isset($schema['type'])) {
            throw new AiException('Schema is invalid.');
        }

        return [
            'score' => 75,
            'rationale' => ['想定賃料と空室率を反映'],
            'risks' => ['金利上昇リスク'],
            'actionItems' => ['修繕積立の見直し'],
            'disclaimer' => '本結果は投資判断の参考情報です。',
            'meta' => ['provider' => 'gemini'],
        ];
    }
}
