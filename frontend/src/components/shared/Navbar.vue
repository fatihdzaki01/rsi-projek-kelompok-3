<script setup>
import { ref, computed, onMounted, nextTick, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/api/axios'

const route = useRoute()
const router = useRouter()

const isMobileMenuOpen = ref(false)
const searchQuery = ref('')
const isLoggedIn = ref(!!localStorage.getItem('token'))

const indicatorStyle = ref({ width: '0px', transform: 'translateX(0px)' })
const navRef = ref(null)

const navLinks = [
  { label: 'Beranda', path: '/' },
  { label: 'Campaign', path: '/campaigns' },
  { label: 'Komunitas', path: '/communities' },
]

function isActive(path) {
  return path === '/' ? route.path === '/' : route.path.startsWith(path)
}

function handleSearch() {
  if (searchQuery.value.trim()) {
    router.push('/search?q=' + encodeURIComponent(searchQuery.value.trim()))
  }
}

async function handleLogout() {
  try {
    await api.post('/auth/logout')
  } catch {}
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  isLoggedIn.value = false
  router.push('/login')
}

function updateIndicator() {
  nextTick(() => {
    const nav = navRef.value
    if (!nav) return
    const activeEl = nav.querySelector('.nav-link-active')
    if (activeEl) {
      indicatorStyle.value = {
        width: activeEl.offsetWidth + 'px',
        transform: 'translateX(' + activeEl.offsetLeft + 'px)',
      }
    }
  })
}

watch(() => route.path, updateIndicator)

onMounted(() => {
  nextTick(updateIndicator)
})
</script>

<template>
  <header class="sticky top-0 z-50 h-16 w-full border-b border-gray-100 bg-white shadow-sm">
    <div class="mx-auto flex h-full max-w-7xl items-center justify-between px-4 lg:px-6">
      <router-link to="/" class="text-sm font-semibold tracking-widest text-[#8B4513]">BERBAGIVE</router-link>

      <nav ref="navRef" class="relative hidden items-center md:flex">
        <div
          class="absolute bottom-0 h-0.5 bg-[#8B4513] rounded-full transition-all duration-300 ease-in-out"
          :style="indicatorStyle"
        />
        <router-link
          v-for="link in navLinks"
          :key="link.path"
          :to="link.path"
          :class="[
            'relative px-4 py-5 text-sm transition-colors',
            isActive(link.path)
              ? 'nav-link-active font-medium text-[#8B4513]'
              : 'text-gray-700 hover:text-[#8B4513]',
          ]"
        >{{ link.label }}</router-link>
      </nav>

      <div class="hidden items-center gap-3 md:flex">
        <div class="relative w-48 lg:w-64">
          <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="7" /><path stroke-linecap="round" d="m20 20-3-3" />
          </svg>
          <input
            v-model="searchQuery"
            @keydown.enter="handleSearch"
            type="text"
            placeholder="Search"
            class="w-full rounded-full bg-[#F5F0E8] py-2 pl-9 pr-4 text-sm text-gray-700 placeholder-gray-400 focus:outline-none"
          />
        </div>

        <template v-if="isLoggedIn">
          <router-link to="/donations/history" class="text-sm text-gray-700 hover:text-[#8B4513] transition-colors" :class="route.path.startsWith('/donations') ? 'font-medium text-[#8B4513]' : ''">Donasi Saya</router-link>
          <router-link to="/profile" class="text-gray-700 transition-colors hover:text-[#8B4513]" aria-label="Profil">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="8" r="4" /><path stroke-linecap="round" d="M4 20c0-4 3.5-6 8-6s8 2 8 6" />
            </svg>
          </router-link>
          <router-link to="/notifications" class="text-gray-700 transition-colors hover:text-[#8B4513]" aria-label="Notifikasi">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
          </router-link>
          <button @click="handleLogout" class="text-gray-700 transition-colors hover:text-[#8B4513]" aria-label="Keluar">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
          </button>
        </template>
        <template v-else>
          <router-link to="/login" class="text-sm text-gray-700 hover:text-[#8B4513] transition-colors">Donasi Saya</router-link>
          <router-link to="/login" class="text-gray-700 transition-colors hover:text-[#8B4513]" aria-label="Profil">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="8" r="4" /><path stroke-linecap="round" d="M4 20c0-4 3.5-6 8-6s8 2 8 6" />
            </svg>
          </router-link>
          <router-link to="/login" class="text-gray-700 transition-colors hover:text-[#8B4513]" aria-label="Notifikasi">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
          </router-link>
          <router-link to="/login" class="text-sm font-medium text-[#8B4513] hover:text-[#6b3410]">Masuk</router-link>
          <router-link to="/register" class="rounded-full bg-[#8B4513] px-4 py-1.5 text-sm font-medium text-white hover:bg-[#6b3410]">Daftar</router-link>
        </template>
      </div>

      <button class="text-gray-700 transition-colors hover:text-[#8B4513] md:hidden" @click="isMobileMenuOpen = !isMobileMenuOpen" aria-label="Menu">
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path v-if="!isMobileMenuOpen" stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16" />
          <path v-else stroke-linecap="round" d="M6 6l12 12M6 18 18 6" />
        </svg>
      </button>
    </div>

    <div v-if="isMobileMenuOpen" class="border-b border-gray-100 bg-white px-4 pb-4 md:hidden">
      <div class="relative my-3">
        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="7" /><path stroke-linecap="round" d="m20 20-3-3" />
        </svg>
        <input
          v-model="searchQuery"
          @keydown.enter="handleSearch"
          type="text"
          placeholder="Search"
          class="w-full rounded-full bg-[#F5F0E8] py-2 pl-9 pr-4 text-sm text-gray-700 placeholder-gray-400 focus:outline-none"
        />
      </div>

      <nav class="flex flex-col gap-1">
        <template v-for="link in navLinks" :key="link.path">
          <router-link
            :to="link.path"
            class="rounded-lg px-3 py-2 text-sm transition-colors hover:text-[#8B4513]"
            :class="isActive(link.path) ? 'font-medium text-[#8B4513] bg-[#F5F0E8]' : 'text-gray-700'"
            @click="isMobileMenuOpen = false"
          >{{ link.label }}</router-link>
        </template>
        <button
          @click="() => { router.push('/login'); isMobileMenuOpen = false }"
          class="rounded-lg px-3 py-2 text-sm transition-colors hover:text-[#8B4513] text-gray-700"
        >
          Donasi Saya
        </button>
      </nav>

      <div class="mt-3 flex items-center gap-4 border-t border-gray-100 pt-3">
        <template v-if="isLoggedIn">
          <router-link to="/profile" class="text-gray-700" aria-label="Profil">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="8" r="4" /><path stroke-linecap="round" d="M4 20c0-4 3.5-6 8-6s8 2 8 6" />
            </svg>
          </router-link>
          <router-link to="/notifications" class="text-gray-700" aria-label="Notifikasi">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
          </router-link>
          <button @click="handleLogout" class="text-gray-700" aria-label="Keluar">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
          </button>
        </template>
        <template v-else>
          <router-link to="/login" class="text-gray-700" aria-label="Profil" @click="isMobileMenuOpen = false">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="8" r="4" /><path stroke-linecap="round" d="M4 20c0-4 3.5-6 8-6s8 2 8 6" />
            </svg>
          </router-link>
          <router-link to="/login" class="text-gray-700" aria-label="Notifikasi" @click="isMobileMenuOpen = false">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
          </router-link>
          <router-link to="/login" class="text-sm font-medium text-[#8B4513]" @click="isMobileMenuOpen = false">Masuk</router-link>
          <router-link to="/register" class="rounded-full bg-[#8B4513] px-4 py-1.5 text-sm font-medium text-white" @click="isMobileMenuOpen = false">Daftar</router-link>
        </template>
      </div>
    </div>
  </header>
</template>
