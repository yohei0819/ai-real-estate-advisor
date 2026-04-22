import type { InvestmentInput, InvestmentResult } from '@ai-real-estate/shared-types';

const safePercent = (value: number): number => Math.min(1, Math.max(0, value));

const FLOAT_EPSILON = 1e-9;

/**
 * 無利子ローン（interestRate ≒ 0）と判定する閾値。
 * この値未満の月利は元本均等返済として扱う。
 */
const ZERO_INTEREST_THRESHOLD = FLOAT_EPSILON;

/**
 * 元利均等返済の年間ローン返済額を計算する
 * @param loanAmount 借入金額（円）
 * @param interestRate 年利（%）
 * @param loanYears 借入年数
 * @returns 年間返済額（円）
 */
export const calculateLoanRepayment = (
  loanAmount: number,
  interestRate: number,
  loanYears: number,
): number => {
  if (loanAmount <= 0 || loanYears <= 0) return 0;
  const r = interestRate / 12 / 100;
  // 無利子ローンは元本均等返済（年間額を返す）
  if (r < ZERO_INTEREST_THRESHOLD) return loanAmount / loanYears;
  const n = loanYears * 12;
  const pow = Math.pow(1 + r, n);
  const monthly = (loanAmount * r * pow) / (pow - 1);
  return monthly * 12;
};

/**
 * 不動産投資の収益指標を計算する
 * @param input 投資入力パラメータ
 * @returns 投資計算結果
 */
export const calculateInvestment = (input: InvestmentInput): InvestmentResult => {
  const occupancyRate = safePercent(input.occupancyRate);
  const repairReserveRate = safePercent(input.repairReserveRate);

  const annualIncome = input.monthlyRent * 12 * occupancyRate;
  const annualRepairReserve = annualIncome * repairReserveRate;

  // annualCashflow: ローン返済前の年間収支（NOI相当: 収入 - 運営費用 - 修繕積立費）
  const annualCashflow = annualIncome - input.annualExpenses - annualRepairReserve;

  const grossYield = input.propertyPrice > 0
    ? (annualIncome / input.propertyPrice) * 100
    : 0;

  // 実質利回り = NOI / 物件価格（annualCashflow と同じ分子）
  const netYield = input.propertyPrice > 0
    ? (annualCashflow / input.propertyPrice) * 100
    : 0;

  const loanRepayment = calculateLoanRepayment(
    input.loanAmount,
    input.interestRate,
    input.loanYears,
  );

  // ローン返済後の年間・月次収支
  const annualCashflowAfterLoan = annualCashflow - loanRepayment;
  const monthlyCashflow = annualCashflowAfterLoan / 12;

  // ROI: ローン返済後CFを自己資金で割る（レバレッジ効果を正確に評価）
  const equity = input.propertyPrice - input.loanAmount;
  const roi = equity > 0 ? (annualCashflowAfterLoan / equity) * 100 : 0;

  return {
    annualIncome,
    annualCashflow,
    annualCashflowAfterLoan,
    grossYield,
    netYield,
    monthlyCashflow,
    loanRepayment,
    roi,
  };
};
