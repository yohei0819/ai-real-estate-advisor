# Tests Instructions

- AIテストはGeminiClientをモックし、外部通信を禁止する。
- Prompt/Schemaの差分はスナップショットまたは構造テストで検知する。
- 金額計算は境界値（0, 負数, 大規模値）を含める。
