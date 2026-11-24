<script setup>
/**
 * Render a translated sentence that embeds markup — a link, bold text, an
 * icon — without `v-html`. The sentence stays one translatable string, so
 * translators can reorder it, and each numbered placeholder (`%1$s`, `%2$s`)
 * is filled by the slot of the same number:
 *
 *   <I18nText :text="__('Read the %1$s before you %2$s.', 'woocommerce-exporter')">
 *     <template #1><a :href="url">{{ __('guide', 'woocommerce-exporter') }}</a></template>
 *     <template #2><strong>{{ __('export', 'woocommerce-exporter') }}</strong></template>
 *   </I18nText>
 *
 * A bare `%s` is slot 1. A placeholder with no matching slot renders nothing.
 *
 * @since 2.8.3
 */
import { computed } from 'vue'

const props = defineProps({
  text: { type: String, required: true },
})

// `%1$s` / `%s` placeholders and the `%%` escape.
const PLACEHOLDER = /%(?:(\d+)\$)?s|%%/g

const parts = computed(() => {
  const result = []
  let last = 0
  let next = 1
  for (const match of props.text.matchAll(PLACEHOLDER)) {
    result.push({ text: props.text.slice(last, match.index) })
    if (match[0] === '%%') {
      result.push({ text: '%' })
    } else {
      result.push({ slot: String(match[1] ?? next++) })
    }
    last = match.index + match[0].length
  }
  result.push({ text: props.text.slice(last) })
  return result
})
</script>

<template>
  <template v-for="(part, index) in parts" :key="index">
    <slot v-if="part.slot" :name="part.slot" /><template v-else>{{ part.text }}</template>
  </template>
</template>
