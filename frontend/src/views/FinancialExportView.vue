<script setup>
import { ref } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/services/api'
import NavBar from '@/components/NavBar.vue'
import AppFooter from '@/components/AppFooter.vue'

const month = ref(new Date().getMonth() + 1)
const year = ref(new Date().getFullYear())
const format = ref('json')

const loading = ref(false)
const errorMessage = ref('')

const months = [
  { label: 'Januari', value: 1 }, { label: 'Februari', value: 2 }, { label: 'Maret', value: 3 },
  { label: 'April', value: 4 }, { label: 'Mei', value: 5 }, { label: 'Juni', value: 6 },
  { label: 'Juli', value: 7 }, { label: 'Agustus', value: 8 }, { label: 'September', value: 9 },
  { label: 'Oktober', value: 10 }, { label: 'November', value: 11 }, { label: 'Desember', value: 12 },
]

const currentYear = new Date().getFullYear()
const years = Array.from({ length: 10 }, (_, i) => currentYear - 5 + i)

const download = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await api.post('/superadmin/reports/financial/export', {
      month: month.value,
      year: year.value,
      format: format.value,
    }, { responseType: format.value === 'csv' ? 'blob' : 'json' })

    if (format.value === 'csv') {
      const blob = new Blob([response.data], { type: 'text/csv' })
      const url = window.URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = url
      a.download = `financial_report_${month.value}_${year.value}.csv`
      a.click()
      window.URL.revokeObjectURL(url)
    } else {
      const data = response.data.data
      const jsonStr = JSON.stringify(data, null, 2)
      const blob = new Blob([jsonStr], { type: 'application/json' })
      const url = window.URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = url
      a.download = `financial_report_${month.value}_${year.value}.json`
      a.click()
      window.URL.revokeObjectURL(url)
    }
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Gagal mengexport laporan.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <main class="dashboard-page">
    <NavBar />

    <section class="container">
      <div class="page-title">
        <div>
          <h1>Export Laporan Keuangan</h1>
          <p>Download laporan keuangan platform per periode.</p>
        </div>

        <RouterLink to="/dashboard" class="back-link">Kembali Dashboard</RouterLink>
      </div>

      <section class="card export-card">
        <div class="card-header">
          <h2>Filter Laporan</h2>
          <p>Pilih periode dan format laporan.</p>
        </div>

        <p v-if="errorMessage" class="field-error general-error">{{ errorMessage }}</p>

        <div class="form-grid">
          <div class="form-field">
            <label>Bulan</label>
            <select v-model.number="month" class="export-select">
              <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
            </select>
          </div>

          <div class="form-field">
            <label>Tahun</label>
            <select v-model.number="year" class="export-select">
              <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
            </select>
          </div>

          <div class="form-field">
            <label>Format</label>
            <div class="format-options">
              <label class="radio-label">
                <input v-model="format" type="radio" value="json" /> JSON
              </label>
              <label class="radio-label">
                <input v-model="format" type="radio" value="csv" /> CSV
              </label>
            </div>
          </div>
        </div>

        <button class="download-btn" :disabled="loading" @click="download">
          {{ loading ? 'Mengunduh...' : 'Download Laporan' }}
        </button>
      </section>
    </section>

    <AppFooter />
  </main>
</template>

<style scoped>
.export-card { max-width: 520px; }

.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 20px; }

.form-field { display: flex; flex-direction: column; gap: 6px; }
.form-field:last-child { grid-column: 1 / -1; }

.form-field label { font-size: 12px; font-weight: 700; color: #6f655b; text-transform: uppercase; letter-spacing: 0.3px; }

.export-select { height: 38px; padding: 0 12px; border: 1px solid #dccdbb; border-radius: 8px; background: #fffaf2; color: #07313a; font-size: 14px; font-family: inherit; }

.format-options { display: flex; gap: 20px; padding: 8px 0; }

.radio-label { display: flex; align-items: center; gap: 6px; font-size: 14px; font-weight: 600; color: #07313a; cursor: pointer; }
.radio-label input { width: 16px; height: 16px; }

.download-btn { width: 100%; height: 44px; margin-top: 24px; border: 0; border-radius: 10px; background: #07313a; color: white; font-weight: 800; font-size: 15px; cursor: pointer; }
.download-btn:disabled { opacity: 0.7; }
</style>
