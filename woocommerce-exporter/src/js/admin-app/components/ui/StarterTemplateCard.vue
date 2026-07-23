<script setup>
defineProps({
  title: { type: String, required: true },
  description: { type: String, default: '' },
  meta: { type: String, default: '' },
  buttonLabel: { type: String, default: 'Use Template' },
})
defineEmits(['use'])
</script>

<template>
  <div class="border border-gray-200 rounded-lg p-4 flex flex-col hover:border-indigo-200 hover:shadow-sm transition-all">
    <div class="flex items-center gap-3 mb-3">
      <slot name="icon" />
      <span class="text-[14px] font-semibold text-gray-900 leading-snug">{{ title }}</span>
    </div>
    <div class="text-[13px] text-gray-500 mb-3 leading-relaxed flex-1">{{ description }}</div>
    <div class="text-[11px] text-gray-400 mb-4">{{ meta }}</div>
    <button
      type="button"
      class="flex items-center justify-center w-full py-[8px] px-3 border border-indigo-500 rounded-[6px] text-[13px] text-indigo-600 font-semibold bg-white hover:bg-indigo-50 hover:border-indigo-600 transition-colors cursor-pointer"
      @click="$emit('use')"
    >
      {{ buttonLabel }}
    </button>
  </div>
</template>
