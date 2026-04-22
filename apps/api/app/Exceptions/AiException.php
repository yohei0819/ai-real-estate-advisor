<?php

namespace App\Exceptions;

use RuntimeException;

class AiException extends RuntimeException
{
    /**
     * ローカルのレート制限、または Gemini API から 429 が返ったときに生成する。
     */
    public static function rateLimitExceeded(): self
    {
        return new self('AI rate limit exceeded.', 429);
    }

    /**
     * Gemini API から 5xx 系エラーが返ったときに生成する。
     */
    public static function serverError(?\Throwable $previous = null): self
    {
        return new self('Gemini API server error.', 502, $previous);
    }

    /**
     * Gemini API から 429 以外の 4xx 系エラーが返ったときに生成する。
     */
    public static function clientError(int $status, ?\Throwable $previous = null): self
    {
        return new self("Gemini API client error: {$status}", $status, $previous);
    }
}
