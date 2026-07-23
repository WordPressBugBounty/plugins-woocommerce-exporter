<script setup>
import { Info } from 'lucide-vue-next'
</script>

<template>
  <div class="flex items-start gap-3 p-4 bg-indigo-50 rounded-lg">
    <Info :size="18" :stroke-width="1.75" class="flex-shrink-0 text-indigo-700" />
    <span class="m-0 text-xs text-indigo-700 leading-normal">
      <slot />
    </span>
  </div>
</template>
