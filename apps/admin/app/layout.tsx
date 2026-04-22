import type { ReactNode } from 'react'

export const metadata = {
  title: 'AI不動産 管理ダッシュボード',
}

export default function RootLayout({ children }: { children: ReactNode }) {
  return (
    <html lang="ja">
      <body style={{ margin: 0, fontFamily: 'sans-serif', background: '#f9fafb' }}>
        <nav style={{ background: '#1e3a5f', color: '#fff', padding: '12px 24px' }}>
          <a href="/" style={{ color: '#fff', fontWeight: 'bold', marginRight: 24, textDecoration: 'none' }}>
            ダッシュボード
          </a>
          <a href="/reports" style={{ color: '#94bcf0', textDecoration: 'none' }}>
            レポート一覧
          </a>
        </nav>
        <main style={{ maxWidth: 960, margin: '32px auto', padding: '0 16px' }}>
          {children}
        </main>
      </body>
    </html>
  )
}
