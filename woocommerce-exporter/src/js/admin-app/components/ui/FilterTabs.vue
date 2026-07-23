<script setup>
defineProps({
  options: { type: Array, required: true },
  modelValue: { type: String, default: null }
})

defineEmits(['update:modelValue'])
</script>

<template>
  <div class="inline-flex self-start items-center gap-1 border border-gray-200 rounded-lg p-1" role="tablist">
    <button
      v-for="opt in options"
      :key="opt.key"
      type="button"
      role="tab"
      :aria-selected="modelValue === opt.key"
      class="inline-flex items-center px-3.5 py-2 rounded-md text-[13px] font-medium cursor-pointer transition-colors border-0"
      :class="modelValue === opt.key
        ? 'bg-[#6C7BFF] text-white shadow-sm'
        : 'bg-transparent text-gray-500 hover:text-gray-700'"
      @click="$emit('update:modelValue', opt.key)"
    >
      {{ opt.label }}<template v-if="opt.count !== undefined"> ({{ opt.count }})</template>
    </button>
  </div>
</template>
