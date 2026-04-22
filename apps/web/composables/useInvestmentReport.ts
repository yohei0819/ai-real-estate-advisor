import type { AiReport, InvestmentInput } from '@ai-real-estate/shared-types'

const ERROR_MESSAGES: Record<number, string> = {
  429: 'AI分析の利用上限に達しました。しばらく後で再試行してください。',
  422: '入力値に誤りがあります。再確認してください。',
}
const SERVER_ERROR_MESSAGE = 'AI分析サービスが一時的に利用できません。'

export const useInvestmentReport = () => {
  const config = useRuntimeConfig()

  // useState でページ間共有（index → report へのナビゲーション後も値が残る）
  const report = useState<AiReport | null>('investment-report', () => null)
  const isLoading = useState<boolean>('investment-report-loading', () => false)
  const error = useState<{ code: number; message: string } | null>('investment-report-error', () => null)

  /**
   * バックエンド経由で AI レポートを取得する。
   * Gemini API は直接呼ばず、Laravel API サーバーに POST する。
   */
  const fetchReport = async (investmentInput: InvestmentInput): Promise<void> => {
    isLoading.value = true
    error.value = null
    report.value = null

    try {
      const data = await $fetch<AiReport>(
        `${config.public.apiBase}/api/v1/investment/report`,
        {
          method: 'POST',
          body: investmentInput,
        },
      )
      report.value = data
    } catch (err: unknown) {
      const code = resolveStatusCode(err)
      const message = ERROR_MESSAGES[code] ?? SERVER_ERROR_MESSAGE
      error.value = { code, message }
    } finally {
      isLoading.value = false
    }
  }

  return { report, isLoading, error, fetchReport }
}

/** $fetch が throw する FetchError から HTTP ステータスコードを取り出す */
function resolveStatusCode(err: unknown): number {
  if (err !== null && typeof err === 'object' && 'statusCode' in err) {
    const code = (err as { statusCode: unknown }).statusCode
    if (typeof code === 'number') return code
  }
  return 500
}
