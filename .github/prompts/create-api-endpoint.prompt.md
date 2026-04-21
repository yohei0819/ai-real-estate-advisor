# Prompt: create-api-endpoint

`apps/api` にLaravelのAPIエンドポイントを追加してください。

## 必須条件
- AI連携は `GeminiClient` 経由のみ。
- Promptは `PromptRegistry` から取得。
- Schemaは `SchemaRegistry` から取得。
- RateLimiterを適用し、例外は `AiException` へ正規化。
