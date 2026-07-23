<script setup>
defineProps({
  value: { type: String, required: true },
  modelValue: { type: String, default: null },
  title: { type: String, required: true },
  description: { type: String, default: null },
  name: { type: String, default: null }
})

defineEmits(['update:modelValue'])
</script>

<template>
  <label
    class="relative flex items-center gap-3 py-3.5 px-3.5 bg-white border-[1.5px] border-gray-200 rounded-[10px] cursor-pointer transition-[border-color,background] hover:border-indigo-300"
    :class="modelValue === value && 'border-indigo-600 bg-[#F5F3FF]'"
  >
    <input
      type="radio"
      :name="name"
      :value="value"
      :checked="modelValue === value"
      class="absolute opacity-0 pointer-events-none"
      @change="$emit('update:modelValue', value)"
    />

    <!-- Icon slot -->
    <span
      class="w-8 h-8 rounded-lg inline-flex items-center justify-center flex-shrink-0 transition-colors"
      :class="modelValue === value ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-500'"
    >
      <slot name="icon" />
    </span>

    <!-- Label -->
    <span class="flex flex-col gap-0.5 flex-1 min-w-0">
      <span class="text-[13px] font-semibold text-gray-900 leading-snug">{{ title }}</span>
      <span v-if="description" class="text-[12px] text-gray-500 leading-[1.4]">{{ description }}</span>
    </span>

    <!-- Custom radio indicator: solid indigo fill + inset white ring when selected -->
    <span
      class="w-5 h-5 rounded-full flex-shrink-0 transition-all"
      :class="modelValue === value
        ? 'bg-indigo-600 shadow-[inset_0_0_0_3px_white,0_0_0_1.5px_#6C7BFF]'
        : 'bg-white shadow-[0_0_0_1.5px_#D1D5DB]'"
    />
  </label>
</template>
