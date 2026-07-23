<script setup>
import { onMounted, onBeforeUnmount, watch } from 'vue'
import { X } from 'lucide-vue-next'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  title: { type: String, default: null },
  size: { type: String, default: 'md' }, // sm | md | lg | xl
  closeOnBackdrop: { type: Boolean, default: true }
})

const emit = defineEmits(['update:modelValue', 'close'])

const SIZE_CLASSES = {
  sm: 'max-w-[420px]',
  md: 'max-w-2xl',
  lg: 'max-w-[880px]',
  xl: 'max-w-[1100px]',
}

function close () {
  emit('update:modelValue', false)
  emit('close')
}

function onKey (e) {
  if (e.key === 'Escape' && props.modelValue) close()
}

onMounted(() => document.addEventListener('keydown', onKey))
onBeforeUnmount(() => document.removeEventListener('keydown', onKey))

watch(() => props.modelValue, (open) => {
  document.body.style.overflow = open ? 'hidden' : ''
})
</script>

<template>
  <Teleport to="body">
    <div
      v-if="modelValue"
      class="wse-ui-modal-backdrop fixed inset-0 bg-gray-900/45 flex items-center justify-center z-[1000] p-6"
      role="dialog"
      aria-modal="true"
      @click.self="closeOnBackdrop && close()"
    >
      <div
        class="wse-ui-modal bg-white rounded-xl shadow-lg w-full max-h-[calc(100vh-3rem)] flex flex-col overflow-hidden"
        :class="SIZE_CLASSES[size] ?? SIZE_CLASSES.md"
      >
        <header v-if="title || $slots.header" class="px-5 py-5 border-b border-gray-200 flex items-center justify-between gap-3">
          <slot name="header">
            <h2 class="m-0! text-base font-semibold text-gray-900">{{ title }}</h2>
          </slot>
          <button
            class="wse-ui-modal-close p-1 rounded text-gray-500 hover:bg-gray-100 hover:text-gray-900 border-0 bg-transparent cursor-pointer"
            type="button"
            aria-label="Close"
            @click="close"
          >
            <X :size="18" />
          </button>
        </header>

        <div class="px-5 py-5 overflow-y-auto flex-1">
          <slot />
        </div>

        <footer v-if="$slots.footer" class="px-5 py-4 border-t border-gray-200 bg-gray-50 flex justify-end gap-2">
          <slot name="footer" />
        </footer>
      </div>
    </div>
  </Teleport>
</template>
