# Phase 4: API整合・管理画面・CI整備

## 前提条件

`pnpm test`（`apps/web`）と `php artisan test`（`apps/api`）が全グリーンであること。
未通過のテストは `.github/prompts/fix-failing-test.prompt.md` に従って先に修正してください。

---

## タスク一覧

### T1: SchemaRegistry と AiReport 型の整合 【#agent:api-builder】

**参照:** `.github/instructions/laravel.instructions.md`, `.github/instructions/ai-prompts.instructions.md`

`apps/api/app/Services/Ai/SchemaRegistry.php` の `reportSchema()` が返すスキーマと
`packages/shared-types/src/ai-report.ts` の `AiReport` インターフェースが乖離しています。

**現状の問題:**

| SchemaRegistry (Gemini出力) | AiReport (フロント期待) | 状態 |
|---|---|---|
| `score` (integer) | `riskScore` (number) | キー名不一致 |
| なし | `summary` (string) | Geminiが返さない |
| なし | `recommendation` (string) | Geminiが返さない |
| なし | `id`, `propertyId`, `status`, `createdAt` | サービス層で付与が必要 |

**実施内容:**

1. `SchemaRegistry::reportSchema()` を以下に更新する:
   - `score` → `riskScore` にリネーム
   - `summary` (string, required) を追加
   - `recommendation` (string, required) を追加
   - `required` 配列も同期して更新する

2. `AiReportService::buildReport()` の戻り値を `AiReport` 全フィールドを満たす形に整形する:
   - Geminiレスポンスをそのまま返すのではなく、`id`（UUID）、`propertyId`（空文字またはnull許容）、`status: 'completed'`、`createdAt`（ISO 8601）を付与する
   - `score` → `riskScore` のマッピングを確実に行う

3. `PromptRegistry` の `investment_report` テンプレートに `summary` と `recommendation` の生成を指示する文言を追加する。
   - 「総合所見（summary）」と「投資判断の推奨（recommendation）」を必ず含めるよう指示を追記する

4. `.github/prompts/create-unit-test.prompt.md` に従い、`SchemaRegistry` の構造テストを `tests/Unit/SchemaRegistryTest.php` として追加する:
   - `reportSchema()` が `riskScore`, `summary`, `recommendation`, `rationale`, `risks`, `actionItems`, `disclaimer` をすべて `required` または `properties` に持つことをアサートする

---

### T2: Controller レスポンス形式の修正 【#agent:api-builder】

**参照:** `.github/instructions/laravel.instructions.md`

`InvestmentReportController` が `{ data: {...}, meta: {...} }` でラップして返しているため、
`apps/web/composables/useInvestmentReport.ts` の `$fetch<AiReport>(...)` と形式が不一致です。

**現状:**
```php
return response()->json([
    'data' => $report,
    'meta' => ['generated_at' => now()->toIso8601String()],
]);
```

**実施内容:**

`AiReportService::buildReport()` が `generated_at` を `createdAt` として `AiReport` 内に含めるように変更し（T1で実施済み）、
Controller はラッパーなしでフラットに返す:

```php
return response()->json($report);
```

`tests/Feature/InvestmentReportControllerTest.php` のレスポンス構造アサーションを合わせて更新する:
- `response->assertJsonStructure(['riskScore', 'summary', 'recommendation', 'rationale', 'risks', 'actionItems', 'disclaimer', 'createdAt'])` に変更する

---

### T3: AiReportService の monthlyCashflow 一貫性修正 【#agent:calculator + #agent:api-builder】

**参照:** `.github/instructions/shared-utils.instructions.md`, `.github/instructions/laravel.instructions.md`

`AiReportService::buildReport()` の `$monthlyCashflow` が修繕積立費を控除していないため、
`packages/shared-utils/src/investment.ts` の `calculateInvestment()` と値が異なります。
AIプロンプトへ渡す数値が画面表示値と乖離するため修正が必要です。

**現状:**
```php
$monthlyCashflow = round(($annualIncome - (int) $input['annual_expenses']) / 12);
```

**修正後:**
```php
$annualNoi = $annualIncome - (int) $input['annual_expenses'] - $annualRepairReserve;
$monthlyCashflow = round($annualNoi / 12);
```

`#agent:calculator` は `packages/shared-utils/src/investment.ts` の `annualCashflow` 定義と
PHP側の `$annualNoi` 計算が同一式（`annualIncome − annualExpenses − annualRepairReserve`）であることを確認し、
差異があれば報告してください。

---

### T4: apps/admin — Next.js 管理画面の初期実装 【#agent:api-builder】

**参照:** `.github/instructions/nextjs.instructions.md`

`apps/admin` に AI利用量・レート制御状態を可視化する管理画面を作成します。
`.github/prompts/create-vue-page.prompt.md` の制約（ローディング/エラー/空状態）を
Next.js App Router の Server Component に読み替えて適用してください。

