# Copilot Instructions (AI不動産投資シミュレーター)

- 本リポジトリのAI実装は **Gemini API のみ** を対象とする。
- アプリケーションコードから直接Gemini APIを呼ばず、必ず `apps/api/app/Services/Ai/GeminiClient` を経由する。
- プロンプトとレスポンススキーマは `apps/api/app/Services/Ai/PromptRegistry` / `SchemaRegistry` で一元管理する。
- レート制限は `apps/api/app/Services/Ai/RateLimiter` に集約する。
- テストでは外部APIを実行せず、GeminiClientをモックして挙動を検証する。
