<template>
  <div class="min-h-screen bg-[#F5F0E8]">
    <header class="bg-[#1a2744] text-white px-6 py-3 flex items-center justify-between sticky top-0 z-50">
      <div class="flex items-center gap-4">
        <router-link to="/dashboard" class="font-bold tracking-wide text-sm">BERBAGIVE</router-link>
        <span class="text-white/50 text-xs">|</span>
        <span class="text-xs text-white/70">Panel Admin</span>
      </div>
      <div class="flex items-center gap-3">
        <router-link to="/dashboard/profile" class="text-xs text-white/60 hover:text-white transition-colors" title="Edit Profil">
          {{ profile?.nama_lengkap || 'Admin' }}
        </router-link>
        <button @click="handleLogout" class="text-xs text-white/70 hover:text-white px-3 py-1 rounded-full border border-white/20 hover:border-white/40 transition-colors">
          Logout
        </button>
      </div>
    </header>

    <div class="flex">
      <aside class="w-56 bg-white border-r border-stone-200 min-h-[calc(100vh-52px)] flex-shrink-0 hidden md:block">
        <nav class="p-4 space-y-1">
          <router-link v-for="item in menuItems" :key="item.path" :to="item.path" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-colors" :class="isActive(item.path) ? 'bg-[#8B4513] text-white' : 'text-gray-600 hover:bg-stone-100'">
            <span v-html="item.icon" class="w-4 h-4 flex items-center justify-center"></span>
            {{ item.label }}
          </router-link>
        </nav>
      </aside>

      <main class="flex-1 p-6">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/api/axios'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()

const profile = ref(null)

onMounted(async () => {
  try {
    const res = await api.get('/superadmin/profile')
    profile.value = res.data.data
  } catch (e) {
    // silent
  }
})

const menuItems = [
  { path: '/dashboard', label: 'Dashboard', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>' },
  { path: '/dashboard/donors', label: 'Donatur', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>' },
  { path: '/dashboard/communities', label: 'Komunitas', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 21v-2a4 4 0 00-4-4H9a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>' },
  { path: '/dashboard/community-registrations', label: 'Registrasi Komunitas', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>' },
  { path: '/campaigns/approval', label: 'Approval Campaign', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' },
  { path: '/dashboard/campaign-categories', label: 'Kategori Campaign', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>' },
  { path: '/disbursements', label: 'Pencairan Dana', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>' },
  { path: '/dashboard/bank-account-changes', label: 'Perubahan Rekening', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>' },
  { path: '/dashboard/campaign-reports', label: 'Laporan Campaign', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>' },
  { path: '/dashboard/document-templates', label: 'Template Dokumen', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>' },
  { path: '/dashboard/analytics', label: 'Analitik', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>' },
  { path: '/dashboard/audit-logs', label: 'Audit Log', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' },
  { path: '/dashboard/profile', label: 'Profil Saya', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>' },
]

function isActive(path) {
  return route.path === path || route.path.startsWith(path + '/')
}

function handleLogout() {
  auth.logout()
  router.push('/login')
}
</script>
