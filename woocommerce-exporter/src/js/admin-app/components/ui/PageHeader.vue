<script setup>
/**
 * PageHeader — standard page header with title, subtitle, and action slot.
 *
 * Usage:
 *   <PageHeader title="Quick Export" subtitle="Run a one-time export.">
 *     <template #actions>
 *       <BaseButton>Run Export</BaseButton>
 *     </template>
 *   </PageHeader>
 */
defineProps({
  title:    { type: String, required: true },
  subtitle: { type: String, default: '' },
})
</script>

<template>
  <div class="wse-header">
    <div>
      <h1 class="wse-page-title">{{ title }}</h1>
      <p v-if="subtitle" class="wse-page-sub">{{ subtitle }}</p>
    </div>
    <div v-if="$slots.actions" class="flex items-center gap-2">
      <slot name="actions" />
    </div>
  </div>
</template>
