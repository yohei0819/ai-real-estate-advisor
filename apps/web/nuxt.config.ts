// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  modules: ['@nuxtjs/tailwindcss'],

  runtimeConfig: {
    public: {
      // ブラウザ側に公開するのは API ベース URL のみ。Gemini API キーは含めない。
      apiBase: process.env['NUXT_PUBLIC_API_BASE'] ?? 'http://localhost:8000',
    },
  },

  typescript: {
    strict: true,
    typeCheck: true,
  },

  compatibilityDate: '2025-04-21',
})
