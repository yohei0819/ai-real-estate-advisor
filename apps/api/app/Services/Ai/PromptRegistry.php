<?php

namespace App\Services\Ai;

use App\Exceptions\AiException;

class PromptRegistry
{
    /** @var array<string, string> */
    private array $prompts = [
        'investment_report' => 'あなたは不動産投資アドバイザーです。JSONのみで回答してください。',
    ];

    public function get(string $name): string
    {
        if (!isset($this->prompts[$name])) {
            throw new AiException("Prompt [{$name}] is not registered.");
        }

        return $this->prompts[$name];
    }
}
