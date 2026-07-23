<script setup>
defineProps({
  options: { type: Array, required: true },
  modelValue: { type: String, default: null }
})

defineEmits(['update:modelValue'])
</script>

<template>
  <div class="inline-flex flex-wrap self-start items-center gap-1 border border-gray-200 rounded-lg p-1" role="radiogroup">
    <button
      v-for="opt in options"
      :key="opt.value"
      type="button"
      :aria-checked="modelValue === opt.value"
      role="radio"
      class="inline-flex items-center px-3.5 py-2 rounded-md text-[13px] font-medium cursor-pointer transition-colors border-0"
      :class="modelValue === opt.value
        ? 'bg-[#6C7BFF] text-white shadow-sm'
        : 'bg-transparent text-gray-500 hover:text-gray-700'"
      @click="$emit('update:modelValue', opt.value)"
    >
      {{ opt.label }}
    </button>
  </div>
</template>
