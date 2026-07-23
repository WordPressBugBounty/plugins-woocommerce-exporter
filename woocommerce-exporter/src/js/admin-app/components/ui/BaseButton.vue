<script setup>
import { computed } from 'vue'

const props = defineProps({
  variant: { type: String, default: 'primary' }, // primary | secondary | ghost | danger
  size: { type: String, default: 'md' },          // sm | md | lg
  block: { type: Boolean, default: false },
  iconOnly: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  loading: { type: Boolean, default: false },
  type: { type: String, default: 'button' },
  tag: { type: String, default: 'button' }, // button | a
  href: { type: String, default: null }
})

defineEmits(['click'])

const VARIANT_CLASSES = {
  primary: 'bg-indigo-600 text-white hover:bg-indigo-700 border-transparent',
  secondary: 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50',
  ghost: 'bg-transparent text-gray-600 hover:bg-gray-100 hover:text-gray-900 border-transparent',
  danger: 'bg-red-600 text-white hover:bg-red-700 border-transparent',
}

const SIZE_CLASSES = {
  sm: 'px-3 py-1 text-xs',
  md: 'px-5 py-[10px] text-[14px]',
  lg: 'px-5 py-3 text-sm',
}

const classes = computed(() => [
  // Stable, style-free hooks for test targeting (see issue #292).
  'wse-ui-btn',
  `wse-ui-btn--${props.variant}`,
  'inline-flex items-center justify-center gap-1.5 rounded-md font-medium leading-none transition-all cursor-pointer border',
  'focus-visible:outline-none focus-visible:ring-[3px] focus-visible:ring-indigo-500/25',
  VARIANT_CLASSES[props.variant] ?? VARIANT_CLASSES.primary,
  props.iconOnly ? 'p-2' : (SIZE_CLASSES[props.size] ?? SIZE_CLASSES.md),
  props.block && 'w-full justify-center',
  (props.disabled || props.loading) && 'opacity-55 cursor-not-allowed pointer-events-none',
].filter(Boolean))
</script>

<template>
  <component
    :is="tag === 'a' ? 'a' : 'button'"
    :class="classes"
    :type="tag === 'button' ? type : undefined"
    :href="tag === 'a' ? href : undefined"
    :disabled="tag === 'button' ? (disabled || loading) : undefined"
    :aria-busy="loading || undefined"
    @click="$emit('click', $event)"
  >
    <span
      v-if="loading"
      class="inline-block w-3 h-3 border-2 border-current/30 border-t-current rounded-full animate-spin"
      aria-hidden="true"
    />
    <slot v-else name="icon" />
    <slot />
  </component>
</template>
