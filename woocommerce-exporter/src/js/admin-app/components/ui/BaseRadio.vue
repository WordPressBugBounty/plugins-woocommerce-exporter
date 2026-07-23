<script setup>
defineProps({
  modelValue: { type: [String, Number, Boolean], default: null },
  value:      { type: [String, Number, Boolean], required: true },
  label:      { type: String,  default: '' },
  disabled:   { type: Boolean, default: false },
  id:         { type: String,  default: null },
  size:       { type: String,  default: 'sm' },  // 'sm' = 16px | 'md' = 20px
  align:      { type: String,  default: 'center' }, // 'center' | 'start'
})
defineEmits(['update:modelValue'])
</script>

<template>
  <!--
    The invisible <input> is rendered last so it sits on top in z-order and
    receives pointer events across the entire label area — not just the 16px
    radio circle. This makes large card-style radio options reliably clickable
    anywhere within the label bounds, not only directly over the dot.
    Note: slot content should not contain interactive children (buttons/links)
    as they would be covered by the full-area input overlay.
  -->
  <label
    class="wse-ui-radio relative inline-flex gap-2.5"
    :class="[
      align === 'start' ? 'items-start' : 'items-center',
      disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'
    ]"
  >
    <span
      class="flex items-center justify-center flex-shrink-0"
      :class="[
        size === 'md' ? 'w-5 h-5' : 'w-4 h-4',
        align === 'start' && 'mt-px'
      ]"
    >
      <span
        class="rounded-full flex items-center justify-center transition-colors"
        :class="[
          size === 'md' ? 'w-5 h-5 border-[1.75px]' : 'w-4 h-4 border-[1.5px]',
          modelValue === value
            ? 'bg-indigo-600 border-indigo-600 shadow-[0_1px_2px_rgba(108,123,255,0.25)]'
            : 'bg-white border-gray-300'
        ]"
      >
        <span
          v-if="modelValue === value"
          class="rounded-full bg-white"
          :class="size === 'md' ? 'w-2 h-2' : 'w-[6px] h-[6px]'"
        />
      </span>
    </span>
    <span v-if="label" class="text-[13px] text-gray-900 select-none leading-snug">{{ label }}</span>
    <slot v-else />
    <input
      type="radio"
      :id="id"
      :value="value"
      :checked="modelValue === value"
      :disabled="disabled"
      class="absolute inset-0 opacity-0 w-full h-full m-0"
      :class="disabled ? 'cursor-not-allowed' : 'cursor-pointer'"
      @change="$emit('update:modelValue', value)"
    />
  </label>
</template>
