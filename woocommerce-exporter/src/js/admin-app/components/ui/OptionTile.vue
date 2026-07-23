<script setup>
defineProps({
  value: { type: String, required: true },
  modelValue: { type: String, default: null },
  label: { type: String, required: true }
})

defineEmits(['update:modelValue'])
</script>

<template>
  <label
    class="flex flex-col items-center justify-center gap-2 px-5 py-4 border-2 border-gray-200 rounded-xl bg-white cursor-pointer text-center min-w-[110px] transition-all relative hover:border-indigo-300 hover:shadow-[0_0_0_3px_#EFF1FF]"
    :class="modelValue === value && 'border-indigo-600 bg-indigo-50 shadow-[0_0_0_3px_rgba(108,123,255,0.08)]'"
  >
    <input
      type="radio"
      :value="value"
      :checked="modelValue === value"
      class="absolute opacity-0 pointer-events-none"
      @change="$emit('update:modelValue', value)"
    />
    <span
      class="flex items-center justify-center"
      :class="modelValue === value ? 'text-indigo-600' : 'text-gray-400'"
    >
      <slot name="icon" />
    </span>
    <span
      class="text-xs whitespace-nowrap"
      :class="modelValue === value ? 'text-indigo-600 font-semibold' : 'text-gray-500 font-medium'"
    >{{ label }}</span>
  </label>
</template>
