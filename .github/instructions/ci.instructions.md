# CI Instructions

- 変更範囲に応じて `apps/api`, `apps/web`, `apps/admin`, `packages/*` を分割検証する。
- AI関連変更は最低限、静的解析 + ユニットテスト + セキュリティスキャンを実行する。
- Gemini APIキーはCIでSecret管理し、ログへ出力しない。
