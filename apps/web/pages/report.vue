<template>
  <div class="min-h-screen bg-gray-50">
    <header class="bg-white border-b border-gray-200">
      <div class="max-w-3xl mx-auto px-4 py-4 flex items-center gap-3">
        <NuxtLink to="/" class="text-sm text-blue-600 hover:underline">← 入力に戻る</NuxtLink>
        <h1 class="text-xl font-bold text-gray-900">AI分析レポート</h1>
      </div>
    </header>

    <main class="max-w-3xl mx-auto px-4 py-8 space-y-6">

      <!-- ローディング状態: スケルトンUI -->
      <template v-if="isLoading">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4 animate-pulse">
          <div class="h-6 bg-gray-200 rounded w-1/3" />
          <div class="h-24 bg-gray-200 rounded" />
          <div class="h-4 bg-gray-200 rounded w-2/3" />
          <div class="h-4 bg-gray-200 rounded w-1/2" />
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-3 animate-pulse">
          <div class="h-5 bg-gray-200 rounded w-1/4" />
          <div v-for="i in 3" :key="i" class="h-4 bg-gray-200 rounded" />
        </div>
        <p class="text-center text-sm text-gray-400">AIが分析中です。しばらくお待ちください…</p>
      </template>

      <!-- エラー状態 -->
      <template v-else-if="error">
        <div
          role="alert"
          class="bg-white rounded-xl shadow-sm border border-red-200 p-6 text-center space-y-3"
        >
          <p class="text-4xl">⚠️</p>
          <p class="font-semibold text-risk">エラーが発生しました（コード: {{ error.code }}）</p>
          <p class="text-sm text-gray-600">{{ error.message }}</p>
          <NuxtLink
            to="/"
            class="inline-block mt-2 px-5 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700"
          >
            入力画面に戻って再試行する
          </NuxtLink>
        </div>
      </template>

      <!-- 空状態 -->
      <template v-else-if="!report">
        <div class="bg-white rounded-xl shadow-sm border border-dashed border-gray-300 p-10 flex flex-col items-center text-center space-y-3">
          <p class="text-5xl">📄</p>
          <p class="text-gray-500">まだレポートがありません。</p>
          <NuxtLink
            to="/"
            class="inline-block px-5 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700"
          >
            入力画面でシミュレーションを実行する
          </NuxtLink>
        </div>
      </template>

      <!-- レポート表示 -->
      <template v-else>

        <!-- リスクスコア -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 text-center">
          <p class="text-sm text-gray-500 mb-1">リスクスコア（1=低リスク〜10=高リスク）</p>
          <p class="text-7xl font-bold" :class="scoreInfo.textClass">{{ report.riskScore }}</p>
          <p class="mt-2 text-lg font-semibold" :class="scoreInfo.textClass">
            {{ scoreInfo.icon }} {{ scoreInfo.label }}
          </p>
          <p class="mt-1 text-xs text-gray-400">{{ scoreInfo.description }}</p>
        </div>

        <!-- サマリー -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <h2 class="text-base font-semibold text-gray-800 mb-3">総合所見</h2>
          <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ report.summary }}</p>
        </div>

        <!-- 判断根拠 (rationale) -->
        <div v-if="report.rationale.length > 0" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <h2 class="text-base font-semibold text-gray-800 mb-3">判断根拠</h2>
          <ul class="space-y-2">
            <li
              v-for="(item, i) in report.rationale"
              :key="i"
              class="flex gap-2 text-sm text-gray-700"
            >
              <span class="text-blue-400 mt-0.5 shrink-0">▶</span>
              <span>{{ item }}</span>
            </li>
          </ul>
        </div>

        <!-- リスク一覧 -->
        <div v-if="report.risks.length > 0" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <h2 class="text-base font-semibold text-gray-800 mb-3">リスク項目</h2>
          <ul class="space-y-2">
            <li
              v-for="(item, i) in report.risks"
              :key="i"
              class="flex gap-2 text-sm text-risk"
            >
              <span class="shrink-0 font-bold">■</span>
              <span>{{ item }}</span>
            </li>
          </ul>
        </div>

        <!-- 推奨アクション -->
        <div v-if="report.actionItems.length > 0" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <h2 class="text-base font-semibold text-gray-800 mb-3">推奨アクション</h2>
          <ul class="space-y-2">
            <li
              v-for="(item, i) in report.actionItems"
              :key="i"
              class="flex gap-2 text-sm text-gray-700"
            >
              <span class="text-positive shrink-0 font-bold">✓</span>
              <span>{{ item }}</span>
            </li>
          </ul>
        </div>

        <!-- 推奨事項 (recommendation) -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <h2 class="text-base font-semibold text-gray-800 mb-3">投資判断の推奨</h2>
          <p class="text-sm text-gray-700 leading-relaxed">{{ report.recommendation }}</p>
        </div>

        <!-- 免責事項 -->
        <div class="rounded-lg bg-gray-50 border border-gray-200 p-4 text-xs text-gray-400 space-y-1">
          <p>{{ report.disclaimer }}</p>
          <p>※ 本レポートはAIが生成した参考情報です。投資判断は必ず専門家（不動産会社・ファイナンシャルプランナー等）にご相談ください。</p>
          <p>生成日時: {{ new Date(report.createdAt).toLocaleString('ja-JP') }}</p>
        </div>

      </template>

    </main>
  </div>
</template>

<script setup lang="ts">
const { report, isLoading, error } = useInvestmentReport()

// riskScore: 1〜10（1=最低リスク, 10=最高リスク）に基づく色・文言
// v-else（report が非null）の中でのみ使われるため optional chaining は不要
const scoreInfo = computed(() => {
  const s = report.value!.riskScore
  if (s <= 3) return { textClass: 'text-positive', icon: '●', label: '低リスク', description: '安定した投資と判断されました' }
  if (s <= 6) return { textClass: 'text-warning',  icon: '▲', label: '中リスク', description: '一部リスク要因が存在します' }
  return           { textClass: 'text-risk',     icon: '■', label: '高リスク', description: '高リスク要因が複数確認されています' }
})
</script>
