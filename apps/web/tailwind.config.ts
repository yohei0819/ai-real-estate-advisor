import { createRequire } from 'node:module'
import type { Config } from 'tailwindcss'
// packages/ui/tailwind-preset.cjs をプリセットとして使用（colors.json が SSoT）
const require = createRequire(import.meta.url)
const uiPreset = require('@ai-real-estate/ui/tailwind-preset.cjs')

export default {
  presets: [uiPreset],
  content: [
    './app/**/*.{vue,ts}',
    './pages/**/*.{vue,ts}',
    './components/**/*.{vue,ts}',
    './composables/**/*.ts',
    './layouts/**/*.vue',
  ],
} satisfies Config
