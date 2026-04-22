<template>
  <div class="min-h-screen bg-gray-50">
    <header class="bg-white border-b border-gray-200">
      <div class="max-w-5xl mx-auto px-4 py-4">
        <h1 class="text-xl font-bold text-gray-900">AI不動産投資シミュレーター</h1>
      </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 py-8">
      <div class="lg:grid lg:grid-cols-2 lg:gap-8 space-y-6 lg:space-y-0">

        <!-- 左列: 入力フォーム -->
        <section class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <h2 class="text-lg font-semibold text-gray-800 mb-5">物件・ローン情報の入力</h2>

          <div class="space-y-4">
            <!-- 物件価格 -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                物件価格 <span class="text-gray-400 font-normal">（万円）</span>
              </label>
              <input
                type="number"
                min="1"
                step="1"
                placeholder="例: 3000"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                :value="form.propertyPriceMan ?? ''"
                @input="handleNumInput($event, v => { form.propertyPriceMan = v })"
              />
              <p v-if="errors.propertyPrice" class="mt-1 text-xs text-risk">{{ errors.propertyPrice }}</p>
            </div>

            <!-- 月額賃料 -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                月額賃料 <span class="text-gray-400 font-normal">（円/月）</span>
              </label>
              <input
                type="number"
                min="0"
                step="1"
                placeholder="例: 100000"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                :value="form.monthlyRent ?? ''"
                @input="handleNumInput($event, v => { form.monthlyRent = v })"
              />
              <p v-if="errors.monthlyRent" class="mt-1 text-xs text-risk">{{ errors.monthlyRent }}</p>
            </div>

            <!-- 稼働率 -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                稼働率
                <span class="ml-2 font-semibold text-blue-600">{{ form.occupancyRatePercent }}%</span>
              </label>
              <input
                type="range"
                min="0"
                max="100"
                step="1"
                class="w-full accent-blue-500"
                :value="form.occupancyRatePercent"
                @input="handleRangeInput($event, v => { form.occupancyRatePercent = v })"
              />
              <div class="flex justify-between text-xs text-gray-400 mt-0.5">
                <span>0%</span><span>50%</span><span>100%</span>
              </div>
            </div>

            <!-- 年間経費 -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                年間経費 <span class="text-gray-400 font-normal">（円/年）</span>
              </label>
              <input
                type="number"
                min="0"
                step="1"
                placeholder="例: 200000"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                :value="form.annualExpenses ?? ''"
                @input="handleNumInput($event, v => { form.annualExpenses = v })"
              />
              <p v-if="errors.annualExpenses" class="mt-1 text-xs text-risk">{{ errors.annualExpenses }}</p>
            </div>

            <hr class="border-gray-100" />

            <!-- 借入金額 -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                借入金額 <span class="text-gray-400 font-normal">（万円）</span>
              </label>
              <input
                type="number"
                min="0"
                step="1"
                placeholder="例: 2400"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                :value="form.loanAmountMan ?? ''"
                @input="handleNumInput($event, v => { form.loanAmountMan = v })"
              />
              <p v-if="errors.loanAmount" class="mt-1 text-xs text-risk">{{ errors.loanAmount }}</p>
            </div>

            <!-- 年利 -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                年利 <span class="text-gray-400 font-normal">（%）</span>
              </label>
              <input
                type="number"
                min="0"
                max="100"
                step="0.1"
                placeholder="例: 2.0"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                :value="form.interestRate ?? ''"
                @input="handleNumInput($event, v => { form.interestRate = v })"
              />
              <p v-if="errors.interestRate" class="mt-1 text-xs text-risk">{{ errors.interestRate }}</p>
            </div>

            <!-- 借入年数 -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                借入年数 <span class="text-gray-400 font-normal">（年）</span>
              </label>
              <input
                type="number"
                min="1"
                max="50"
                step="1"
                placeholder="例: 35"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                :value="form.loanYears ?? ''"
                @input="handleNumInput($event, v => { form.loanYears = v })"
              />
              <p v-if="errors.loanYears" class="mt-1 text-xs text-risk">{{ errors.loanYears }}</p>
            </div>

            <!-- 修繕積立率 -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                修繕積立率 <span class="text-gray-400 font-normal">（%）</span>
              </label>
              <input
                type="number"
                min="0"
                max="100"
                step="0.1"
                placeholder="例: 5.0"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                :value="form.repairReserveRatePercent"
                @input="handleRangeInput($event, v => { form.repairReserveRatePercent = v })"
              />
              <p v-if="errors.repairReserveRate" class="mt-1 text-xs text-risk">{{ errors.repairReserveRate }}</p>
            </div>
          </div>
        </section>

        <!-- 右列: リアルタイム計算プレビュー -->
        <section class="flex flex-col gap-5">

          <!-- 計算結果 -->
          <div v-if="displayResult" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">シミュレーション結果</h2>

            <dl class="space-y-3">
              <div class="flex justify-between items-center">
                <dt class="text-sm text-gray-600">表面利回り</dt>
                <dd class="font-semibold text-gray-900">{{ displayResult.grossYield }}%</dd>
              </div>
              <div class="flex justify-between items-center">
                <dt class="text-sm text-gray-600">実質利回り（修繕費込み）</dt>
                <dd class="font-semibold text-gray-900">{{ displayResult.netYield }}%</dd>
              </div>
              <div class="flex justify-between items-center">
                <dt class="text-sm text-gray-600">月次CF（ローン返済後）</dt>
                <dd
                  class="font-semibold"
                  :class="displayResult.isMonthlyCashflowPositive ? 'text-positive' : 'text-risk'"
                >
                  {{ displayResult.isMonthlyCashflowPositive ? '+' : '' }}{{ displayResult.monthlyCashflow }}円/月
                </dd>
              </div>
              <div class="flex justify-between items-center">
                <dt class="text-sm text-gray-600">ROI（自己資金利回り）</dt>
                <dd class="font-semibold text-gray-900">
                  {{ displayResult.roi !== null ? displayResult.roi + '%' : 'N/A' }}
                </dd>
              </div>
            </dl>

            <hr class="my-4 border-gray-100" />

            <!-- リスク判定 (色+文言) -->
            <h3 class="text-sm font-semibold text-gray-700 mb-2">リスク判定</h3>
            <div class="space-y-2">
              <div v-if="risks" class="space-y-2">
                <div class="flex justify-between items-center">
                  <span class="text-sm text-gray-600">空室リスク</span>
                  <span class="text-sm font-medium" :class="RISK_DISPLAY[risks.vacancy].textClass">
                    {{ RISK_DISPLAY[risks.vacancy].icon }} {{ RISK_DISPLAY[risks.vacancy].label }}
                  </span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-sm text-gray-600">修繕リスク</span>
                  <span class="text-sm font-medium" :class="RISK_DISPLAY[risks.repair].textClass">
                    {{ RISK_DISPLAY[risks.repair].icon }} {{ RISK_DISPLAY[risks.repair].label }}
                  </span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-sm text-gray-600">ローンリスク（LTV）</span>
                  <span class="text-sm font-medium" :class="RISK_DISPLAY[risks.loan].textClass">
                    {{ RISK_DISPLAY[risks.loan].icon }} {{ RISK_DISPLAY[risks.loan].label }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- 空状態: フォーム未入力 -->
          <div
            v-else
            class="bg-white rounded-xl shadow-sm border border-dashed border-gray-300 p-8 flex flex-col items-center justify-center text-center"
          >
            <div class="text-4xl mb-3">📊</div>
            <p class="text-sm text-gray-500">必須項目をすべて入力すると<br />リアルタイムで計算結果が表示されます</p>
          </div>

          <!-- AI分析ボタン -->
          <button
            type="button"
            :disabled="!isReady || isLoading"
            class="w-full py-3 px-6 rounded-xl font-semibold text-white transition-colors"
            :class="isReady && !isLoading
              ? 'bg-blue-600 hover:bg-blue-700 cursor-pointer'
              : 'bg-gray-300 cursor-not-allowed'"
            @click="onAnalyze"
          >
            <span v-if="isLoading" class="flex items-center justify-center gap-2">
              <!-- スピナー -->
              <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
              </svg>
              AI分析中…
            </span>
            <span v-else>AI分析レポートを生成する</span>
          </button>

          <!-- APIエラー表示 -->
          <div
            v-if="reportError"
            role="alert"
            class="rounded-lg bg-red-50 border border-red-200 p-4 text-sm text-risk"
          >
            <p class="font-semibold">エラーが発生しました（コード: {{ reportError.code }}）</p>
            <p class="mt-1">{{ reportError.message }}</p>
          </div>

        </section>
      </div>

      <!-- 注意文言 -->
      <p class="mt-8 text-center text-xs text-gray-400">
        ※ 本シミュレーション結果は参考情報です。投資判断は専門家にご相談ください。
      </p>
    </main>
  </div>
</template>

<script setup lang="ts">
import type { InvestmentInput } from '@ai-real-estate/shared-types'

const { input, result, risks, isReady, errors } = useInvestmentCalculator()
const { fetchReport, isLoading, error: reportError } = useInvestmentReport()
const { RISK_DISPLAY } = useRiskDisplay()

// フォーム表示値（単位変換あり: 万円入力 → 円、%入力 → 小数）
const form = reactive({
  propertyPriceMan: undefined as number | undefined,
  monthlyRent: undefined as number | undefined,
  occupancyRatePercent: 95,
  annualExpenses: undefined as number | undefined,
  loanAmountMan: undefined as number | undefined,
  interestRate: undefined as number | undefined,
  loanYears: undefined as number | undefined,
  repairReserveRatePercent: 5,
})

// フォーム表示値 → InvestmentCalculator input へ同期
watchEffect(() => {
  input.value = {
    propertyPrice: form.propertyPriceMan !== undefined ? form.propertyPriceMan * 10000 : undefined,
    monthlyRent: form.monthlyRent,
    occupancyRate: form.occupancyRatePercent / 100,
    annualExpenses: form.annualExpenses,
    loanAmount: form.loanAmountMan !== undefined ? form.loanAmountMan * 10000 : undefined,
    interestRate: form.interestRate,
    loanYears: form.loanYears,
    repairReserveRate: form.repairReserveRatePercent / 100,
  }
})

/** number input の @input ハンドラー（空欄 → undefined、NaN → undefined） */
const handleNumInput = (e: Event, setter: (v: number | undefined) => void) => {
  const n = (e.target as HTMLInputElement).valueAsNumber
  setter(Number.isNaN(n) ? undefined : n)
}

/** range/number input の @input ハンドラー（0 を許容） */
const handleRangeInput = (e: Event, setter: (v: number) => void) => {
  const n = (e.target as HTMLInputElement).valueAsNumber
  if (!Number.isNaN(n)) setter(n)
}

// 表示用フォーマット済み計算結果
const displayResult = computed(() => {
  if (!result.value) return null
  const r = result.value
  const v = input.value
  // equity = 0（100% LTV）のとき ROI はゼロ除算回避で 0 を返すが、それは誤誘導なので N/A 表示
  const equity = (v.propertyPrice ?? 0) - (v.loanAmount ?? 0)
  return {
    grossYield: r.grossYield.toFixed(2),
    netYield: r.netYield.toFixed(2),
    monthlyCashflow: Math.round(r.monthlyCashflow).toLocaleString('ja-JP'),
    roi: equity > 0 ? r.roi.toFixed(2) : null,
    isMonthlyCashflowPositive: r.monthlyCashflow >= 0,
  }
})

// InvestmentInput として完全な入力値を返す（型安全なキャスト）
const completeInput = computed<InvestmentInput | null>(() => {
  if (!isReady.value) return null
  const v = input.value
  if (
    v.propertyPrice === undefined || v.monthlyRent === undefined ||
    v.occupancyRate === undefined || v.annualExpenses === undefined ||
    v.loanAmount === undefined || v.interestRate === undefined ||
    v.loanYears === undefined || v.repairReserveRate === undefined
  ) return null
  return {
    propertyPrice: v.propertyPrice,
    monthlyRent: v.monthlyRent,
    occupancyRate: v.occupancyRate,
    annualExpenses: v.annualExpenses,
    loanAmount: v.loanAmount,
    interestRate: v.interestRate,
    loanYears: v.loanYears,
    repairReserveRate: v.repairReserveRate,
  }
})

const onAnalyze = async () => {
  if (!completeInput.value) return
  await fetchReport(completeInput.value)
  if (!reportError.value) {
    await navigateTo('/report')
  }
}
</script>
