# ai-real-estate-advisor

AI不動産投資シミュレーター（Gemini API連携）

## 初期化直後の最適な作業開始・進行ステップ（Gemini専用 / モノレポ想定）

1. **Issue #1「Monorepo土台構築」から開始**  
   Turborepo構成で `apps/web-nuxt`（Nuxt）, `apps/web-next`（Next）, `apps/api`（Laravel）, `packages/*`（TypeScript共通）を作成し、OpenAI/Anthropic関連は入れずGemini専用方針をIssue本文に明記する。
2. **Issue #2「開発規範の自動適用」**  
   `copilot-instructions.md` と `.github/instructions/*.md` を整備し、**全mdファイルの規範が自動適用される運用**（命名・レビュー観点・セキュリティ観点）を確立する。
3. **Issue #3「Gemini AI基盤実装」→ PR #1**  
   `packages/ai-prompts` に Gemini Provider / prompt builder / response schema を実装。`GEMINI_API_KEY` のみ必須にし、モデルは `gemini-2.5-flash` 系をデフォルトにする。
4. **Issue #4「レート制限・クォータ管理」→ PR #2**  
   15 RPM / 1,500 RPD / 1,000,000 TPD を前提に、API側でガード・429時バックオフ・利用量ログ・残量APIを追加する。
5. **Issue #5「不動産投資ロジックMVP」→ PR #3**  
   収益性（表面/実質利回り）、空室/修繕/金利リスク、将来性スコアをTypeScriptで純関数化し、Nuxt/Next両UIから同一ロジックを利用可能にする。
6. **Issue #6「AI instructions適用の実運用」**  
   プロンプトは system/user 分離、JSON出力は responseSchema 前提、推測禁止・根拠必須をmd規範に固定。ここでCopilot Coding Agentに「規範準拠チェック」を任せる。
7. **Issue #7「テスト基盤」→ PR #4**  
   単体（TypeScriptロジック）, API（Laravel Feature）, E2E（主要導線）を追加。**AI利用タイミングは、テストケース草案作成・境界値洗い出し時**に限定し、最終期待値は人間が確定する。
8. **Issue #8「PR/Issue運用フロー定義」**  
   Issueテンプレート（目的・受け入れ条件・非対象）とPRテンプレート（変更点・検証・リスク）を整備。1 Issue = 1 PRを基本に、小さく出して早くレビューする。
9. **Copilot Coding Agent / VS Code Agent Mode の役割分担で実装加速**  
   人間: 要件決定・最終承認・リスク判断。Agent: 反復実装・テスト下書き・ドキュメント同期。VS Code Agent Modeは「Issue単位の実装→ローカル検証→PR更新」の反復に使う。
10. **CI/CD・レビュー・マージ**  
    PRごとに lint/test/build/security を必須化し、main保護ルールで通過後にマージ。デプロイ後は運用Issueへフィードバックし、**issue/PRのサイクルを回しながら拡張**する。

## 推奨する最初のIssue/PR例

- Issue #1: Monorepo scaffold（Nuxt/Next/Laravel/TS）
- Issue #3: Gemini Provider + schema出力
- Issue #4: Gemini rate limit guard
- PR #1: AI基盤（Gemini専用）
- PR #2: レート制限と利用量可視化
