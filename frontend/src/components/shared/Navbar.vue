<template>
  <nav class="bg-[#F5F0E8] border-b border-[#e0d8cc] sticky top-0 z-50">
    <div class="max-w-6xl mx-auto px-6">
      <div class="flex items-center justify-between h-16 gap-6">

        <!-- Logo -->
        <span class="text-lg font-bold tracking-wide text-[#2C2C2C] flex-shrink-0">
          BERBAGIVE
        </span>

        <!-- Menu Pills -->
        <div class="hidden md:flex items-center gap-2">
          <button class="px-4 py-1.5 rounded-full bg-[#8B4513] text-white text-sm font-medium hover:bg-[#6b3410] transition-colors">
            Beranda
          </button>
          <button class="px-4 py-1.5 rounded-full bg-[#8B4513] text-white text-sm font-medium hover:bg-[#6b3410] transition-colors">
            Campaign
          </button>
          <button class="px-4 py-1.5 rounded-full bg-[#8B4513] text-white text-sm font-medium hover:bg-[#6b3410] transition-colors">
            Komunitas
          </button>
          <button class="px-4 py-1.5 rounded-full bg-[#8B4513] text-white text-sm font-medium hover:bg-[#6b3410] transition-colors">
            Donasi Saya
          </button>
        </div>

        <!-- Search + Icons -->
        <div class="flex items-center gap-3 flex-shrink-0">
          <!-- Search bar -->
          <div class="hidden md:flex items-center bg-white rounded-full px-3 py-1.5 gap-2 border border-[#e0d8cc]">
            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input
              type="text"
              placeholder="Search"
              class="bg-transparent outline-none text-sm text-[#2C2C2C] w-36 placeholder-gray-400"
            />
          </div>

          <!-- User icon -->
          <button class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-[#e8dfd0] transition-colors">
            <svg class="w-5 h-5 text-[#2C2C2C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
          </button>

          <!-- Mail icon -->
          <button class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-[#e8dfd0] transition-colors">
            <svg class="w-5 h-5 text-[#2C2C2C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
          </button>

          <!-- Logout icon -->
          <button class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-[#e8dfd0] transition-colors">
            <svg class="w-5 h-5 text-[#2C2C2C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
          </button>
        </div>

      </div>
    </div>
  </nav>
</template>

<script setup>
// Navbar: logo, nav pills, search, action icons
</script>
