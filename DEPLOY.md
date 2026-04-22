# デプロイ手順 — Vercel (web/admin) + Railway (API)

## 事前確認

- [ ] GitHub リポジトリに最新のコードがプッシュされている
- [ ] Vercel アカウント作成済み (<https://vercel.com>)
- [ ] Railway アカウント作成済み (<https://railway.app>)
- [ ] Gemini API キー取得済み

---

## Step 1: Railway — Laravel API

### 1-1. プロジェクト作成

1. Railway ダッシュボード → **New Project** → **Deploy from GitHub repo**
2. リポジトリを選択 → **Root Directory** を `apps/api` に設定
3. **Deploy** をクリック（Nixpacks が自動で PHP を検出）

### 1-2. 環境変数の設定

Railway の **Variables** タブで以下を設定：

| 変数名 | 値 |
|---|---|
| `APP_ENV` | `production` |
| `APP_KEY` | `php artisan key:generate --show` の出力 |
| `APP_DEBUG` | `false` |
| `GEMINI_API_KEY` | 取得した Gemini API キー |
| `GEMINI_MODEL` | `gemini-2.5-flash` |
| `AI_RATE_LIMIT_PER_MINUTE` | `30` |
| `FRONTEND_URL` | Vercel web の URL（Step 2 完了後に設定） |
| `ADMIN_URL` | Vercel admin の URL（Step 3 完了後に設定） |

> **APP_KEY の生成方法:**
> ローカルで `cd apps/api && php artisan key:generate --show` を実行

### 1-3. デプロイ確認

Railway が表示する URL で以下を確認：

```
GET https://<your-app>.railway.app/health
→ 200 OK が返れば成功
```

---

## Step 2: Vercel — Nuxt 3 (apps/web)

### 2-1. プロジェクト作成

1. Vercel ダッシュボード → **Add New Project** → GitHub リポジトリを選択
2. **Root Directory** を `apps/web` に設定
3. **Framework Preset**: Nuxt.js（自動検出される）
4. **Build Command**: `pnpm -F @ai-real-estate/web build`（自動設定）

### 2-2. 環境変数の設定

Vercel の **Environment Variables** で設定：

| 変数名 | 値 |
|---|---|
| `NUXT_PUBLIC_API_BASE` | `https://<your-app>.railway.app` |

### 2-3. デプロイ

**Deploy** をクリック → ビルド完了後に URL が発行される

---

## Step 3: Vercel — Next.js Admin (apps/admin)

### 3-1. プロジェクト作成

1. Vercel ダッシュボード → **Add New Project** → 同じリポジトリを選択
2. **Root Directory** を `apps/admin` に設定
3. **Framework Preset**: Next.js（自動検出される）

### 3-2. 環境変数の設定

| 変数名 | 値 |
|---|---|
| `API_BASE_URL` | `https://<your-app>.railway.app` |

### 3-3. デプロイ

**Deploy** をクリック → URL が発行される

---

## Step 4: CORS 設定の更新

Railway の Variables に戻り、Vercel で発行された URL を設定：

| 変数名 | 値 |
|---|---|
| `FRONTEND_URL` | `https://<web>.vercel.app` |
| `ADMIN_URL` | `https://<admin>.vercel.app` |

設定後 Railway が自動再デプロイされる。

---

## Step 5: GitHub Secrets の設定（CI/CD 用）

GitHub リポジトリ → **Settings** → **Secrets and variables** → **Actions** で追加：

| シークレット名 | 値 |
|---|---|
| `GEMINI_API_KEY` | Gemini API キー |

---

## 動作確認チェックリスト

- [ ] `GET /health` → 200
- [ ] Web フロント: `https://<web>.vercel.app` が表示される
- [ ] Admin パネル: `https://<admin>.vercel.app` が表示される
- [ ] 投資シミュレーション → AI レポート生成が完了する
- [ ] Admin パネルでレポート一覧・統計が表示される

---

## 自動デプロイ

GitHub の `main` ブランチにプッシュすると：

- **Vercel**: 自動ビルド＆デプロイ
- **Railway**: 自動デプロイ

PR ごとに **Preview Deploy** も自動生成される。
