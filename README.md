# AI不動産投資シミュレーター

Gemini API連携を前提にしたモノレポ初期雛形です。

## 初期構成

- `apps/web`: Nuxt 3 フロントエンド（予定）
- `apps/admin`: Next.js 管理画面（予定）
- `apps/api`: Laravel 11 + Gemini API サービス層雛形
- `packages/shared-types`: 共通型
- `packages/shared-utils`: 投資計算ロジック
- `packages/ai-prompts`: AIプロンプト関連型
- `packages/ui`: UIトークン / Tailwindプリセット
- `.github/instructions`, `.github/prompts`, `.github/agents`: AIペア開発向けガイド

## Gemini統一方針

1. AIコードは `apps/api/app/Services/Ai/GeminiClient` 経由に統一
2. Prompt/Schema/RateLimitをサービス層に集約
3. AI連携の実装と運用はGeminiに統一
