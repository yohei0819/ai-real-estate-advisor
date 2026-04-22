import { describe, it, expect, vi } from 'vitest'
import { useInvestmentReport } from '../../composables/useInvestmentReport'
import type { AiReport } from '@ai-real-estate/shared-types'

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

const MOCK_REPORT: AiReport = {
  id: 'report-1',
  propertyId: 'prop-1',
  status: 'completed',
  summary: 'テストサマリー',
  riskScore: 4,
  recommendation: '購入を検討してください',
  createdAt: new Date().toISOString(),
  rationale: ['根拠1'],
  risks: ['リスク1'],
  actionItems: ['アクション1'],
  disclaimer: '本結果は参考情報です。',
}

describe('useInvestmentReport', () => {
  it('初期状態: report=null, isLoading=false, error=null', () => {
    const { report, isLoading, error } = useInvestmentReport()
    expect(report.value).toBeNull()
    expect(isLoading.value).toBe(false)
    expect(error.value).toBeNull()
  })

  it('fetchReport 成功: report にデータが格納され isLoading が false に戻る', async () => {
    globalThis.$fetch = vi.fn().mockResolvedValue(MOCK_REPORT)

    const { report, isLoading, error, fetchReport } = useInvestmentReport()
    await fetchReport(VALID_INPUT)

    expect(report.value).toEqual(MOCK_REPORT)
    expect(isLoading.value).toBe(false)
    expect(error.value).toBeNull()
  })

  it('fetchReport 失敗(429): error.code=429 が設定される', async () => {
    globalThis.$fetch = vi.fn().mockRejectedValue({ statusCode: 429 })

    const { error, fetchReport } = useInvestmentReport()
    await fetchReport(VALID_INPUT)

    expect(error.value?.code).toBe(429)
    expect(error.value?.message).toMatch(/利用上限/)
  })

  it('fetchReport 失敗(500): error.code=500 が設定される', async () => {
    globalThis.$fetch = vi.fn().mockRejectedValue({ statusCode: 500 })

    const { error, fetchReport } = useInvestmentReport()
    await fetchReport(VALID_INPUT)

    expect(error.value?.code).toBe(500)
    expect(error.value?.message).toBeTruthy()
  })
})
