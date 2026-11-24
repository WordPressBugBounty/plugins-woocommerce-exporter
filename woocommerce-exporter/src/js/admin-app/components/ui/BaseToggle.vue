<script setup>
import { __ } from '@wordpress/i18n'

defineProps({
  modelValue: { type: Boolean, default: false },
  disabled:   { type: Boolean, default: false },
})
defineEmits(['update:modelValue'])
</script>

<template>
  <button
    type="button"
    role="switch"
    :aria-checked="modelValue"
    :aria-label="modelValue ? __('Active — click to pause', 'woocommerce-exporter') : __('Paused — click to activate', 'woocommerce-exporter')"
    :disabled="disabled"
    class="wse-ui-toggle relative h-7 w-[80px] rounded-full border-none cursor-pointer flex-shrink-0 overflow-hidden transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
    :class="modelValue ? 'bg-indigo-600' : 'bg-amber-400'"
    @click="$emit('update:modelValue', !modelValue)"
  >
    <!-- Sliding circle indicator -->
    <span
      class="absolute top-[5px] w-[18px] h-[18px] rounded-full bg-white shadow-md transition-all duration-200"
      :class="modelValue ? 'left-[57px]' : 'left-[5px]'"
    />
    <!-- State label — opposite side from circle -->
    <span
      class="absolute top-1/2 -translate-y-1/2 text-white text-[12px] font-semibold leading-none select-none transition-all duration-200"
      :class="modelValue ? 'left-[10px]' : 'right-[10px]'"
    >{{ modelValue ? __('Active', 'woocommerce-exporter') : __('Pause', 'woocommerce-exporter') }}</span>
  </button>
</template>
