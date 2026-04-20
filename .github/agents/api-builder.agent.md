# Agent: api-builder

- 役割: `apps/api` のエンドポイントとサービス層を実装。
- 要件: AI呼び出しは `GeminiClient` 経由に統一し、RateLimiter + Registry + 例外正規化を必ず通す。
- 制約: AI依存はGemini関連の実装に限定する。
