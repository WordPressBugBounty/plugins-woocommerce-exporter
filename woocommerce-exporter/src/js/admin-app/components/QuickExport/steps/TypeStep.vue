<script setup>
import { ref, computed } from 'vue'
import { storeToRefs } from 'pinia'
import { RefreshCw, Check, Lock } from 'lucide-vue-next'
import { useQuickExportStore } from '../../../stores/quickExport'
import { useToastsStore } from '../../../stores/toasts'
import { useUpsellStore } from '../../../stores/upsell'
import { TYPE_VISUALS, DEFAULT_VISUAL } from '../../../exportTypeVisuals'

const store = useQuickExportStore()
const toasts = useToastsStore()
const upsell = useUpsellStore()
const { config, selectedType } = storeToRefs(store)

// Free export types first, premium (Deluxe-only) after — stable within each group.
const visibleTypes = computed(() =>
  [...(config.value?.types ?? [])].sort((a, b) => (a.premium === b.premium ? 0 : a.premium ? 1 : -1))
)

function onTileClick (type) {
  if (type.premium) {
    upsell.open({ label: type.label, url: type.upsell_url })
    return
  }
  if (type.records > 0) store.selectType(type.slug)
}

const refreshing = ref(false)
async function refreshCounts () {
  refreshing.value = true
  try {
    await store.refreshConfig()
    toasts.push('Record counts refreshed', 'info')
  } catch {
    toasts.push('Failed to refresh counts', 'error')
  } finally {
    refreshing.value = false
  }
}
</script>

<template>
  <div class="pt-1 px-[18px] pb-4 border-t border-gray-50">
    <div class="flex items-center justify-between gap-3 pt-2.5 pb-1">
      <span class="text-[11.5px] text-gray-500">Choose what to export. Records reflect current store data.</span>
      <button
        class="wse-qe-refresh-counts inline-flex items-center gap-[5px] rounded-lg text-[11.5px] font-semibold py-[5px] px-2.5 cursor-pointer bg-transparent border-[1.5px] border-gray-200 text-gray-700 hover:bg-gray-100"
        :disabled="refreshing"
        @click="refreshCounts"
      >
        <RefreshCw :size="12" :stroke-width="2" :class="refreshing && 'animate-spin'" />
        {{ refreshing ? 'Refreshing…' : 'Refresh' }}
      </button>
    </div>
    <div class="grid gap-2 pt-3 pb-1" style="grid-template-columns: repeat(auto-fill, minmax(108px, 1fr))">
      <button
        v-for="type in visibleTypes"
        :key="type.slug"
        class="wse-qe-type-tile relative flex flex-col items-center gap-1.5 py-3 px-2 pb-[10px] border-[1.5px] rounded-[10px] bg-white cursor-pointer text-center transition-[border-color,box-shadow]"
        :class="[
          `wse-qe-type--${type.slug}`,
          type.premium
            ? 'is-premium border-gray-200 bg-gray-50 hover:border-amber-300 hover:shadow-[0_0_0_3px_#FEF3C7]'
            : (type.records === 0 ? 'opacity-45 cursor-not-allowed bg-gray-50 border-gray-200 hover:border-gray-200' : 'hover:border-indigo-300 hover:shadow-[0_0_0_3px_#EFF1FF]'),
          !type.premium && selectedType === type.slug ? 'border-indigo-600 border-2 bg-[#FAFAFE]' : 'border-gray-200'
        ]"
        :disabled="!type.premium && type.records === 0"
        :title="type.premium ? 'Available in Store Exporter Deluxe' : (type.records === 0 ? 'No records available' : '')"
        @click="onTileClick(type)"
      >
        <span v-if="type.premium" class="absolute top-1.5 right-1.5 w-4 h-4 rounded-full bg-amber-100 flex items-center justify-center">
          <Lock :size="9" :stroke-width="2.5" color="#B45309" />
        </span>
        <span v-else-if="selectedType === type.slug" class="absolute top-1.5 right-1.5 w-4 h-4 rounded-full bg-indigo-50 flex items-center justify-center">
          <Check :size="10" :stroke-width="2.5" color="#6C7BFF" />
        </span>
        <span
          class="w-11 h-11 rounded-[9px] flex items-center justify-center"
          :class="type.premium && 'opacity-70'"
          :style="{ background: (TYPE_VISUALS[type.slug] ?? DEFAULT_VISUAL).bgColor }"
        >
          <component
            :is="(TYPE_VISUALS[type.slug] ?? DEFAULT_VISUAL).icon"
            :size="20"
            :color="(TYPE_VISUALS[type.slug] ?? DEFAULT_VISUAL).iconColor"
            :stroke-width="1.75"
          />
        </span>
        <span
          class="text-xs font-semibold leading-[1.3]"
          :class="type.premium ? 'text-gray-600' : (selectedType === type.slug ? 'text-gray-900' : (type.records === 0 ? 'text-gray-400' : 'text-gray-700'))"
        >{{ type.label }}</span>
        <span v-if="type.premium" class="inline-flex items-center gap-[3px] text-[10px] font-semibold text-amber-700 bg-amber-100 rounded-[5px] py-[1px] px-1.5 -mt-0.5">
          Deluxe
        </span>
        <span v-else class="text-[10.5px] text-gray-400 -mt-0.5">{{ type.records.toLocaleString() }} Records</span>
      </button>
    </div>
  </div>
</template>
