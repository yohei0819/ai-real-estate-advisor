export interface InvestmentInput {
  propertyPrice: number;
  monthlyRent: number;
  occupancyRate: number;
  annualExpenses: number;
}

export interface InvestmentResult {
  annualIncome: number;
  annualCashflow: number;
  grossYield: number;
}
