import { describe, it, expect } from 'vitest'
import { useInvestmentCalculator } from '../../composables/useInvestmentCalculator'
import { calculateLoanRepayment, calculateInvestment } from '@ai-real-estate/shared-utils'

/** 全フィールド入力済みの有効な基準値 */
const VALID_INPUT = {
  propertyPrice: 30_000_000,
  monthlyRent: 100_000,
  occupancyRate: 0.95,
  annualExpenses: 200_000,
  loanAmount: 24_000_000,
  interestRate: 2.0,
  loanYears: 35,
  repairReserveRate: 0.05,
}

describe('useInvestmentCalculator', () => {
  it('全フィールド未入力のとき isReady=false, result=null, risks=null', () => {
    const { isReady, result, risks } = useInvestmentCalculator()
    expect(isReady.value).toBe(false)
    expect(result.value).toBeNull()
    expect(risks.value).toBeNull()
  })

  it('全フィールド有効値のとき isReady=true, result/risks が返る', () => {
    const { input, isReady, result, risks } = useInvestmentCalculator()
    input.value = { ...VALID_INPUT }

    expect(isReady.value).toBe(true)
    expect(result.value).not.toBeNull()
    expect(risks.value).not.toBeNull()
  })

  it('物件価格が0のとき propertyPrice エラーが出る', () => {
    const { input, errors, isReady } = useInvestmentCalculator()
    input.value = { ...VALID_INPUT, propertyPrice: 0 }

    expect(errors.value.propertyPrice).toBeTruthy()
    expect(isReady.value).toBe(false)
  })

  it('loanAmount > propertyPrice のとき loanAmount エラーが出る', () => {
    const { input, errors } = useInvestmentCalculator()
    input.value = { ...VALID_INPUT, loanAmount: 40_000_000 }

    expect(errors.value.loanAmount).toMatch(/物件価格以下/)
  })

  it('interestRate=0 (無利子) のとき result が null でない', () => {
    const { input, result } = useInvestmentCalculator()
    input.value = { ...VALID_INPUT, interestRate: 0 }

    expect(result.value).not.toBeNull()
  })

  it('稼働率が 1.1 のとき occupancyRate エラーが出る', () => {
    const { input, errors } = useInvestmentCalculator()
    input.value = { ...VALID_INPUT, occupancyRate: 1.1 }

    expect(errors.value.occupancyRate).toBeTruthy()
  })

  it('loanYears=51 のとき loanYears エラーが出る', () => {
    const { input, errors } = useInvestmentCalculator()
    input.value = { ...VALID_INPUT, loanYears: 51 }

    expect(errors.value.loanYears).toBeTruthy()
  })

  it('buildYear 未指定のとき repair リスクは medium になる', () => {
    const { input, risks } = useInvestmentCalculator()
    input.value = { ...VALID_INPUT }  // buildYear なし

    expect(risks.value?.repair).toBe('medium')
  })
})

// ── shared-utils の計算ロジック回帰テスト ──────────────────────────────────

describe('calculateLoanRepayment', () => {
  it('interestRate=0 のとき年間返済額は loanAmount / loanYears になる（月次ではない）', () => {
    // 2400万円 / 35年 = 685,714.28…/年（修正前のバグは /420ヶ月 = 57,142.86 を返していた）
    const annual = calculateLoanRepayment(24_000_000, 0, 35)
    expect(annual).toBeCloseTo(24_000_000 / 35, 0)
    // バグ値 (57142) と正値 (685714) の中間より大きいことを確認
    expect(annual).toBeGreaterThan(100_000)
  })

  it('通常金利 (2%) での年間返済額が正常な範囲内に収まる', () => {
    const annual = calculateLoanRepayment(24_000_000, 2.0, 35)
    // 元本均等なら 685714/年、利子付きなので若干多い
    expect(annual).toBeGreaterThan(685_714)
    expect(annual).toBeLessThan(1_200_000)  // 極端に大きくならない
  })

  it('loanAmount=0 のとき 0 を返す', () => {
    expect(calculateLoanRepayment(0, 2.0, 35)).toBe(0)
  })
})

describe('calculateInvestment - annualCashflow と netYield の整合性', () => {
  it('annualCashflow = annualIncome - annualExpenses - annualRepairReserve', () => {
    const input = { ...VALID_INPUT }
    const r = calculateInvestment(input)

    // annualIncome = 100000 * 12 * 0.95 = 1,140,000
    const annualIncome = 100_000 * 12 * 0.95
    // repairReserve = annualIncome * 0.05 = 57,000
    const annualRepairReserve = annualIncome * 0.05
    const expectedNOI = annualIncome - 200_000 - annualRepairReserve

    expect(r.annualCashflow).toBeCloseTo(expectedNOI, 0)
  })

  it('netYield と annualCashflow が同じ費用ベースを使う', () => {
    const r = calculateInvestment(VALID_INPUT)

    // netYield = annualCashflow / propertyPrice * 100
    const expectedNetYield = (r.annualCashflow / VALID_INPUT.propertyPrice) * 100
    expect(r.netYield).toBeCloseTo(expectedNetYield, 5)
  })

  it('monthlyCashflow = annualCashflowAfterLoan / 12', () => {
    const r = calculateInvestment(VALID_INPUT)
    expect(r.monthlyCashflow).toBeCloseTo(r.annualCashflowAfterLoan / 12, 5)
  })
})
