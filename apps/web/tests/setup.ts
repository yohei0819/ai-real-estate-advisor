/**
 * Nuxt auto-imports are not available in Vitest.
 * Polyfill the globals that composables and pages rely on.
 */
import { ref, computed, reactive, watchEffect, nextTick } from 'vue'
import { vi } from 'vitest'
import {
  useInvestmentCalculator,
} from '../composables/useInvestmentCalculator'
import { useInvestmentReport } from '../composables/useInvestmentReport'
import { useRiskDisplay } from '../composables/useRiskDisplay'

// Vue reactivity primitives
globalThis.ref = ref
globalThis.computed = computed
globalThis.reactive = reactive
globalThis.watchEffect = watchEffect
globalThis.nextTick = nextTick

// useState: key-based SSR-safe shared state in Nuxt.
// In tests, create a fresh ref per call (no cross-test sharing).
globalThis.useState = <T>(_key: string, init?: () => T) => ref<T>(init ? init() : (undefined as T))

// useRuntimeConfig: returns config with public.apiBase
globalThis.useRuntimeConfig = () => ({
  public: { apiBase: 'http://localhost:8000' },
})

// $fetch: must be mocked per-test; default noop
globalThis.$fetch = vi.fn()

// navigateTo: spy to inspect navigation calls
globalThis.navigateTo = vi.fn()

// Nuxt auto-import composables
globalThis.useInvestmentCalculator = useInvestmentCalculator
globalThis.useInvestmentReport = useInvestmentReport
globalThis.useRiskDisplay = useRiskDisplay

// Reset mocks before each test
beforeEach(() => {
  vi.resetAllMocks()
  globalThis.$fetch = vi.fn()
  globalThis.navigateTo = vi.fn()
})
