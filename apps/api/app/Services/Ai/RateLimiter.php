<?php

namespace App\Services\Ai;

use App\Exceptions\AiException;
use Illuminate\Cache\Repository as Cache;

class RateLimiter
{
    public function __construct(
        private readonly Cache $cache,
        private readonly int $limitPerMinute,
    ) {}

    /**
     * レート制限を確認し、超過時は AiException をスローする。
     * Cache::add() + increment() で固定ウィンドウ・アトミック操作を実現する。
     *
     * @throws AiException
     */
    public function assertAllowed(string $key): void
    {
        $cacheKey = "ai_rate_limit:{$key}";

        // 初回リクエスト時のみ TTL=60s でカウンタを作成（以降は add() が no-op）
        $this->cache->add($cacheKey, 0, 60);

        // increment() はアトミック操作 — レースコンディションを防ぐ
        $count = $this->cache->increment($cacheKey);

        if ($count > $this->limitPerMinute) {
            throw AiException::rateLimitExceeded();
        }
    }

    /**
     * 現在のウィンドウで残り利用可能回数を返す。
     * カウンタが存在しない場合は上限値をそのまま返す。
     */
    public function remaining(string $key): int
    {
        $cacheKey = "ai_rate_limit:{$key}";
        $count    = (int) $this->cache->get($cacheKey, 0);

        return max(0, $this->limitPerMinute - $count);
    }
}
