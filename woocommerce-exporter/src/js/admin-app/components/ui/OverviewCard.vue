<script setup>
import ViewAllButton from './ViewAllButton.vue'

defineProps({
  viewAllUrl: { type: String, default: '' },
  viewAllLabel: { type: String, default: 'View All' },
  subtitle: { type: String, default: '' }
})
</script>

<template>
  <section class="wse-card p-0 flex flex-col">
    <header class="px-5 pt-[18px] pb-3.5 border-b border-gray-100">
      <div class="flex items-center gap-3">
        <span class="w-11 h-11 rounded-[10px] bg-indigo-50 inline-flex items-center justify-center flex-shrink-0">
          <slot name="icon" />
        </span>
        <div class="flex-1 min-w-0">
          <h2 class="text-[18px] font-bold text-gray-900 m-0 leading-tight">
            <slot name="title" />
          </h2>
          <p v-if="subtitle" class="m-0! mt-[0.3em]! text-[13px] text-gray-500 font-normal leading-snug">{{ subtitle }}</p>
        </div>
        <ViewAllButton v-if="viewAllUrl" :to="viewAllUrl" :label="viewAllLabel" />
      </div>
      <div v-if="$slots.badges" class="mt-2.5">
        <slot name="badges" />
      </div>
    </header>

    <div class="px-5 pt-3 pb-[18px] flex-1 flex flex-col min-h-0">
      <slot />
    </div>

    <footer v-if="$slots.footer" class="flex items-center gap-2.5 mx-5 mb-[18px] py-3 px-3.5 bg-violet-50 rounded-[10px] text-[12.5px] text-gray-600">
      <slot name="footer" />
    </footer>
  </section>
</template>
