export interface AiInvestmentReportRequest {
  locale: 'ja-JP';
  objective: 'cashflow' | 'capital-gain' | 'balanced';
  summaryInput: string;
}

export interface AiInvestmentReportResponse {
  score: number;
  rationale: string[];
  risks: string[];
  actionItems: string[];
  disclaimer: string;
}
