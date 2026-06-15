<template>
  <div class="min-h-screen bg-[#F5F0E8] flex flex-col items-center justify-center px-6">
    <div class="text-center max-w-md">
      <h1 class="text-8xl font-bold text-[#8B4513] mb-4">404</h1>
      <h2 class="text-xl font-semibold text-[#2C2C2C] mb-2">Halaman Tidak Ditemukan</h2>
      <p class="text-gray-500 mb-8">Halaman yang Anda cari tidak ada atau telah dipindahkan.</p>
      <router-link to="/campaigns" class="inline-block px-6 py-3 bg-[#8B4513] text-white rounded-lg font-medium hover:bg-[#6b3410] transition-colors">
        Kembali ke Beranda
      </router-link>
    </div>
  </div>
</template>
