<template>
  <button
    v-bind="$attrs"
    :class="[
      'inline-flex items-center justify-center rounded-full text-sm font-medium transition-colors disabled:pointer-events-none disabled:opacity-50',
      variants[variant],
      sizes[size],
      $attrs.class,
    ]"
  >
    <slot />
  </button>
</template>

<script setup>
defineProps({
  variant: { type: String, default: 'default' },
  size: { type: String, default: 'default' },
})

const variants = {
  default: 'bg-[#8B4513] text-white hover:bg-[#6b3410]',
  outline: 'border border-[#8B4513] text-[#8B4513] hover:bg-[#8B4513] hover:text-white',
  ghost: 'text-[#2C2C2C] hover:bg-[#e8dfd0]',
}

const sizes = {
  default: 'px-4 py-1.5',
  sm: 'px-3 py-1 text-xs',
  lg: 'px-5 py-2',
  icon: 'w-8 h-8',
}
</script>
