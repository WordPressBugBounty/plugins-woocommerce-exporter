<script setup>
/**
 * PageShell — root layout wrapper for every admin page.
 *
 * Provides the wse-main flex column with consistent gap and min-width.
 * Feature views should render inside <PageShell> rather than applying
 * wse-main directly, so the shell can be changed in one place.
 */
defineProps({
  tag: { type: String, default: 'main' },
})
</script>

<template>
  <component :is="tag" class="wse-main" data-testid="app-main">
    <slot />
  </component>
</template>
