# Laravel Instructions

## Scope
`apps/api` のみ。

## Rules
1. AI連携は `GeminiClient` 経由で統一する。
2. サービス層でユースケースを完結させ、Controllerを薄く保つ。
3. Prompt/SchemaはRegistry経由で参照し、文字列を散在させない。
4. 例外は `AiException` へ正規化する。
5. Gemini呼び出し前にRateLimiterを適用する。
