import type { RiskLevel } from '@ai-real-estate/shared-utils'

/** リスクレベルに対応する表示情報（色・アイコン・文言）。 */
export interface RiskDisplay {
  textClass: string
  label: string
  icon: string
}

/**
 * リスクレベルの表示定義。
 * index.vue・report.vue など複数箇所で共通利用するため composable に集約。
 * 色覚多様性に配慮し、色とアイコン・文言の両方で区別する。
 */
export const RISK_DISPLAY: Record<RiskLevel, RiskDisplay> = {
  low: { textClass: 'text-positive', label: '低リスク', icon: '●' },
  medium: { textClass: 'text-warning', label: '中リスク', icon: '▲' },
  high: { textClass: 'text-risk', label: '高リスク', icon: '■' },
}

export const useRiskDisplay = () => ({ RISK_DISPLAY })
