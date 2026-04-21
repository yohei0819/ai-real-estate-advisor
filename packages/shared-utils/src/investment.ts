import type { InvestmentInput, InvestmentResult } from '@ai-real-estate/shared-types';

const safePercent = (value: number): number => Math.min(1, Math.max(0, value));

export const calculateInvestment = (input: InvestmentInput): InvestmentResult => {
  const occupancyRate = safePercent(input.occupancyRate);
  const annualIncome = input.monthlyRent * 12 * occupancyRate;
  const annualCashflow = annualIncome - input.annualExpenses;
  const grossYield = input.propertyPrice > 0 ? (annualIncome / input.propertyPrice) * 100 : 0;

  return { annualIncome, annualCashflow, grossYield };
};
