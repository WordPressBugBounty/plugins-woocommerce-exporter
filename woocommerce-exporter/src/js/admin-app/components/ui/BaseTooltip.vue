<script setup>
import { ref } from 'vue'

defineProps({
  content: { type: String, required: true },
  position: { type: String, default: 'top' }, // 'top' | 'bottom'
})

const visible = ref(false)
</script>

<template>
  <div
    class="relative inline-flex"
    @mouseenter="visible = true"
    @mouseleave="visible = false"
    @focusin="visible = true"
    @focusout="visible = false"
  >
    <slot />
    <Transition name="wse-tooltip">
      <div
        v-if="visible"
        role="tooltip"
        class="wse-tooltip"
        :class="position === 'bottom' ? 'wse-tooltip--bottom' : 'wse-tooltip--top'"
      >
        {{ content }}
        <span class="wse-tooltip__arrow" />
      </div>
    </Transition>
  </div>
</template>
