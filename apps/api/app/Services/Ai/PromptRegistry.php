<?php

namespace App\Services\Ai;

class PromptRegistry
{
    /** @var array<string, string> */
    private array $prompts = [
        'investment_report' => 'あなたは不動産投資アドバイザーです。JSONのみで回答してください。',
    ];

    public function get(string $name): string
    {
        return $this->prompts[$name] ?? '';
    }
}
