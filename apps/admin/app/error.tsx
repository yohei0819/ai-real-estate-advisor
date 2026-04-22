'use client'

export default function ErrorPage({ error, reset }: { error: Error; reset: () => void }) {
  return (
    <div style={{ textAlign: 'center', paddingTop: 64 }}>
      <p style={{ color: '#dc2626', fontWeight: 'bold' }}>データの取得に失敗しました</p>
      <p style={{ color: '#6b7280', fontSize: 14 }}>{error.message}</p>
      <button
        onClick={reset}
        style={{
          marginTop: 16,
          padding: '8px 20px',
          background: '#1e3a5f',
          color: '#fff',
          border: 'none',
          borderRadius: 6,
          cursor: 'pointer',
        }}
      >
        再試行
      </button>
    </div>
  )
}
