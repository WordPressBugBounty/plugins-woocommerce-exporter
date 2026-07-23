<script setup>
import { ArrowRight } from 'lucide-vue-next'

defineProps({
  to: { type: [String, Object], required: true },
  label: { type: String, default: 'View All' }
})
</script>

<template>
  <router-link
    :to="to"
    class="inline-flex items-center gap-1.5 px-3.5 py-[7px] border border-gray-200 rounded-[6px] bg-white text-indigo-600! text-[13px] font-semibold no-underline cursor-pointer whitespace-nowrap flex-shrink-0 transition-colors hover:bg-indigo-50 hover:border-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
  >
    {{ label }} <ArrowRight :size="13" :stroke-width="2" />
  </router-link>
</template>
