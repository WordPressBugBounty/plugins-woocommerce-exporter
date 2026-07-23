<script setup>
defineProps({
  modelValue: { type: Boolean, default: false },
  label:      { type: String,  default: '' },
  disabled:   { type: Boolean, default: false },
  id:         { type: String,  default: null },
  size:       { type: String,  default: 'sm' }, // 'sm' = 16px | 'md' = 20px
})
defineEmits(['update:modelValue'])
</script>

<template>
  <label
    class="wse-ui-checkbox inline-flex items-center gap-2 cursor-pointer"
    :class="disabled && 'opacity-50 cursor-not-allowed'"
  >
    <span
      class="relative flex items-center justify-center flex-shrink-0"
      :class="size === 'md' ? 'w-5 h-5' : 'w-4 h-4'"
    >
      <input
        type="checkbox"
        :id="id"
        :checked="modelValue"
        :disabled="disabled"
        class="absolute opacity-0 w-full h-full m-0 cursor-pointer"
        @change="$emit('update:modelValue', $event.target.checked)"
      />
      <span
        class="rounded-[4px] flex items-center justify-center transition-colors"
        :class="[
          size === 'md' ? 'w-5 h-5 border-[1.75px]' : 'w-4 h-4 border-[1.5px]',
          modelValue
            ? 'bg-indigo-600 border-indigo-600 shadow-[0_1px_2px_rgba(108,123,255,0.25)]'
            : 'bg-white border-gray-300'
        ]"
      >
        <template v-if="modelValue">
          <svg v-if="size === 'md'" width="12" height="10" viewBox="0 0 12 10" fill="none">
            <path d="M1.5 5L4.5 8L10.5 1.5" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
          <svg v-else width="9" height="7" viewBox="0 0 9 7" fill="none">
            <path d="M1 3.5L3.5 6L8 1" stroke="#fff" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </template>
      </span>
    </span>
    <span v-if="label" class="text-[13px] text-gray-900 select-none leading-snug">{{ label }}</span>
    <slot v-else />
  </label>
</template>
