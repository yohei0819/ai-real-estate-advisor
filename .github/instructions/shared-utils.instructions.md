# Shared Utils Instructions

## Scope
`packages/shared-utils`

## Rules
- 純粋関数中心で副作用を持たせない。
- 利回り/キャッシュフローなどの計算式は型を厳格にし、丸め方を明示する。
- 業務ルール変更時は型定義(`shared-types`)と同時更新する。
