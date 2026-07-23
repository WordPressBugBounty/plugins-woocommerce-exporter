<script setup>
import { computed } from 'vue'
import { storeToRefs } from 'pinia'
import { Check, FileText, Plus, ChevronRight, Info } from 'lucide-vue-next'
import { useQuickExportStore } from '../../../stores/quickExport'
import { useToastsStore } from '../../../stores/toasts'
import { formatVisual, extraOptionIcon, extraOptionDescription, LIMIT_PRESETS } from '../../../views/quickExportConstants'
import BaseSelect from '../../ui/BaseSelect.vue'
import RadioOptionCard from '../../ui/RadioOptionCard.vue'

const store = useQuickExportStore()
const toasts = useToastsStore()
const {
  selectedType, currentFilters, config,
  exportFormat, exportTemplate,
  extraOptionValues,
} = storeToRefs(store)

const formats   = computed(() => config.value?.formats ?? [])
const templates = computed(() => config.value?.templates ?? [])
const extraOptions = computed(() => currentFilters.value?.extraOptions ?? [])

const limitIsPreset = computed(() => {
  const v = String(store.exportLimit ?? '')
  return LIMIT_PRESETS.some(p => p.value === v)
})
function onLimitSelectChange (value) {
  store.exportLimit = value === '__custom__' ? '1' : value
}

const templateOptions = computed(() => [
  { value: '', label: 'Choose an Export Template…' },
  ...templates.value.map(t => ({ value: String(t.id), label: t.label })),
])

const limitOptions = computed(() => [
  ...LIMIT_PRESETS,
  { value: '__custom__', label: 'Custom…' },
])

function setExtraOption (type, key, value) {
  if (!extraOptionValues.value[type]) extraOptionValues.value[type] = {}
  extraOptionValues.value[type] = { ...extraOptionValues.value[type], [key]: value }
}

const manageTemplatesUrl = computed(() => {
  const base = (window.ajaxurl || '/wp-admin/admin-ajax.php').replace(/admin-ajax\.php.*$/, 'admin.php')
  return `${base}?page=woo_ce&tab=export_template`
})
</script>

