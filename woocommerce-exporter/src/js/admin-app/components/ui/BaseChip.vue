<script setup>
defineProps({
  variant: { type: String, default: 'default' } // default | primary | success | danger | warning | accent
})

const VARIANT_CLASSES = {
  default: 'bg-gray-100 text-gray-700',
  primary: 'bg-indigo-50 text-indigo-700',
  success: 'bg-emerald-50 text-emerald-700',
  danger: 'bg-red-50 text-red-600',
  warning: 'bg-amber-50 text-amber-500',
  accent: 'bg-violet-50 text-violet-500',
}
</script>

<template>
  <span
    class="inline-flex items-center gap-1 py-0.5 px-2 rounded-full text-xs font-medium"
    :class="VARIANT_CLASSES[variant] ?? VARIANT_CLASSES.default"
  >
    <slot name="icon" />
    <slot />
  </span>
</template>
