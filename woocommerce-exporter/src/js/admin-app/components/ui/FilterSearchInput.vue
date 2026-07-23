<script setup>
import { Search, X } from 'lucide-vue-next'

defineProps({
  modelValue: { type: String, default: '' },
  placeholder: { type: String, default: 'Search...' }
})

defineEmits(['update:modelValue'])
</script>

<template>
  <div class="wse-ui-filter-search wse-search-field flex items-center gap-2 h-[40px] px-3.5 border border-[#E5E7EB] rounded-lg bg-white focus-within:border-[#7986FF] transition-colors">
    <input
      type="text"
      :value="modelValue"
      :placeholder="placeholder"
      class="flex-1 outline-none text-[14px] text-gray-900 bg-transparent placeholder:text-[#9CA3AF]"
      @input="$emit('update:modelValue', $event.target.value)"
    />
    <button
      v-if="modelValue"
      type="button"
      class="flex-shrink-0 flex items-center justify-center w-4 h-4 rounded-full bg-gray-300 hover:bg-gray-400 transition-colors cursor-pointer border-0 p-0"
      aria-label="Clear search"
      @click="$emit('update:modelValue', '')"
    >
      <X :size="10" color="#fff" :stroke-width="2.5" />
    </button>
    <Search v-else :size="16" color="#9CA3AF" :stroke-width="1.75" class="flex-shrink-0" />
  </div>
</template>
