<script setup>
defineProps({
  value: { type: String, required: true },
  modelValue: { type: String, default: null },
  title: { type: String, required: true },
  description: { type: String, default: null }
})

defineEmits(['update:modelValue'])
</script>

<template>
  <label
    class="flex flex-col items-start gap-2 px-5 py-4 border-2 border-gray-200 rounded-xl bg-white cursor-pointer transition-all relative hover:border-indigo-300"
    :class="modelValue === value && 'border-indigo-600 bg-indigo-50'"
  >
    <input
      type="radio"
      :value="value"
      :checked="modelValue === value"
      class="absolute opacity-0 pointer-events-none"
      @change="$emit('update:modelValue', value)"
    />
    <span
      class="w-9 h-9 rounded-lg inline-flex items-center justify-center flex-shrink-0 transition-all"
      :class="modelValue === value ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-600'"
    >
      <slot name="icon" />
    </span>
    <span
      class="text-[13px] font-semibold block"
      :class="modelValue === value ? 'text-indigo-600' : 'text-gray-900'"
    >{{ title }}</span>
    <span v-if="description" class="text-xs text-gray-500 block leading-tight">{{ description }}</span>
  </label>
</template>
