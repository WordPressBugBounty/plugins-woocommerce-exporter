<script setup>
defineProps({
  title: { type: String, default: null },
  message: { type: String, default: null }
})
</script>

<template>
  <div class="text-center py-16 px-4 text-gray-500">
    <div v-if="$slots.icon" class="text-gray-300 mb-3">
      <slot name="icon" />
    </div>
    <h3 v-if="title" class="text-[13px] font-semibold text-gray-900 mb-1 mt-0">{{ title }}</h3>
    <p v-if="message" class="m-0 text-sm">{{ message }}</p>
    <div v-if="$slots.actions" class="mt-4 inline-flex gap-2">
      <slot name="actions" />
    </div>
  </div>
</template>