<template>
  <div class="pt-1 px-[18px] pb-4 border-t border-gray-50">
    <!-- File Format -->
    <div class="mt-2">
      <h4 class="text-[13.5px] font-semibold text-gray-900 m-0 mb-0.5">File Format</h4>
      <p class="text-xs text-gray-500 m-0 mb-3">Choose the format of the exported file.</p>
      <div class="grid gap-2.5" style="grid-template-columns: repeat(5, 1fr)">
        <button
          v-for="fmt in formats"
          :key="fmt.value"
          type="button"
          class="wse-qe-format-tile relative flex flex-col items-center justify-center gap-2.5 py-[18px] px-3 pb-3.5 bg-white border-[1.5px] border-gray-200 rounded-[10px] cursor-pointer text-center transition-[border-color,box-shadow,background] hover:border-indigo-300"
          :class="[`wse-qe-format--${fmt.value}`, exportFormat === fmt.value && 'border-indigo-600 bg-[#F5F3FF] shadow-[0_0_0_3px_rgba(108,123,255,0.08)]']"
          @click="store.exportFormat = fmt.value"
        >
          <span v-if="exportFormat === fmt.value" class="absolute top-2 right-2 w-[18px] h-[18px] rounded-full bg-indigo-600 flex items-center justify-center">
            <Check :size="11" :stroke-width="2.5" color="#fff" />
          </span>
          <span class="relative w-11 h-11 flex items-center justify-center">
            <component :is="formatVisual(fmt.value).icon" :size="28" :stroke-width="1.5" color="#6C7BFF" />
            <span v-if="formatVisual(fmt.value).badge" class="absolute bottom-1 left-1/2 -translate-x-1/2 text-[8.5px] font-bold text-gray-900 bg-white px-[3px] rounded-[2px] tracking-[0.04em]">{{ formatVisual(fmt.value).badge }}</span>
          </span>
          <span class="text-[12.5px] font-semibold" :class="exportFormat === fmt.value ? 'text-gray-900' : 'text-gray-700'">{{ fmt.label }}</span>
        </button>
      </div>
    </div>

    <!-- Template + Batch Settings -->
    <div class="grid mt-[22px] bg-white border-[1.5px] border-gray-200 rounded-xl overflow-hidden qev-opts-split" style="grid-template-columns: minmax(0, 0.85fr) minmax(0, 1.5fr)">
      <div class="min-w-0 flex flex-col p-5">
        <h4 class="text-sm font-bold text-gray-900 m-0 mb-0.5">Export Template</h4>
        <p class="text-[12.5px] text-gray-500 m-0 mb-3.5">Save time by using or creating a template.</p>
        <template v-if="templates.length">
          <BaseSelect variant="form" :options="templateOptions" v-model="store.exportTemplate" />
          <a class="inline-flex items-center gap-2 mt-3 self-start py-2 px-3.5 border-[1.5px] border-gray-200 rounded-[9px] text-[12.5px] font-medium text-gray-700 bg-white no-underline transition-[background,border-color,color] hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700" :href="manageTemplatesUrl">
            Manage Templates
          </a>
        </template>
        <div v-else class="flex flex-col items-center text-center gap-1.5 py-[22px] px-4 bg-gray-50 border-[1.5px] border-dashed border-gray-300 rounded-[10px]">
          <span class="w-9 h-9 rounded-lg bg-indigo-50 flex items-center justify-center mb-0.5">
            <FileText :size="18" color="#6C7BFF" :stroke-width="1.75" />
          </span>
          <p class="text-[13px] font-semibold text-gray-900 m-0">No templates yet</p>
          <p class="text-xs text-gray-500 m-0">Create a template to reuse this configuration in future exports.</p>
          <a class="inline-flex items-center gap-1.5 mt-2 py-2 px-4 bg-indigo-600 border-none rounded-lg text-[12.5px] font-semibold text-white no-underline cursor-pointer transition-colors hover:bg-indigo-700 hover:text-white" :href="manageTemplatesUrl">
            <Plus :size="13" :stroke-width="2" />
            Create Template
          </a>
        </div>
      </div>
      <div class="min-w-0 flex flex-col p-5 border-l border-l-gray-100">
        <h4 class="text-sm font-bold text-gray-900 m-0 mb-0.5">Batch Settings</h4>
        <p class="text-[12.5px] text-gray-500 m-0 mb-3.5">Control how many records are processed per batch.</p>
        <div class="grid gap-2 items-end" style="grid-template-columns: minmax(0, 3fr) minmax(0, 1fr) minmax(0, 1fr)">
          <div class="flex flex-col gap-1.5 min-w-0">
            <label class="inline-flex items-center gap-[5px] text-[12.5px] font-semibold text-gray-900">Limit <Info :size="11" color="#9CA3AF" :stroke-width="1.75" /></label>
            <BaseSelect
              v-if="limitIsPreset"
              variant="form"
              :options="limitOptions"
              :model-value="String(store.exportLimit ?? '')"
              @update:model-value="onLimitSelectChange"
            />
            <input
              v-else
              v-model="store.exportLimit"
              type="number"
              min="0"
              placeholder="All Records"
              class="w-full py-[10px] px-3.5 border-[1.5px] border-gray-200 rounded-[9px] text-[13px] text-gray-900 outline-none focus:border-indigo-400"
              @blur="store.exportLimit = String(store.exportLimit ?? '').trim()"
            />
          </div>
          <div class="flex flex-col gap-1.5 min-w-0">
            <label class="inline-flex items-center gap-[5px] text-[12.5px] font-semibold text-gray-900">Offset <Info :size="11" color="#9CA3AF" :stroke-width="1.75" /></label>
            <input v-model="store.exportOffset" type="number" min="0" placeholder="0" class="w-full py-[10px] px-3.5 border-[1.5px] border-gray-200 rounded-[9px] text-[13px] text-gray-900 outline-none focus:border-indigo-400" />
          </div>
          <div class="flex flex-col gap-1.5 min-w-0">
            <label class="inline-flex items-center gap-[5px] text-[12.5px] font-semibold text-gray-900">Batch Size <Info :size="11" color="#9CA3AF" :stroke-width="1.75" /></label>
            <input v-model="store.exportBatchSize" type="number" min="1" placeholder="100" class="w-full py-[10px] px-3.5 border-[1.5px] border-gray-200 rounded-[9px] text-[13px] text-gray-900 outline-none focus:border-indigo-400" />
          </div>
        </div>
      </div>
    </div>

    <!-- Extra type-specific options -->
    <div v-for="opt in extraOptions" :key="opt.key" class="mt-[22px]">
      <h4 class="text-[13.5px] font-semibold text-gray-900 m-0 mb-0.5">{{ opt.label }}</h4>
      <p v-if="opt.description" class="text-xs text-gray-500 m-0 mb-3">{{ opt.description }}</p>
      <div class="grid gap-2.5" :class="opt.options.length === 2 ? 'grid-cols-2' : opt.options.length === 3 ? 'grid-cols-3' : 'grid-cols-2'">
        <RadioOptionCard
          v-for="choice in opt.options"
          :key="choice.value"
          :value="String(choice.value)"
          :model-value="String(extraOptionValues[selectedType]?.[opt.key] ?? opt.value)"
          :name="`${selectedType}_${opt.key}`"
          :title="choice.label"
          :description="extraOptionDescription(opt.key, choice.value) || undefined"
          @update:model-value="setExtraOption(selectedType, opt.key, choice.value)"
        >
          <template #icon>
            <component :is="extraOptionIcon(opt.key, choice.value)" :size="16" :stroke-width="1.75" />
          </template>
        </RadioOptionCard>
      </div>
      <div v-if="opt.key === 'product_image_formatting'" class="flex items-center gap-2 mt-3 py-2.5 px-3 bg-indigo-50 rounded-lg text-xs text-indigo-800">
        <Info :size="14" color="#6C7BFF" :stroke-width="1.75" />
        <span>The product featured image is serialized in the export.</span>
      </div>
    </div>

    <div class="flex items-center justify-end gap-2 pt-3.5">
      <button class="wse-qe-step-cancel inline-flex items-center gap-[5px] rounded-lg text-[12.5px] font-semibold py-[7px] px-3.5 cursor-pointer bg-transparent border-[1.5px] border-gray-200 text-gray-700 hover:bg-gray-100" @click="store.setExpandedStep('')">Cancel</button>
      <button class="wse-qe-options-continue inline-flex items-center gap-[5px] rounded-lg text-[12.5px] font-semibold py-[7px] px-3.5 cursor-pointer bg-indigo-600 text-white border-none hover:bg-indigo-700" @click="store.confirmOptions(); toasts.push('Export options saved')">
        Continue <ChevronRight :size="13" :stroke-width="2" />
      </button>
    </div>
  </div>
</template>
