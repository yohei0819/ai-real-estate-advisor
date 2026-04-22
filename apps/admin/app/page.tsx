import type { CSSProperties } from 'react'
import { API_BASE_URL } from '../lib/config'
import type { AdminStats } from '../lib/types'
import StatCard from '../components/StatCard'

async function fetchStats(): Promise<AdminStats> {
  const res = await fetch(`${API_BASE_URL}/api/v1/admin/stats`, { cache: 'no-store' })
  if (!res.ok) throw new Error(`stats fetch failed: ${res.status}`)
  return res.json() as Promise<AdminStats>
}

export default async function DashboardPage() {
  const stats = await fetchStats()

  if (stats.total === 0) {
    return (
      <div style={emptyStyle}>
        <p style={{ fontSize: 40 }}>📊</p>
        <p>まだレポートが生成されていません</p>
      </div>
    )
  }

  return (
    <div>
      <h1 style={headingStyle}>AI利用状況</h1>
      <div style={gridStyle}>
        <StatCard label="累計レポート数"     value={stats.total} />
        <StatCard label="直近24時間"         value={stats.last_24h} />
        <StatCard label="レートリミット残数" value={stats.rate_limit_remaining} />
      </div>
    </div>
  )
}

// ─── スタイル定数（再レンダーで再生成されないよう module 外に配置） ───────────

const emptyStyle: CSSProperties = {
  textAlign: 'center',
  paddingTop: 64,
  color: '#6b7280',
}

const headingStyle: CSSProperties = {
  fontSize: 24,
  fontWeight: 'bold',
  marginBottom: 24,
}

const gridStyle: CSSProperties = {
  display: 'grid',
  gridTemplateColumns: 'repeat(3, 1fr)',
  gap: 16,
}
