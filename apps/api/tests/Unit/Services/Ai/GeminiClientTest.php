<?php

namespace Tests\Unit\Services\Ai;

use App\Exceptions\AiException;
use App\Services\Ai\GeminiClient;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Mockery;
use PHPUnit\Framework\TestCase;

class GeminiClientTest extends TestCase
{
    private HttpClient $httpClient;
    private GeminiClient $client;

    protected function setUp(): void
    {
        $this->httpClient = Mockery::mock(HttpClient::class);
        $this->client = new GeminiClient(
            apiKey: 'test-key',
            model: 'gemini-2.5-flash',
            endpoint: 'https://example.com/v1beta/models',
            httpClient: $this->httpClient,
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function test_正常なレスポンスをデコードして返す(): void
    {
        $payload = json_encode(['score' => 80, 'disclaimer' => 'テスト免責']);
        $apiResponse = json_encode([
            'candidates' => [
                ['content' => ['parts' => [['text' => $payload]]]],
            ],
        ]);

        $this->httpClient->shouldReceive('post')
            ->once()
            ->andReturn(new Response(200, [], $apiResponse));

        $result = $this->client->generateStructured(
            'テストプロンプト',
            ['type' => 'object'],
        );

        $this->assertSame(80, $result['score']);
    }

    public function test_空プロンプトでAiExceptionをスローする(): void
    {
        $this->expectException(AiException::class);
        $this->expectExceptionMessage('Prompt must not be empty.');

        $this->client->generateStructured('', ['type' => 'object']);
    }

    public function test_typeなしスキーマでAiExceptionをスローする(): void
    {
        $this->expectException(AiException::class);
        $this->expectExceptionMessage('Schema is invalid');

        $this->client->generateStructured('プロンプト', []);
    }

    public function test_HTTP429でAiExceptionをスローする(): void
    {
        $this->expectException(AiException::class);
        $this->expectExceptionMessage('rate limit');

        $request = new Request('POST', 'https://example.com');
        $response = new Response(429);
        $this->httpClient->shouldReceive('post')
            ->once()
            ->andThrow(new ClientException('Too Many Requests', $request, $response));

        $this->client->generateStructured('プロンプト', ['type' => 'object']);
    }

    public function test_HTTP500でAiExceptionをスローする(): void
    {
        $this->expectException(AiException::class);
        $this->expectExceptionMessage('server error');

        $request = new Request('POST', 'https://example.com');
        $response = new Response(500);
        $this->httpClient->shouldReceive('post')
            ->once()
            ->andThrow(new ServerException('Internal Server Error', $request, $response));

        $this->client->generateStructured('プロンプト', ['type' => 'object']);
    }

    public function test_不正なJSONレスポンスでAiExceptionをスローする(): void
    {
        $this->expectException(AiException::class);

        $this->httpClient->shouldReceive('post')
            ->once()
            ->andReturn(new Response(200, [], 'invalid json'));

        $this->client->generateStructured('プロンプト', ['type' => 'object']);
    }
}
