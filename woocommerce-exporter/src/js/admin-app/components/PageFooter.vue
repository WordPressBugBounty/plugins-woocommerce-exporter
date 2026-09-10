<script setup>
defineProps({
  plugin: { type: Object, default: () => ({}) }
})

const memoryPercent = (used, limit) => {
  if (!limit) return 0
  return Math.round((used / limit) * 100)
}
</script>

<template>
  <footer class="flex items-center gap-1.5 text-xs text-gray-400 pt-4 pb-1 border-t border-gray-200 flex-wrap" data-testid="page-footer">
    <span v-if="plugin.version">Version {{ plugin.version }}</span>
    <span v-if="plugin.version" class="text-gray-300">&middot;</span>
    <span v-if="plugin.memory_limit">
      Memory: {{ plugin.memory_used }} of {{ plugin.memory_limit }} MB
      ({{ memoryPercent(plugin.memory_used, plugin.memory_limit) }}%)
    </span>
    <span v-if="plugin.load_time" class="text-gray-300">&middot;</span>
    <span v-if="plugin.load_time">{{ plugin.load_time }}s</span>

    <span class="flex-1" />

    <span class="flex items-center gap-[5px]">
      Enjoying Store Exporter?
      <a
        href="https://wordpress.org/support/plugin/woocommerce-store-exporter-deluxe/reviews/#new-post"
        target="_blank"
        rel="noopener noreferrer"
        class="text-indigo-600 no-underline hover:underline"
      >Leave a review</a>
      <span class="text-amber-500 text-[13px] tracking-[1px]" aria-label="5 stars">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
    </span>
  </footer>
</template>
