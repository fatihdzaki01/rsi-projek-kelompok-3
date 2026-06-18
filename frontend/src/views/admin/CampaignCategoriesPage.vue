<template>
  <AdminLayout>
    <div class="max-w-4xl">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-xl font-bold text-[#2C2C2C]">Kategori Campaign</h1>
          <p class="text-xs text-gray-400 mt-0.5">Kelola kategori campaign platform</p>
        </div>
        <button @click="showAddModal = true" class="px-4 py-2 bg-[#8B4513] text-white rounded-lg text-xs font-medium hover:bg-[#6b3410] transition-colors">+ Tambah Kategori</button>
      </div>

      <div v-if="loading" class="bg-white rounded-xl shadow-sm p-8 text-center text-sm text-gray-400">Memuat data...</div>

      <div v-else class="bg-white rounded-xl shadow-sm border border-stone-100 overflow-hidden">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-stone-100 bg-stone-50">
              <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500">Nama Kategori</th>
              <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500">Deskripsi</th>
              <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500">Status</th>
              <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(cat, i) in categories" :key="cat.id_kategori" :class="['hover:bg-stone-50', i < categories.length - 1 ? 'border-b border-stone-100' : '']">
              <td class="px-5 py-3.5 font-medium text-[#2C2C2C]">{{ cat.nama_kategori }}</td>
              <td class="px-5 py-3.5 text-gray-500 max-w-xs truncate">{{ cat.deskripsi || '-' }}</td>
              <td class="px-5 py-3.5 text-center">
                <button @click="toggleStatus(cat)" :class="['px-2 py-0.5 rounded-full text-xs font-medium transition-colors', cat.is_active ? 'bg-green-50 text-green-700 hover:bg-red-50 hover:text-red-700' : 'bg-gray-100 text-gray-500 hover:bg-green-50 hover:text-green-700']">{{ cat.is_active ? 'Aktif' : 'Nonaktif' }}</button>
              </td>
              <td class="px-5 py-3.5 text-center">
                <button @click="editCategory(cat)" class="text-xs text-[#8B4513] hover:underline">Edit</button>
              </td>
            </tr>
            <tr v-if="categories.length === 0">
              <td colspan="4" class="px-5 py-12 text-center text-sm text-gray-400">Belum ada kategori</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="showAddModal || showEditModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" @click.self="closeModal">
      <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md mx-4">
        <h3 class="text-sm font-bold text-[#2C2C2C] mb-4">{{ showEditModal ? 'Edit Kategori' : 'Tambah Kategori' }}</h3>
        <div class="space-y-3">
          <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Nama Kategori</label>
            <input v-model="form.nama_kategori" type="text" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#8B4513]" />
            <p v-if="formErrors.nama_kategori" class="text-xs text-red-500 mt-1">{{ formErrors.nama_kategori }}</p>
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Deskripsi</label>
            <textarea v-model="form.deskripsi" rows="3" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#8B4513] resize-none"></textarea>
          </div>
        </div>
        <div class="flex items-center justify-end gap-3 mt-4">
          <button @click="closeModal" class="px-4 py-2 text-xs text-gray-500 hover:text-gray-700">Batal</button>
          <button @click="saveCategory" :disabled="saving" class="px-4 py-2 text-xs font-medium text-white bg-[#8B4513] rounded-lg hover:bg-[#6b3410] disabled:opacity-50">{{ saving ? 'Menyimpan...' : 'Simpan' }}</button>
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
const categories = ref([])
const showAddModal = ref(false)
const showEditModal = ref(false)
const editingId = ref(null)
const saving = ref(false)
const form = ref({ nama_kategori: '', deskripsi: '' })
const formErrors = ref({})

async function fetchCategories() {
  loading.value = true
  try {
    const res = await api.get('/superadmin/campaign-categories')
    const data = res.data.data || res.data
    categories.value = data.data || data
  } catch (e) {
    categories.value = []
  } finally {
    loading.value = false
  }
}

function editCategory(cat) {
  editingId.value = cat.id_kategori
  form.value = { nama_kategori: cat.nama_kategori, deskripsi: cat.deskripsi || '' }
  formErrors.value = {}
  showEditModal.value = true
}

function closeModal() {
  showAddModal.value = false
  showEditModal.value = false
  editingId.value = null
  form.value = { nama_kategori: '', deskripsi: '' }
  formErrors.value = {}
}

async function saveCategory() {
  formErrors.value = {}
  if (!form.value.nama_kategori.trim()) {
    formErrors.value.nama_kategori = 'Nama kategori wajib diisi'
    return
  }
  saving.value = true
  try {
    if (showEditModal.value) {
      await api.patch(`/superadmin/campaign-categories/${editingId.value}`, form.value)
    } else {
      await api.post('/superadmin/campaign-categories', form.value)
    }
    closeModal()
    await fetchCategories()
  } catch (e) {
    const errData = e.response?.data?.errors || {}
    formErrors.value = Object.fromEntries(Object.entries(errData).map(([k, v]) => [k, Array.isArray(v) ? v[0] : v]))
    if (Object.keys(formErrors.value).length === 0) {
      alert(e.response?.data?.message || 'Gagal menyimpan')
    }
  } finally {
    saving.value = false
  }
}

async function toggleStatus(cat) {
  try {
    await api.patch(`/superadmin/campaign-categories/${cat.id_kategori}/status`)
    cat.is_active = !cat.is_active
  } catch (e) {
    alert(e.response?.data?.message || 'Gagal mengubah status')
  }
}

onMounted(fetchCategories)
</script>
