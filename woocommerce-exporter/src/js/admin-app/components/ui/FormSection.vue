<script setup>
import { ref } from 'vue'
import { ChevronDown } from 'lucide-vue-next'

const props = defineProps({
  title: { type: String, required: true },
  description: { type: String, default: null },
  collapsible: { type: Boolean, default: false },
  startCollapsed: { type: Boolean, default: false }
})

const collapsed = ref(props.startCollapsed)
</script>

<template>
  <section class="px-6 py-6 border-b border-gray-200 last:border-b-0">
    <div
      class="flex items-start justify-between gap-4 mb-5"
      :class="collapsible && 'cursor-pointer select-none'"
      @click="collapsible && (collapsed = !collapsed)"
    >
      <div class="flex-1 min-w-0">
        <h3
          class="m-0 mb-0.5 text-base font-semibold text-gray-900 transition-colors"
          :class="collapsible && 'hover:text-indigo-600'"
        >{{ title }}</h3>
        <p v-if="description" class="m-0 text-[13px] text-gray-500">{{ description }}</p>
      </div>
      <ChevronDown
        v-if="collapsible"
        :size="16"
        :stroke-width="2"
        class="flex-shrink-0 text-gray-500 mt-0.5 transition-transform duration-120"
        :class="!collapsed && 'rotate-180'"
      />
    </div>
    <div v-show="!collapsed">
      <slot />
    </div>
  </section>
</template>
