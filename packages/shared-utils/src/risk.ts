export type RiskLevel = 'low' | 'medium' | 'high';

// ─── 判定閾値（業務仕様変更時はここだけ修正する） ─────────────────────────────

/** 空室リスク: 稼働率がこの値以上なら low */
const VACANCY_LOW_THRESHOLD  = 0.95;
/** 空室リスク: 稼働率がこの値以上なら medium（未満は high） */
const VACANCY_MED_THRESHOLD  = 0.90;

/** 修繕リスク: 築年数がこの値未満なら low */
const REPAIR_LOW_AGE  = 10;
/** 修繕リスク: 築年数がこの値未満なら medium（以上は high） */
const REPAIR_MED_AGE  = 20;

/** ローンリスク(LTV): この値未満なら low */
const LTV_LOW_THRESHOLD = 0.50;
/** ローンリスク(LTV): この値未満なら medium（以上は high） */
const LTV_MED_THRESHOLD = 0.70;

// ─── 評価関数 ────────────────────────────────────────────────────────────────

/**
 * 空室リスクを評価する
 * @param occupancyRate 稼働率（0〜1）
 * @returns リスクレベル（low: 95%以上, medium: 90%以上, high: 90%未満）
 */
export const calcVacancyRisk = (occupancyRate: number): RiskLevel => {
  if (occupancyRate >= VACANCY_LOW_THRESHOLD) return 'low';
  if (occupancyRate >= VACANCY_MED_THRESHOLD) return 'medium';
  return 'high';
};

/**
 * 修繕リスクを評価する
 * @param buildYear 建築年（西暦）
 * @param currentYear 現在年（省略時はシステム年）
 * @returns リスクレベル（low: 築10年未満, medium: 築20年未満, high: 築20年以上）
 */
export const calcRepairRisk = (buildYear: number, currentYear?: number): RiskLevel => {
  const age = (currentYear ?? new Date().getFullYear()) - buildYear;
  if (age < REPAIR_LOW_AGE) return 'low';
  if (age < REPAIR_MED_AGE) return 'medium';
  return 'high';
};

/**
 * ローンリスク（LTV）を評価する
 * @param loanAmount 借入金額（円）
 * @param propertyPrice 物件価格（円）
 * @returns リスクレベル（low: LTV50%未満, medium: LTV70%未満, high: LTV70%以上）
 */
export const calcLoanRisk = (loanAmount: number, propertyPrice: number): RiskLevel => {
  if (propertyPrice <= 0) return 'high';
  const ltv = loanAmount / propertyPrice;
  if (ltv < LTV_LOW_THRESHOLD) return 'low';
  if (ltv < LTV_MED_THRESHOLD) return 'medium';
  return 'high';
};
