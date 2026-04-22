import type { CSSProperties } from 'react'

interface Props {
  label: string
  value: number
}

export default function StatCard({ label, value }: Props) {
  return (
    <div style={cardStyle}>
      <p style={labelStyle}>{label}</p>
      <p style={valueStyle}>{value}</p>
    </div>
  )
}

const cardStyle: CSSProperties = {
  background: '#fff',
  border: '1px solid #e5e7eb',
  borderRadius: 12,
  padding: '24px 20px',
  textAlign: 'center',
}

const labelStyle: CSSProperties = {
  color: '#6b7280',
  fontSize: 13,
  marginBottom: 8,
}

const valueStyle: CSSProperties = {
  fontSize: 36,
  fontWeight: 'bold',
  color: '#1e3a5f',
}
