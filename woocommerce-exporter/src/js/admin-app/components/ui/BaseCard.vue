<script setup>
defineProps({
  title: { type: String, default: null },
  subtitle: { type: String, default: null },
  compact: { type: Boolean, default: false },
  flush: { type: Boolean, default: false }
})
</script>

<template>
  <section
    class="bg-white border border-gray-200 rounded-lg"
    :class="flush ? 'p-0' : compact ? 'p-3' : 'p-5'"
  >
    <header v-if="title || $slots.header" class="flex items-center justify-between gap-3 mb-4">
      <slot name="header">
        <div>
          <h2 class="m-0 text-base font-semibold text-gray-900">{{ title }}</h2>
          <p v-if="subtitle" class="mt-1 m-0 text-sm text-gray-500">{{ subtitle }}</p>
        </div>
      </slot>
      <slot name="header-actions" />
    </header>

    <div class="text-gray-700">
      <slot />
    </div>

    <footer v-if="$slots.footer" class="mt-4 pt-4 border-t border-gray-200 flex items-center justify-end gap-2">
      <slot name="footer" />
    </footer>
  </section>
</template>
