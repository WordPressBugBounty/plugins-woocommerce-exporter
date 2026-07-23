<script setup>
defineProps({
  label: { type: String, default: null },
  help: { type: String, default: null },
  error: { type: String, default: null },
  for: { type: String, default: null }
})
</script>

<template>
  <div class="flex flex-col gap-1">
    <label v-if="label" :for="$props.for" class="text-sm font-medium text-gray-700">{{ label }}</label>
    <slot />
    <p v-if="error" class="m-0 text-xs text-red-600">{{ error }}</p>
    <p v-else-if="help" class="m-0 text-xs text-gray-500">{{ help }}</p>
  </div>
</template>
