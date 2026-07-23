<script setup>
import { computed, ref, useAttrs } from 'vue'
import { Copy, RotateCcw } from 'lucide-vue-next'

defineOptions({ inheritAttrs: false })

const props = defineProps({
  modelValue:   { type: String, default: '' },
  tokens:       { type: Array,  default: () => ['{date}', '{time}', '{type}', '{format}', '{store}', '{year}', '{month}', '{day}', '{timestamp}'] },
  placeholder:  { type: String, default: '' },
  /** When provided, a reset button appears and resets modelValue to this string. */
  defaultValue: { type: String, default: null },
})

const emit = defineEmits(['update:modelValue'])

const attrs = useAttrs()
// class/style stay on the wrapper div (the pre-inheritAttrs:false root) so
// consumers styling the whole control aren't affected; everything else
// (e.g. data-testid) forwards to the actual <input>.
const inputAttrs = computed(() => {
  const { class: _class, style: _style, ...rest } = attrs
  return rest
})

const inputEl = ref(null)

function insertToken (token) {
  const el = inputEl.value
  const current = props.modelValue || ''
  const start = el ? el.selectionStart : current.length
  const end   = el ? el.selectionEnd   : current.length
  emit('update:modelValue', current.slice(0, start) + token + current.slice(end))
  const cursor = start + token.length
  if (el) setTimeout(() => { el.focus(); el.setSelectionRange(cursor, cursor) }, 0)
}
</script>

<template>
  <div class="flex flex-col gap-1.5" :class="attrs.class" :style="attrs.style">
    <div class="flex items-center gap-2">
      <input
        ref="inputEl"
        type="text"
        :value="modelValue"
        :placeholder="placeholder"
        class="flex-1 h-9 py-0 px-3 border border-gray-200 rounded-lg text-[13px] text-gray-900 outline-none focus:border-indigo-400 transition-colors bg-white"
        v-bind="inputAttrs"
        @input="emit('update:modelValue', $event.target.value)"
      />
      <button
        type="button"
        class="inline-flex items-center justify-center flex-shrink-0 w-9 h-9 border border-gray-200 rounded-lg bg-white text-gray-400 cursor-pointer hover:border-gray-300 hover:text-gray-600 transition-colors"
        title="Copy filename pattern"
        @click="navigator.clipboard?.writeText(modelValue)"
      >
        <Copy :size="14" :stroke-width="1.75" />
      </button>
      <button
        v-if="defaultValue !== null"
        type="button"
        class="inline-flex items-center justify-center flex-shrink-0 w-9 h-9 border border-gray-200 rounded-lg bg-white text-gray-400 cursor-pointer hover:border-gray-300 hover:text-gray-600 transition-colors disabled:opacity-35 disabled:cursor-not-allowed"
        :disabled="modelValue === defaultValue"
        title="Reset to default filename"
        @click="emit('update:modelValue', defaultValue)"
      >
        <RotateCcw :size="14" :stroke-width="1.75" />
      </button>
    </div>
    <div class="flex flex-wrap gap-1.5">
      <button
        v-for="token in tokens"
        :key="token"
        type="button"
        class="inline-flex items-center py-[3px] px-[7px] bg-gray-100 text-gray-600 text-[11px] rounded-md font-mono cursor-pointer hover:bg-indigo-50 hover:text-indigo-700 border border-gray-200 hover:border-indigo-200 transition-colors leading-none"
        @mousedown.prevent
        @click="insertToken(token)"
      >{{ token }}</button>
    </div>
  </div>
</template>
