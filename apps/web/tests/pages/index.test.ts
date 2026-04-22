import { describe, it, expect, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { ref } from 'vue'
import IndexPage from '../../pages/index.vue'

// NuxtLink スタブ
const globalStubs = {
  components: {
    NuxtLink: { template: '<a><slot /></a>' },
  },
}

function makeCalculatorMock(overrides: Record<string, unknown> = {}) {
  return {
    input: ref({}),
    result: ref(null),
    risks: ref(null),
    isReady: ref(false),
    errors: ref({}),
    ...overrides,
  }
}

function makeReportMock(overrides: Record<string, unknown> = {}) {
  return {
    report: ref(null),
    isLoading: ref(false),
    error: ref(null),
    fetchReport: vi.fn().mockResolvedValue(undefined),
    ...overrides,
  }
}

describe('pages/index.vue', () => {
  it('isReady=false のとき AI分析ボタンが disabled になる', () => {
    globalThis.useInvestmentCalculator = vi.fn().mockReturnValue(makeCalculatorMock({ isReady: ref(false) }))
    globalThis.useInvestmentReport = vi.fn().mockReturnValue(makeReportMock())

    const wrapper = mount(IndexPage, { global: globalStubs })
    const button = wrapper.find('button')
    expect(button.attributes('disabled')).toBeDefined()
  })

  it('isReady=true かつ isLoading=false のとき AI分析ボタンが有効になる', () => {
    const validResult = {
      grossYield: 4.0,
      netYield: 3.5,
      monthlyCashflow: 10000,
      roi: 8.0,
    }
    globalThis.useInvestmentCalculator = vi.fn().mockReturnValue(
      makeCalculatorMock({
        isReady: ref(true),
        result: ref(validResult),
        risks: ref({ vacancy: 'low', repair: 'medium', loan: 'low' }),
        input: ref({
          propertyPrice: 30_000_000,
          monthlyRent: 100_000,
          occupancyRate: 0.95,
          annualExpenses: 200_000,
          loanAmount: 24_000_000,
          interestRate: 2.0,
          loanYears: 35,
          repairReserveRate: 0.05,
        }),
      }),
    )
    globalThis.useInvestmentReport = vi.fn().mockReturnValue(makeReportMock())

    const wrapper = mount(IndexPage, { global: globalStubs })
    const button = wrapper.find('button')
    expect(button.attributes('disabled')).toBeUndefined()
  })

  it('isLoading=true のとき「AI分析中…」スピナーが表示される', () => {
    globalThis.useInvestmentCalculator = vi.fn().mockReturnValue(makeCalculatorMock({ isReady: ref(true) }))
    globalThis.useInvestmentReport = vi.fn().mockReturnValue(makeReportMock({ isLoading: ref(true) }))

    const wrapper = mount(IndexPage, { global: globalStubs })
    expect(wrapper.text()).toContain('AI分析中')
  })

  it('reportError があるとき エラーコードとメッセージが表示される', () => {
    globalThis.useInvestmentCalculator = vi.fn().mockReturnValue(makeCalculatorMock())
    globalThis.useInvestmentReport = vi.fn().mockReturnValue(
      makeReportMock({
        error: ref({ code: 429, message: 'AI分析の利用上限に達しました。しばらく後で再試行してください。' }),
      }),
    )

    const wrapper = mount(IndexPage, { global: globalStubs })
    expect(wrapper.text()).toContain('429')
    expect(wrapper.text()).toContain('利用上限')
  })
})
