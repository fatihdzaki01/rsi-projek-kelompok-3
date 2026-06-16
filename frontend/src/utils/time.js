/**
 * Format remaining time until endDate.
 * - >= 1 day  → "X Hari Lagi"
 * - < 1 day   → "X Jam Y Menit" or "X Menit Y Detik" or "X Detik"
 * - expired   → "Selesai"
 */
export function formatTimeRemaining(endDate) {
  if (!endDate) return '—'

  const now = new Date()
  const end = new Date(endDate)
  const diff = end - now

  if (diff <= 0) return 'Selesai'

  const days = Math.floor(diff / (1000 * 60 * 60 * 24))

  if (days >= 1) {
    return `${days} Hari Lagi`
  }

  const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))
  const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))
  const seconds = Math.floor((diff % (1000 * 60)) / 1000)

  if (hours > 0) {
    return `${hours} Jam ${minutes} Menit`
  }
  if (minutes > 0) {
    return `${minutes} Menit ${seconds} Detik`
  }
  return `${seconds} Detik`
}
