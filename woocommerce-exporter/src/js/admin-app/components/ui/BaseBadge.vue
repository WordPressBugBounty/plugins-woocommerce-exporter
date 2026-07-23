<script setup>
defineProps({
  variant: { type: String, default: 'gray' }, // 'gray' | 'primary' | 'success' | 'danger' | 'warning'
  size:    { type: String, default: 'md' },   // 'sm' | 'md'
  dot:     { type: Boolean, default: false },
})

const variantClasses = {
  gray:    'bg-gray-100 text-gray-700',
  primary: 'bg-indigo-50 text-indigo-700',
  success: 'bg-green-50 text-green-700',
  danger:  'bg-red-50 text-red-700',
  warning: 'bg-amber-50 text-amber-700',
}
const dotClasses = {
  gray:    'bg-gray-400',
  primary: 'bg-indigo-500',
  success: 'bg-green-500',
  danger:  'bg-red-500',
  warning: 'bg-amber-500',
}
</script>

<template>
  <span
    class="wse-ui-badge inline-flex items-center gap-1.5 font-medium rounded-[5px]"
    :class="[
      variantClasses[variant] ?? variantClasses.gray,
      size === 'sm' ? 'text-[11px] py-[2px] px-[7px]' : 'text-[12px] py-[3px] px-[9px]',
    ]"
  >
    <span v-if="dot" class="w-1.5 h-1.5 rounded-full flex-shrink-0" :class="dotClasses[variant] ?? dotClasses.gray" />
    <slot />
  </span>
</template>
