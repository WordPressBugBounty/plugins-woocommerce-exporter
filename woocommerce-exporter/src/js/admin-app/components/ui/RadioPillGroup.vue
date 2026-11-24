<script setup>
import { onBeforeUpdate } from 'vue'

const props = defineProps({
  options: { type: Array, required: true },
  modelValue: { type: String, default: null }
})

const emit = defineEmits(['update:modelValue'])

// A plain array filled by a function ref, indexed by the option position. A
// string ref inside v-for does not keep the order of the source array.
let buttons = []
onBeforeUpdate(() => {
  buttons = []
})

/**
 * The index of the pill that takes the Tab stop: the checked pill, or the
 * first pill when no option is checked (roving tabindex).
 */
function tabStopIndex() {
  const index = props.options.findIndex((opt) => opt.value === props.modelValue)
  return index === -1 ? 0 : index
}

/**
 * Arrow keys, Home and End move the selection, as in a native radio group.
 */
function onKeydown(event, index) {
  const count = props.options.length
  if (!count) {
    return
  }

  let next
  switch (event.key) {
    case 'ArrowRight':
    case 'ArrowDown':
      next = (index + 1) % count
      break
    case 'ArrowLeft':
    case 'ArrowUp':
      next = (index - 1 + count) % count
      break
    case 'Home':
      next = 0
      break
    case 'End':
      next = count - 1
      break
    default:
      return
  }

  event.preventDefault()
  emit('update:modelValue', props.options[next].value)
  buttons[next]?.focus()
}
</script>

<template>
  <div class="inline-flex flex-wrap self-start items-center gap-1 border border-gray-200 rounded-lg p-1" role="radiogroup">
    <button
      v-for="(opt, index) in options"
      :key="opt.value"
      :ref="(el) => { buttons[index] = el }"
      type="button"
      :aria-checked="modelValue === opt.value"
      :tabindex="index === tabStopIndex() ? 0 : -1"
      role="radio"
      class="inline-flex items-center px-3.5 py-2 rounded-md text-[13px] font-medium cursor-pointer transition-colors border-0"
      :class="modelValue === opt.value
        ? 'bg-[#6C7BFF] text-white shadow-sm'
        : 'bg-transparent text-gray-500 hover:text-gray-700'"
      @click="emit('update:modelValue', opt.value)"
      @keydown="onKeydown($event, index)"
    >
      {{ opt.label }}
    </button>
  </div>
</template>
