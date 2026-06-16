<template>
  <AdminLayout>
    <div class="max-w-4xl">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-xl font-bold text-[#2C2C2C]">Template Dokumen</h1>
          <p class="text-xs text-gray-400 mt-0.5">Kelola jenis dokumen yang diperlukan untuk registrasi komunitas</p>
        </div>
        <button @click="showAddModal = true" class="px-4 py-2 bg-[#8B4513] text-white rounded-lg text-xs font-medium hover:bg-[#6b3410]">+ Tambah Template</button>
      </div>

      <div v-if="loading" class="bg-white rounded-xl shadow-sm p-8 text-center text-sm text-gray-400">Memuat data...</div>

      <div v-else class="bg-white rounded-xl shadow-sm border border-stone-100 overflow-hidden">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-stone-100 bg-stone-50">
              <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500">Nama Dokumen</th>
              <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500">Deskripsi</th>
              <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500">Wajib</th>
              <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500">Jenis Lembaga</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(d, i) in templates" :key="d.id_jenis_dok" :class="['hover:bg-stone-50', i < templates.length - 1 ? 'border-b border-stone-100' : '']">
              <td class="px-5 py-3.5 font-medium text-[#2C2C2C]">{{ d.nama_dokumen }}</td>
              <td class="px-5 py-3.5 text-gray-500 max-w-xs truncate">{{ d.deskripsi || '-' }}</td>
              <td class="px-5 py-3.5 text-center">
                <span :class="['px-2 py-0.5 rounded-full text-xs font-medium', d.is_opsional ? 'bg-gray-100 text-gray-500' : 'bg-green-50 text-green-700']">{{ d.is_opsional ? 'Opsional' : 'Wajib' }}</span>
              </td>
              <td class="px-5 py-3.5 text-gray-500">{{ d.wajib_untuk_jenis_lembaga || 'Semua' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="showAddModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" @click.self="showAddModal = false">
      <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md mx-4">
        <h3 class="text-sm font-bold text-[#2C2C2C] mb-4">Tambah Template Dokumen</h3>
        <div class="space-y-3">
          <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Nama Dokumen</label>
            <input v-model="form.nama_dokumen" type="text" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#8B4513]" />
            <p v-if="errors.nama_dokumen" class="text-xs text-red-500 mt-1">{{ errors.nama_dokumen }}</p>
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Deskripsi</label>
            <textarea v-model="form.deskripsi" rows="2" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#8B4513] resize-none"></textarea>
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Wajib untuk Jenis Lembaga</label>
            <input v-model="form.wajib_untuk_jenis_lembaga" type="text" placeholder="Kosongkan untuk semua" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#8B4513]" />
          </div>
          <div class="flex items-center gap-2">
            <input v-model="form.is_opsional" type="checkbox" id="opsional" class="accent-[#8B4513]" />
            <label for="opsional" class="text-xs text-gray-500">Dokumen opsional (tidak wajib)</label>
          </div>
        </div>
        <div class="flex justify-end gap-3 mt-4">
          <button @click="showAddModal = false" class="px-4 py-2 text-xs text-gray-500">Batal</button>
          <button @click="handleSave" :disabled="saving" class="px-4 py-2 text-xs font-medium text-white bg-[#8B4513] rounded-lg disabled:opacity-50">{{ saving ? 'Menyimpan...' : 'Simpan' }}</button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api/axios'
import AdminLayout from '@/components/admin/AdminLayout.vue'

const loading = ref(true)
const templates = ref([])
const showAddModal = ref(false)
const saving = ref(false)
const form = ref({ nama_dokumen: '', deskripsi: '', wajib_untuk_jenis_lembaga: '', is_opsional: false })
const errors = ref({})

async function fetchTemplates() {
  loading.value = true
  try {
    const res = await api.get('/superadmin/document-templates')
    const data = res.data.data || res.data
    templates.value = data.data || data
  } catch (e) {
    templates.value = []
  } finally {
    loading.value = false
  }
}

async function handleSave() {
  errors.value = {}
  if (!form.value.nama_dokumen.trim()) {
    errors.value.nama_dokumen = 'Nama dokumen wajib diisi'
    return
  }
  saving.value = true
  try {
    await api.post('/superadmin/document-templates', {
      nama_dokumen: form.value.nama_dokumen,
      deskripsi: form.value.deskripsi || undefined,
      wajib_untuk_jenis_lembaga: form.value.wajib_untuk_jenis_lembaga || undefined,
      is_opsional: form.value.is_opsional,
    })
    showAddModal.value = false
    form.value = { nama_dokumen: '', deskripsi: '', wajib_untuk_jenis_lembaga: '', is_opsional: false }
    await fetchTemplates()
  } catch (e) {
    const errData = e.response?.data?.errors || {}
    errors.value = Object.fromEntries(Object.entries(errData).map(([k, v]) => [k, Array.isArray(v) ? v[0] : v]))
    if (Object.keys(errors.value).length === 0) alert(e.response?.data?.message || 'Gagal menyimpan')
  } finally {
    saving.value = false
  }
}

onMounted(fetchTemplates)
</script>
