<script setup>
/**
 * One Custom Meta Fields key in a field picker's meta section.
 *
 * A native button exposed as a checkbox: focusable, toggled by Enter and Space,
 * and announces its label and checked state. Attributes set by the parent
 * (e.g. test hooks) fall through to the button.
 */
defineProps({
  /** Display label for the meta key. */
  label: { type: String, required: true },
  /** Whether the key is ticked. */
  selected: { type: Boolean, default: false }
})

defineEmits(['toggle'])
</script>

<template>
  <button
    type="button"
    role="checkbox"
    :aria-checked="String(selected)"
    class="flex items-center gap-[10px] w-full m-0 py-[7px] pr-4 pl-[38px] bg-transparent border-none appearance-none text-left font-[inherit] cursor-pointer transition-colors hover:bg-gray-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-indigo-600"
    @click="$emit('toggle')"
  >
    <span
      class="w-4 h-4 rounded border flex-shrink-0 flex items-center justify-center transition-colors"
      :class="selected ? 'bg-indigo-600 border-indigo-600' : 'bg-white border-gray-300'"
      aria-hidden="true"
      data-meta-key-glyph
    >
      <svg v-if="selected" width="10" height="8" viewBox="0 0 10 8" fill="none">
        <path d="M1 4l3 3 5-6" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </span>
    <span
      class="text-[12.5px] font-mono"
      :class="selected ? 'text-gray-900 font-medium' : 'text-gray-500'"
      data-meta-key-label
    >{{ label }}</span>
  </button>
</template>
