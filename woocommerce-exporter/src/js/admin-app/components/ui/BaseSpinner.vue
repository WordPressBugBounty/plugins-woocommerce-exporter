<script setup>
defineProps({
  size: { type: String, default: 'md' } // sm | md | lg
})

const SIZE_CLASSES = {
  sm: 'w-3 h-3 border-2',
  md: 'w-[18px] h-[18px] border-2',
  lg: 'w-7 h-7 border-[3px]',
}
</script>

<template>
  <span
    class="inline-block rounded-full border-gray-200 border-t-indigo-600 animate-spin"
    :class="SIZE_CLASSES[size] ?? SIZE_CLASSES.md"
    role="status"
    aria-label="Loading"
  />
</template>
