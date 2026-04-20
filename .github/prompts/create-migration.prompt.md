# Prompt: create-migration

`apps/api/database/migrations` にLaravel migrationを追加してください。

## 必須条件
- 監査に必要なAIレスポンスメタデータ（model, tokens, status）を保存可能にする。
- 機密値(API key/生PII)は保存しない。
