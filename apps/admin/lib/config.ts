/**
 * サーバーサイドでのみ参照する内部 API のベース URL。
 * 環境変数 API_BASE_URL で上書き可能（例: Docker Compose のサービス名）。
 */
export const API_BASE_URL = process.env.API_BASE_URL ?? 'http://localhost:8000'
