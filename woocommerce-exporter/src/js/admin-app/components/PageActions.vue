<script setup>
/**
 * PageActions — global Help button, fixed top-right of the SPA.
 *
 * Reads the current route's meta.helpUrl for the Help button link.
 * To change the help URL for a page, update docLinks.js or meta.helpUrl in router.js.
 */
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { HelpCircle } from 'lucide-vue-next'
import { KB_HUB_URL } from '../docLinks'

const route = useRoute()

const helpUrl = computed(() => route.meta?.helpUrl ?? KB_HUB_URL)
</script>

<template>
  <div class="wse-page-actions">
    <!-- Help button -->
    <a
      :href="helpUrl"
      target="_blank"
      rel="noopener noreferrer"
      class="wse-page-actions__help"
      aria-label="Help documentation"
    >
      <HelpCircle :size="14" :stroke-width="1.75" />
      <span>Help</span>
    </a>
  </div>
</template>

<style lang="scss" scoped>
@use '../../../scss/abstracts' as *;

.wse-page-actions {
  display: flex;
  align-items: center;
  gap: 6px;
}

// ── Help button ───────────────────────────────────────────────────────────────
.wse-page-actions__help {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  height: 36px;
  padding: 0 12px;
  border: 1px solid $gray-300;
  border-radius: $radius-sm;
  background: $color-surface;
  color: $gray-700;
  font-size: $font-size-sm;
  font-weight: 500;
  text-decoration: none;
  cursor: pointer;
  transition: background $transition-fast, border-color $transition-fast, color $transition-fast;
  white-space: nowrap;

  svg { color: $gray-500; flex-shrink: 0; }

  &:hover {
    background: $gray-50;
    border-color: $gray-400;
    color: $gray-900;
  }

  &:focus-visible {
    outline: 2px solid $color-primary-600;
    outline-offset: 2px;
  }
}
</style>
