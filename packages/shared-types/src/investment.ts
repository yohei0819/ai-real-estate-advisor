export interface InvestmentInput {
  propertyPrice: number;
  monthlyRent: number;
  occupancyRate: number;
  annualExpenses: number;
  loanAmount: number;
  interestRate: number;
  loanYears: number;
  /** 年間収入に対する修繕積立費の比率（例: 0.05 = 5%） */
  repairReserveRate: number;
}

export interface InvestmentResult {
  annualIncome: number;
  /** ローン返済前の年間収支（NOI相当: 収入 - 運営費用 - 修繕積立費） */
  annualCashflow: number;
  /** ローン返済後の年間収支 */
  annualCashflowAfterLoan: number;
  grossYield: number;
  netYield: number;
  /** ローン返済後の月次収支 */
  monthlyCashflow: number;
  loanRepayment: number;
  roi: number;
}
