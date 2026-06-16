<template>
  <section aria-labelledby="cerita-kampanye-title" class="bg-card rounded-xl p-6 border border-border">
    <div class="flex items-stretch gap-3 mb-5">
      <div class="w-1 rounded-full bg-[#8B4513]" aria-hidden="true" />
      <h2 id="cerita-kampanye-title" class="font-semibold text-base text-foreground">
        Cerita Kampanye
      </h2>
    </div>

    <!-- Skeleton loading -->
    <template v-if="!deskripsi">
      <div class="flex flex-col gap-3">
        <div class="h-4 bg-secondary rounded animate-pulse w-full" />
        <div class="h-4 bg-secondary rounded animate-pulse w-5/6" />
        <div class="h-4 bg-secondary rounded animate-pulse w-4/6" />
      </div>
    </template>

    <!-- Content -->
    <template v-else>
      <div class="text-sm leading-relaxed text-foreground whitespace-pre-line">
        {{ deskripsi }}
      </div>

      <div
        v-if="fotoGaleri && fotoGaleri.length"
        class="grid grid-cols-2 gap-3 mt-6"
        aria-label="Galeri foto kampanye"
      >
        <figure v-for="(foto, idx) in fotoGaleri" :key="idx">
          <img
            :src="foto.url"
            :alt="foto.alt || `Foto kampanye ${idx + 1}`"
            class="w-full h-36 object-cover rounded-lg"
            loading="lazy"
          />
        </figure>
      </div>
    </template>
  </section>
</template>

<script setup>
defineProps({
  deskripsi: { type: String, default: '' },
  fotoGaleri: { type: Array, default: () => [] },
})
</script>
