<script setup>
defineProps({
  value: { type: String, required: true },
  modelValue: { type: String, default: null },
  label: { type: String, required: true },
  description: { type: String, default: null }
})

defineEmits(['update:modelValue'])
</script>

<template>
  <label
    class="relative flex flex-col items-center gap-2 px-4 py-5 border-2 border-gray-200 rounded-xl bg-white cursor-pointer text-center transition-all hover:border-indigo-300 hover:shadow-[0_0_0_3px_#EFF1FF]"
    :class="modelValue === value && 'border-indigo-600 bg-indigo-50 shadow-[0_0_0_3px_rgba(108,123,255,0.08)]'"
  >
    <input
      type="radio"
      :value="value"
      :checked="modelValue === value"
      class="absolute opacity-0 pointer-events-none"
      @change="$emit('update:modelValue', value)"
    />
    <!-- Selected checkmark badge -->
    <span
      v-if="modelValue === value"
      class="absolute top-2 right-2 w-5 h-5 rounded-full bg-indigo-600 inline-flex items-center justify-center"
    >
      <svg width="10" height="8" viewBox="0 0 10 8" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M1 3.5L3.8 6.5L9 1.5" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </span>
    <span
      class="w-11 h-11 rounded-xl inline-flex items-center justify-center transition-all"
      :class="modelValue === value ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-500'"
    >
      <slot name="icon" />
    </span>
    <span
      class="text-[13px] font-semibold block"
      :class="modelValue === value ? 'text-indigo-600' : 'text-gray-900'"
    >{{ label }}</span>
    <span
      v-if="description"
      class="text-xs block leading-tight"
      :class="modelValue === value ? 'text-indigo-500' : 'text-gray-500'"
    >{{ description }}</span>
  </label>
</template>
