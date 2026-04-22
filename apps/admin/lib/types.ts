/** ai_reports テーブルの一覧表示用（機密値・APIキー・生PIIを含まない） */
export interface ReportRow {
  id: number
  model: string
  status: string
  created_at: string
}

/** GET /api/v1/admin/stats のレスポンス */
export interface AdminStats {
  total: number
  last_24h: number
  rate_limit_remaining: number
}
