<script setup>
import { computed } from 'vue'
import { __, sprintf } from '@wordpress/i18n'

const props = defineProps({
  plugin: { type: Object, default: () => ({}) }
})

const versionLabel = computed(() => {
  /* translators: %s: plugin version number */
  return sprintf(__('Version %s', 'woocommerce-exporter'), props.plugin.version)
})
</script>

<template>
  <footer class="flex items-center gap-1.5 text-xs text-gray-400 pt-4 pb-1 border-t border-gray-200 flex-wrap" data-testid="page-footer">
    <span v-if="plugin.version">{{ versionLabel }}</span>
    <span v-if="plugin.version && plugin.load_time" class="text-gray-300">&middot;</span>
    <span v-if="plugin.load_time">{{ plugin.load_time }}s</span>

    <span class="flex-1" />

    <span class="flex items-center gap-[5px]">
      {{ __('Enjoying Store Exporter?', 'woocommerce-exporter') }}
      <a
        href="https://wordpress.org/support/plugin/woocommerce-store-exporter-deluxe/reviews/#new-post"
        target="_blank"
        rel="noopener noreferrer"
        class="text-indigo-600 no-underline hover:underline"
      >{{ __('Leave a review', 'woocommerce-exporter') }}</a>
      <span class="text-amber-500 text-[13px] tracking-[1px]" :aria-label="__('5 stars', 'woocommerce-exporter')">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
    </span>
  </footer>
</template>
