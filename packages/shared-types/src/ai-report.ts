export type AiReportStatus = 'pending' | 'processing' | 'completed' | 'failed';

/**
 * AI生成の投資分析レポート。
 *
 * `rationale` / `risks` / `actionItems` / `disclaimer` は
 * SchemaRegistry の required 定義により Gemini が必ず返すフィールドのため非 optional。
 */
export interface AiReport {
  id: string;
  propertyId: string;
  status: AiReportStatus;
  /** 総合所見 */
  summary: string;
  /** リスクスコア（1=最低リスク〜10=最高リスク） */
  riskScore: number;
  /** 投資判断の推奨文 */
  recommendation: string;
  createdAt: string;
  /** 判断根拠（箇条書き） */
  rationale: string[];
  /** リスク項目一覧 */
  risks: string[];
  /** 推奨アクション */
  actionItems: string[];
  /** 免責事項 */
  disclaimer: string;
}
