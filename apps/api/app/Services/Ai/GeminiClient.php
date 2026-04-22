<?php

namespace App\Services\Ai;

use App\Exceptions\AiException;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

class GeminiClient
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $model,
        private readonly string $endpoint,
        private readonly HttpClient $httpClient,
    ) {}

    /**
     * 構造化されたJSONレスポンスをGemini APIから取得する
     *
     * @param  array<string, mixed>  $schema  JSON Schemaオブジェクト
     * @return array<string, mixed>
     *
     * @throws AiException
     */
    public function generateStructured(string $prompt, array $schema): array
    {
        if ($prompt === '') {
            throw new AiException('Prompt must not be empty.');
        }

        if (! isset($schema['type'])) {
            throw new AiException('Schema is invalid: missing "type" field.');
        }

        $url = "{$this->endpoint}/{$this->model}:generateContent?key={$this->apiKey}";

        try {
            $response = $this->httpClient->post($url, [
                'json' => [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]],
                    ],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json',
                        'responseSchema' => $schema,
                    ],
                ],
            ]);
        } catch (ClientException $e) {
            $status = $e->getResponse()->getStatusCode();
            throw $status === 429
                ? AiException::rateLimitExceeded()
                : AiException::clientError($status, $e);
        } catch (ServerException $e) {
            throw AiException::serverError($e);
        }

        $body    = (string) $response->getBody();
        $decoded = json_decode($body, true);

        if (! is_array($decoded)) {
            throw new AiException('Gemini API returned invalid JSON.');
        }

        $text = $this->extractResponseText($decoded);

        $result = json_decode($text, true);

        if (! is_array($result)) {
            throw new AiException('Gemini API response content is not valid JSON.');
        }

        return $result;
    }

    /**
     * Gemini レスポンスの candidates 配列から生成テキストを取り出す。
     *
     * 期待構造:
     *   candidates[0].content.parts[0].text
     *
     * @param  array<string, mixed>  $decoded
     *
     * @throws AiException
     */
    private function extractResponseText(array $decoded): string
    {
        $text = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (! is_string($text)) {
            throw new AiException('Gemini API response has unexpected structure.');
        }

        return $text;
    }
}
