<?php

namespace Tests\Unit\Services\Ai;

use App\Exceptions\AiException;
use App\Services\Ai\RateLimiter;
use Illuminate\Cache\Repository as Cache;
use Mockery;
use PHPUnit\Framework\TestCase;

class RateLimiterTest extends TestCase
{
    private Cache $cache;

    protected function setUp(): void
    {
        $this->cache = Mockery::mock(Cache::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function test_上限未満では例外が発生しない(): void
    {
        $this->cache->shouldReceive('add')->once()->with('ai_rate_limit:test', 0, 60);
        $this->cache->shouldReceive('increment')->once()->with('ai_rate_limit:test')->andReturn(10);

        $limiter = new RateLimiter($this->cache, 30);
        $limiter->assertAllowed('test');

        $this->expectNotToPerformAssertions();
    }

    public function test_上限超過でAiExceptionをスローする(): void
    {
        $this->expectException(AiException::class);
        $this->expectExceptionMessage('rate limit exceeded');

        $this->cache->shouldReceive('add')->once();
        $this->cache->shouldReceive('increment')->once()->andReturn(31);

        $limiter = new RateLimiter($this->cache, 30);
        $limiter->assertAllowed('test');
    }

    public function test_上限ちょうどで通過する(): void
    {
        $this->cache->shouldReceive('add')->once();
        $this->cache->shouldReceive('increment')->once()->andReturn(30);

        $limiter = new RateLimiter($this->cache, 30);
        $limiter->assertAllowed('test');

        $this->expectNotToPerformAssertions();
    }

    public function test_上限プラス1で拒否される(): void
    {
        $this->expectException(AiException::class);

        $this->cache->shouldReceive('add')->once();
        $this->cache->shouldReceive('increment')->once()->andReturn(31);

        $limiter = new RateLimiter($this->cache, 30);
        $limiter->assertAllowed('test');
    }

    public function test_AiExceptionのコードが429である(): void
    {
        $this->cache->shouldReceive('add')->once();
        $this->cache->shouldReceive('increment')->once()->andReturn(31);

        $limiter = new RateLimiter($this->cache, 30);

        try {
            $limiter->assertAllowed('test');
            $this->fail('AiException should have been thrown');
        } catch (AiException $e) {
            $this->assertSame(429, $e->getCode());
        }
    }
}
