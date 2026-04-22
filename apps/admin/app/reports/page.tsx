import type { CSSProperties } from 'react'
import { API_BASE_URL } from '../../lib/config'
import type { ReportRow } from '../../lib/types'

async function fetchReports(): Promise<ReportRow[]> {
  const res = await fetch(`${API_BASE_URL}/api/v1/admin/reports`, { cache: 'no-store' })
  if (!res.ok) throw new Error(`reports fetch failed: ${res.status}`)
  return res.json() as Promise<ReportRow[]>
}

export default async function ReportsPage() {
  const rows = await fetchReports()

  if (rows.length === 0) {
    return (
      <div style={emptyStyle}>
        <p style={{ fontSize: 40 }}>📄</p>
        <p>まだレポートが生成されていません</p>
      </div>
    )
  }

  return (
    <div>
      <h1 style={headingStyle}>レポート一覧（直近100件）</h1>
      <div style={tableWrapStyle}>
        <table style={tableStyle}>
          <thead>
            <tr style={theadRowStyle}>
              <th style={thStyle}>ID</th>
              <th style={thStyle}>モデル</th>
              <th style={thStyle}>ステータス</th>
              <th style={thStyle}>生成日時</th>
            </tr>
          </thead>
          <tbody>
            {rows.map((row) => (
              <tr key={row.id} style={tbodyRowStyle}>
                <td style={tdStyle}>{row.id}</td>
                <td style={tdStyle}>{row.model}</td>
                <td style={tdStyle}>{row.status}</td>
                <td style={tdStyle}>{new Date(row.created_at).toLocaleString('ja-JP')}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  )
}

// ─── スタイル定数 ────────────────────────────────────────────────────────────

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

const tableWrapStyle: CSSProperties = {
  background: '#fff',
  border: '1px solid #e5e7eb',
  borderRadius: 12,
  overflow: 'hidden',
}

const tableStyle: CSSProperties = {
  width: '100%',
  borderCollapse: 'collapse',
  fontSize: 14,
}

const theadRowStyle: CSSProperties = {
  background: '#f3f4f6',
  textAlign: 'left',
}

const tbodyRowStyle: CSSProperties = {
  borderTop: '1px solid #e5e7eb',
}

const thStyle: CSSProperties = {
  padding: '10px 16px',
  fontWeight: 600,
  color: '#374151',
}

const tdStyle: CSSProperties = {
  padding: '10px 16px',
  color: '#4b5563',
}
