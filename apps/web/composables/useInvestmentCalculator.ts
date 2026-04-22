import {
  calculateInvestment,
  calcVacancyRisk,
  calcRepairRisk,
  calcLoanRisk,
} from '@ai-real-estate/shared-utils'
import type { RiskLevel } from '@ai-real-estate/shared-utils'
import type { InvestmentInput, InvestmentResult } from '@ai-real-estate/shared-types'

/** フォーム用の拡張入力型（buildYear は InvestmentInput に含まれないが修繕リスク算出に必要） */
type CalculatorInput = Partial<InvestmentInput & { buildYear: number }>

// ─── バリデーションメッセージ（文言変更はここだけ修正） ────────────────────────
const MSG = {
  propertyPrice:     '物件価格は1以上の整数を入力してください。',
  monthlyRent:       '月額賃料は0以上の整数を入力してください。',
  occupancyRate:     '稼働率は0〜1の範囲で入力してください。',
  annualExpenses:    '年間経費は0以上の整数を入力してください。',
  loanAmount:        'ローン金額は0以上の整数を入力してください。',
  loanAmountExceed:  'ローン金額は物件価格以下にしてください。',
  interestRate:      '金利は0〜100の範囲で入力してください。',
  loanYears:         '借入年数は1〜50の整数を入力してください。',
  repairReserveRate: '修繕積立率は0〜1の範囲で入力してください。',
} as const

export const useInvestmentCalculator = () => {
  const input = ref<CalculatorInput>({})

  /** フィールドごとのバリデーションエラー（未入力フィールドは検証しない） */
  const errors = computed<Partial<Record<keyof InvestmentInput, string>>>(() => {
    const e: Partial<Record<keyof InvestmentInput, string>> = {}
    const v = input.value

    if (v.propertyPrice !== undefined) {
      if (!Number.isInteger(v.propertyPrice) || v.propertyPrice < 1)
        e.propertyPrice = MSG.propertyPrice
    }
    if (v.monthlyRent !== undefined) {
      if (!Number.isInteger(v.monthlyRent) || v.monthlyRent < 0)
        e.monthlyRent = MSG.monthlyRent
    }
    if (v.occupancyRate !== undefined) {
      if (v.occupancyRate < 0 || v.occupancyRate > 1)
        e.occupancyRate = MSG.occupancyRate
    }
    if (v.annualExpenses !== undefined) {
      if (!Number.isInteger(v.annualExpenses) || v.annualExpenses < 0)
        e.annualExpenses = MSG.annualExpenses
    }
    if (v.loanAmount !== undefined) {
      if (!Number.isInteger(v.loanAmount) || v.loanAmount < 0) {
        e.loanAmount = MSG.loanAmount
      } else if (v.propertyPrice !== undefined && v.loanAmount > v.propertyPrice) {
        e.loanAmount = MSG.loanAmountExceed
      }
    }
    if (v.interestRate !== undefined) {
      if (v.interestRate < 0 || v.interestRate > 100)
        e.interestRate = MSG.interestRate
    }
    if (v.loanYears !== undefined) {
      if (!Number.isInteger(v.loanYears) || v.loanYears < 1 || v.loanYears > 50)
        e.loanYears = MSG.loanYears
    }
    if (v.repairReserveRate !== undefined) {
      if (v.repairReserveRate < 0 || v.repairReserveRate > 1)
        e.repairReserveRate = MSG.repairReserveRate
    }

    return e
  })

  /** 全必須フィールドが入力済み かつ バリデーションエラーなし */
  const isReady = computed<boolean>(() => {
    const v = input.value
    return (
      v.propertyPrice !== undefined &&
      v.monthlyRent !== undefined &&
      v.occupancyRate !== undefined &&
      v.annualExpenses !== undefined &&
      v.loanAmount !== undefined &&
      v.interestRate !== undefined &&
      v.loanYears !== undefined &&
      v.repairReserveRate !== undefined &&
      Object.keys(errors.value).length === 0
    )
  })

  /** isReady が true のときのみ計算を実行。falseなら null を返す */
  const result = computed<InvestmentResult | null>(() => {
    if (!isReady.value) return null
    return calculateInvestment(input.value as InvestmentInput)
  })

  /**
   * リスク評価。isReady が true のときのみ算出。
   * buildYear が未入力の場合、修繕リスクは 'medium'（中間値）とする。
   */
  const risks = computed<{ vacancy: RiskLevel; repair: RiskLevel; loan: RiskLevel } | null>(() => {
    if (!isReady.value) return null
    const v = input.value as InvestmentInput & { buildYear?: number }
    return {
      vacancy: calcVacancyRisk(v.occupancyRate),
      repair:  v.buildYear !== undefined ? calcRepairRisk(v.buildYear) : 'medium',
      loan:    calcLoanRisk(v.loanAmount, v.propertyPrice),
    }
  })

  return { input, result, risks, isReady, errors }
}