**ディレクトリ構成:**
```
apps/admin/
  app/
    layout.tsx          # 共通レイアウト（ヘッダー・サイドバー）
    page.tsx            # ダッシュボード（AI利用サマリー）
    reports/
      page.tsx          # ai_reports テーブルの一覧（直近100件）
  package.json          # next, react, react-dom, typescript
  next.config.ts
  tsconfig.json
```

**実施内容:**

1. `apps/admin/package.json` を作成する（`next@15`, `react@19`, `react-dom@19`, `typescript@5`）
2. `apps/admin/app/page.tsx` (Server Component) を作成する:
   - `apps/api` の内部エンドポイント `GET /api/v1/admin/stats` を `fetch()` で呼ぶ（SSR）
   - AIレポート総件数・直近24時間件数・レートリミット残数を表示する
   - ローディング: `loading.tsx` で Suspense フォールバックを表示する
   - エラー: `error.tsx` でエラー境界を実装する
   - 空状態: 件数0のとき「まだレポートが生成されていません」を表示する
3. `apps/admin/app/reports/page.tsx` を作成する:
   - `ai_reports` の直近100件（`created_at` DESC）を表形式で表示する
   - 表示カラム: `id`, `model`, `status`, `created_at`（機密値・APIキー・生PIIは表示しない）
4. 対応する Laravel エンドポイント `GET /api/v1/admin/stats` を `.github/prompts/create-api-endpoint.prompt.md` に従い追加する:
   - DB集計のみ（Gemini呼び出しなし）
   - `ai_reports` テーブルから `total`, `last_24h`, `rate_limit_remaining` を返す
   - RateLimiter の残数は `RateLimiter::remaining('investment_report')` で取得する
5. `turbo.json` の `pipeline` に `admin#build` と `admin#dev` を追加する

---

### T5: CI パイプライン構築 【#agent:reviewer】

**参照:** `.github/instructions/ci.instructions.md`

`.github/workflows/ci.yml` を新規作成してください。

**要件:**

```yaml
# トリガー: push / pull_request (main ブランチ)
# ジョブ分割: api | web | admin | packages
# 各ジョブの必須ステップ:
#   api:      composer install → php artisan test → 静的解析(larastan)
#   web:      pnpm install → pnpm -F @ai-real-estate/web test → pnpm -F @ai-real-estate/web build
#   admin:    pnpm install → pnpm -F @ai-real-estate/admin build
#   packages: pnpm install → pnpm -F @ai-real-estate/shared-utils test
```

**制約 (`.github/instructions/ci.instructions.md` より):**
- `GEMINI_API_KEY` は GitHub Secrets から注入し、`echo` や `run` コマンドのログに出力しない
- AI関連変更（`apps/api/app/Services/Ai/**` 変更時）は静的解析 + ユニットテスト + セキュリティスキャン（`composer audit`）を追加で実行する
- `packages/*` 変更時はすべての downstream アプリのビルドを再実行する

---

### T6: 統合テスト追加 【#agent:reviewer + #agent:api-builder】

**参照:** `.github/instructions/tests.instructions.md`, `.github/prompts/create-unit-test.prompt.md`

T1〜T2 の修正後、以下のテストが全グリーンであることを確認・追加してください。

**Laravel (`php artisan test`):**
- `InvestmentReportControllerTest`: レスポンスが `riskScore`, `summary`, `recommendation`, `createdAt` をトップレベルに持つ
- `AiReportServiceTest`: `monthlyCashflow` の計算値が `(annualIncome - annualExpenses - annualRepairReserve) / 12` と一致する
- `SchemaRegistryTest`: `reportSchema()` が `riskScore`, `summary`, `recommendation` を含む（T1で追加）

**Nuxt (`pnpm test` in `apps/web`):**
- `useInvestmentReport.test.ts`: `$fetch` モックが `AiReport` 形式（フラット・ラッパーなし）を返したとき `report.value` に正しくセットされる（既存テストがT2修正後も通ることを確認）

---

## 完了条件

- [ ] `php artisan test` 全グリーン
- [ ] `pnpm test`（`apps/web`）全グリーン
- [ ] `apps/admin` が `pnpm -F @ai-real-estate/admin build` でエラーなくビルドできる
- [ ] `.github/workflows/ci.yml` が構文エラーなし（`actionlint` または `gh act` で確認）
- [ ] `#agent:reviewer` が全チェック項目（GeminiClient経由原則・Registry集中管理・レート制御・モックテスト方針）を承認した

---

## エージェント割り当てサマリー

| タスク | 主担当 | 副担当 |
|---|---|---|
| T1: SchemaRegistry整合 | `#agent:api-builder` | — |
| T2: Controllerレスポンス修正 | `#agent:api-builder` | — |
| T3: monthlyCashflow一貫性 | `#agent:calculator` | `#agent:api-builder` |
| T4: apps/admin 初期実装 | `#agent:api-builder` | — |
| T5: CI パイプライン | `#agent:reviewer` | — |
| T6: 統合テスト追加 | `#agent:reviewer` | `#agent:api-builder` |
