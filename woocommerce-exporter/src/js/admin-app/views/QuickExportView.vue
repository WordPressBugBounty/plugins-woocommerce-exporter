<script setup>
import { onMounted, computed } from 'vue'
import { storeToRefs } from 'pinia'
import { useRouter, useRoute } from 'vue-router'
import {
  ChevronDown, ChevronRight,
  Zap, ArrowLeft, Check,
  Database, AlertCircle,
  List, Box, FileText,
  Filter as FilterIcon, ArrowUpDown,
} from 'lucide-vue-next'
import { BaseButton } from '../components/ui/index'
import { useQuickExportStore, STEPS } from '../stores/quickExport'
import { useOverviewStore } from '../stores/overview'
import { useToastsStore } from '../stores/toasts'
import { api } from '../lib/api'
import ManageFieldsPopup from '../components/QuickExport/ManageFieldsPopup.vue'
import ExportProgressDialog from '../components/QuickExport/ExportProgressDialog.vue'
import PageFooter from '../components/PageFooter.vue'
import TypeStep    from '../components/QuickExport/steps/TypeStep.vue'
import FieldsStep  from '../components/QuickExport/steps/FieldsStep.vue'
import FiltersStep from '../components/QuickExport/steps/FiltersStep.vue'
import OptionsStep from '../components/QuickExport/steps/OptionsStep.vue'
import PageActions from '../components/PageActions.vue'

const router = useRouter()
const route  = useRoute()
const store  = useQuickExportStore()
const overviewStore = useOverviewStore()
const toasts = useToastsStore()

const {
  config, configLoading, configError,
  expandedStep, activeStep,
  selectedType, enabledFieldNames,
  currentFields, currentFilters,
  filterValues, sortField, sortDirection,
  exportFormat,
  showManageFields, showExportProgress,
  canRunExport, currentTypeConfig, hasFiltersStep,
} = storeToRefs(store)

// ── Accordion step navigation ────────────────────────────────────────────────
function visibleStepNum (slug) {
  const order = hasFiltersStep.value
    ? ['type', 'fields', 'filters', 'options']
    : ['type', 'fields', 'options']
  const idx = order.indexOf(slug)
  return idx === -1 ? '' : idx + 1
}
function isStepComplete (slug) {
  return STEPS.indexOf(slug) < STEPS.indexOf(activeStep.value)
}
function isStepAccessible () { return true }
function toggleStep (slug) {
  store.setExpandedStep(expandedStep.value === slug ? '' : slug)
}

// ── Summary chips for collapsed accordion headers ────────────────────────────
const typeSummary = computed(() => {
  if (!selectedType.value || !config.value?.types) return null
  return config.value.types.find(t => t.slug === selectedType.value)?.label ?? null
})

const fieldsChips = computed(() => {
  if (!selectedType.value || !currentFields.value?.groups) return []
  const all = []
  currentFields.value.groups.forEach(g => g.fields.forEach(f => all.push(f)))
  const enabled = all.filter(f => store.getFieldEnabled(selectedType.value, f.name))
  const order = store.fieldOrder[selectedType.value]
  if (!order || !order.length) return enabled
  return [...enabled].sort((a, b) => {
    const ai = order.indexOf(a.name), bi = order.indexOf(b.name)
    if (ai === -1 && bi === -1) return 0
    if (ai === -1) return 1; if (bi === -1) return -1
    return ai - bi
  })
})

const activeFilterCount = computed(() => {
  if (!selectedType.value) return 0
  return Object.values(filterValues.value[selectedType.value] ?? {}).filter(v => v && v !== false && v !== '').length
})

const sortFieldLabel = computed(() => {
  if (!selectedType.value) return null
  const sf = sortField.value[selectedType.value]
  if (!sf || !currentFilters.value?.sort?.options) return null
  return currentFilters.value.sort.options.find(o => o.value === sf)?.label ?? sf
})
const sortSummary = computed(() => {
  if (!sortFieldLabel.value) return null
  const dir = sortDirection.value[selectedType.value] ?? 'ASC'
  return `${sortFieldLabel.value} (${dir === 'ASC' ? 'Asc' : 'Desc'})`
})

const formatLabel = computed(() => exportFormat.value?.toUpperCase() ?? 'CSV')

const templateLabel = computed(() => {
  if (!store.exportTemplate || !config.value?.templates) return null
  return config.value.templates.find(t => t.id === Number(store.exportTemplate))?.label ?? null
})

const estimatedRows  = computed(() => currentTypeConfig.value?.records ?? 0)
const selectedHasNoRecords = computed(() => !!currentTypeConfig.value && currentTypeConfig.value.records === 0)

// ── Mount ────────────────────────────────────────────────────────────────────
onMounted(async () => {
  await store.loadConfig()
  if (!overviewStore.data) overviewStore.fetchData()

  const templateId = route.query.template_id
  if (templateId) {
    try {
      const templateData = await api.get(`export-templates/${templateId}`)
      await store.applyTemplate(templateData)
    } catch {
      // Silently fail — Quick Export opens normally without preselection
    }
  }
})
</script>

<template>
  <main class="wse-main wse-quick-export">
    <!-- Page header -->
    <div class="wse-header">
      <div>
        <h1 class="wse-page-title">Quick Export</h1>
        <p class="wse-page-sub">Configure and run a one-time export operation instantly.</p>
      </div>
      <div class="flex items-center gap-2">
        <button class="wse-qe-back inline-flex items-center gap-[5px] bg-transparent border-[1.5px] border-gray-200 rounded-lg text-[12.5px] font-medium text-gray-700 py-[6px] px-3 cursor-pointer hover:bg-gray-100" @click="router.push('/')">
          <ArrowLeft :size="14" :stroke-width="2" />
          Back
        </button>
        <button class="wse-qe-cancel bg-transparent border-[1.5px] border-gray-200 rounded-lg text-[12.5px] font-medium text-gray-700 py-[6px] px-3.5 cursor-pointer hover:bg-gray-100" @click="store.reset(); router.push('/')">
          Cancel
        </button>
        <BaseButton class="wse-qe-run shadow-[0_1px_4px_rgba(108,123,255,0.3)]" :disabled="!canRunExport" @click="store.startExport()">
          <template #icon><Zap :size="13" color="#fff" fill="#fff" :stroke-width="1.75" /></template>
          Run Export
        </BaseButton>
        <PageActions />
      </div>
    </div>

    <!-- Loading / error states -->
    <div v-if="configLoading" class="wse-loading">
      <span class="wse-spinner" /> Loading…
    </div>
    <div v-else-if="configError" class="wse-error">{{ configError }}</div>

    <template v-else>
      <!-- Accordion -->
      <div class="bg-white border border-gray-200 rounded-[10px] overflow-hidden">
        <div class="flex flex-col">

          <!-- ── Step 1: Select Export Type ────────────────────────────────── -->
          <div class="border-b border-gray-100">
            <button
              class="qev-step__hd wse-qe-step wse-qe-step--type relative w-full flex items-center gap-3 py-3.5 px-[18px] bg-white border-none cursor-pointer text-left transition-colors hover:bg-[#FAFAFA]"
              :class="expandedStep === 'type' && 'bg-[#FAFAFA]'"
              :aria-expanded="String(expandedStep === 'type')"
              @click="toggleStep('type')"
            >
              <span class="relative z-[1] w-6 h-6 rounded-full flex items-center justify-center text-[11.5px] font-semibold flex-shrink-0" :class="isStepComplete('type') ? 'bg-indigo-600 border-indigo-600 text-white border-[1.5px]' : 'bg-white border-[1.5px] border-gray-200 text-gray-400'">
                <Check v-if="isStepComplete('type')" :size="11" :stroke-width="2.5" color="#fff" />
                <span v-else>{{ visibleStepNum('type') }}</span>
              </span>
              <span class="w-[34px] h-[34px] rounded-[9px] bg-indigo-50 flex items-center justify-center flex-shrink-0">
                <Box :size="18" color="#6C7BFF" :stroke-width="1.75" />
              </span>
              <div class="flex flex-col gap-[1px] flex-1 min-w-0">
                <span class="text-[13.5px] font-semibold text-gray-900 leading-[1.3]">Select Export Type</span>
                <span class="text-[11.5px] text-gray-400 leading-[1.3]">Choose what you want to export.</span>
              </div>
              <div v-if="typeSummary && expandedStep !== 'type'" class="flex items-center gap-1.5 flex-wrap flex-shrink-0">
                <span class="text-[11.5px] font-medium text-gray-700 bg-gray-100 rounded-[5px] py-[3px] px-2 whitespace-nowrap">{{ typeSummary }}</span>
              </div>
              <component :is="expandedStep === 'type' ? ChevronDown : ChevronRight" :size="15" color="#9CA3AF" :stroke-width="2" />
            </button>
            <TypeStep v-show="expandedStep === 'type'" />
          </div>

          <!-- No-records notice -->
          <div v-if="selectedHasNoRecords" class="flex items-center gap-3 py-3.5 px-4 bg-red-50 border border-red-200 rounded-[10px] my-3 mx-[18px]">
            <AlertCircle :size="18" color="#DC2626" :stroke-width="1.75" />
            <div class="flex-1 min-w-0">
              <p class="text-[13.5px] font-semibold text-red-700 m-0 mb-0.5">No records to export</p>
              <p class="text-[12.5px] text-red-900 m-0">The selected <strong>{{ typeSummary }}</strong> export has 0 records. Pick a different type, or refresh to recheck.</p>
            </div>
          </div>

          <!-- ── Step 2: Export Fields ──────────────────────────────────────── -->
          <div v-show="!selectedHasNoRecords" class="border-b border-gray-100">
            <button
              class="qev-step__hd wse-qe-step wse-qe-step--fields relative w-full flex items-center gap-3 py-3.5 px-[18px] bg-white border-none cursor-pointer text-left transition-colors hover:bg-[#FAFAFA] disabled:opacity-40 disabled:cursor-not-allowed"
              :class="expandedStep === 'fields' && 'bg-[#FAFAFA]'"
              :disabled="!isStepAccessible('fields')"
              :aria-expanded="String(expandedStep === 'fields')"
              @click="toggleStep('fields')"
            >
              <span class="relative z-[1] w-6 h-6 rounded-full flex items-center justify-center text-[11.5px] font-semibold flex-shrink-0" :class="isStepComplete('fields') ? 'bg-indigo-600 border-indigo-600 text-white border-[1.5px]' : 'bg-white border-[1.5px] border-gray-200 text-gray-400'">
                <Check v-if="isStepComplete('fields')" :size="11" :stroke-width="2.5" color="#fff" />
                <span v-else>{{ visibleStepNum('fields') }}</span>
              </span>
              <span class="w-[34px] h-[34px] rounded-[9px] bg-indigo-50 flex items-center justify-center flex-shrink-0">
                <List :size="18" color="#6C7BFF" :stroke-width="1.75" />
              </span>
              <div class="flex flex-col gap-[1px] flex-1 min-w-0">
                <span class="text-[13.5px] font-semibold text-gray-900 leading-[1.3]">Export Fields</span>
                <span class="text-[11.5px] text-gray-400 leading-[1.3]">Select and organize the fields to include in your export.</span>
              </div>
              <div v-if="fieldsChips.length && expandedStep !== 'fields'" class="flex items-center gap-1.5 flex-wrap flex-shrink-0">
                <span class="inline-flex items-center gap-[5px] py-1 px-[9px] text-[11.5px] font-medium text-gray-700 bg-gray-100 rounded-[5px] whitespace-nowrap">
                  <Database :size="11" :stroke-width="2" />
                  {{ fieldsChips.length }} Fields Selected
                </span>
              </div>
              <component :is="expandedStep === 'fields' ? ChevronDown : ChevronRight" :size="15" color="#9CA3AF" :stroke-width="2" />
            </button>
            <FieldsStep v-show="expandedStep === 'fields'" />
          </div>

          <!-- ── Step 3: Filters & Sorting ─────────────────────────────────── -->
          <div v-if="hasFiltersStep" v-show="!selectedHasNoRecords" class="border-b border-gray-100">
            <button
              class="qev-step__hd wse-qe-step wse-qe-step--filters relative w-full flex items-center gap-3 py-3.5 px-[18px] bg-white border-none cursor-pointer text-left transition-colors hover:bg-[#FAFAFA] disabled:opacity-40 disabled:cursor-not-allowed"
              :class="expandedStep === 'filters' && 'bg-[#FAFAFA]'"
              :disabled="!isStepAccessible('filters')"
              :aria-expanded="String(expandedStep === 'filters')"
              @click="toggleStep('filters')"
            >
              <span class="relative z-[1] w-6 h-6 rounded-full flex items-center justify-center text-[11.5px] font-semibold flex-shrink-0" :class="isStepComplete('filters') ? 'bg-indigo-600 border-indigo-600 text-white border-[1.5px]' : 'bg-white border-[1.5px] border-gray-200 text-gray-400'">
                <Check v-if="isStepComplete('filters')" :size="11" :stroke-width="2.5" color="#fff" />
                <span v-else>{{ visibleStepNum('filters') }}</span>
              </span>
              <span class="w-[34px] h-[34px] rounded-[9px] bg-indigo-50 flex items-center justify-center flex-shrink-0">
                <FilterIcon :size="18" color="#6C7BFF" :stroke-width="1.75" />
              </span>
              <div class="flex flex-col gap-[1px] flex-1 min-w-0">
                <span class="text-[13.5px] font-semibold text-gray-900 leading-[1.3]">Filters &amp; Sorting</span>
                <span class="text-[11.5px] text-gray-400 leading-[1.3]">Filter data and sort the results to export exactly what you need.</span>
              </div>
              <div v-if="expandedStep !== 'filters'" class="flex items-center gap-1.5 flex-wrap flex-shrink-0">
                <span v-if="activeFilterCount > 0" class="inline-flex items-center gap-[5px] text-[11.5px] font-medium text-gray-700 bg-gray-100 rounded-[5px] py-[3px] px-2 whitespace-nowrap">
                  <FilterIcon :size="11" :stroke-width="2" />
                  {{ activeFilterCount }} Filter{{ activeFilterCount > 1 ? 's' : '' }}
                </span>
                <span v-if="sortSummary" class="inline-flex items-center gap-[5px] text-[11.5px] font-medium text-gray-700 bg-gray-100 rounded-[5px] py-[3px] px-2 whitespace-nowrap">
                  <ArrowUpDown :size="11" :stroke-width="2" />
                  Sorted by: {{ sortSummary }}
                </span>
              </div>
              <component :is="expandedStep === 'filters' ? ChevronDown : ChevronRight" :size="15" color="#9CA3AF" :stroke-width="2" />
            </button>
            <FiltersStep v-show="expandedStep === 'filters'" />
          </div>

          <!-- ── Step 4: Export Options ─────────────────────────────────────── -->
          <div v-show="!selectedHasNoRecords" class="border-b border-gray-100">
            <button
              class="qev-step__hd wse-qe-step wse-qe-step--options relative w-full flex items-center gap-3 py-3.5 px-[18px] bg-white border-none cursor-pointer text-left transition-colors hover:bg-[#FAFAFA] disabled:opacity-40 disabled:cursor-not-allowed"
              :class="expandedStep === 'options' && 'bg-[#FAFAFA]'"
              :disabled="!isStepAccessible('options')"
              :aria-expanded="String(expandedStep === 'options')"
              @click="toggleStep('options')"
            >
              <span class="relative z-[1] w-6 h-6 rounded-full flex items-center justify-center text-[11.5px] font-semibold flex-shrink-0" :class="isStepComplete('options') ? 'bg-indigo-600 border-indigo-600 text-white border-[1.5px]' : 'bg-white border-[1.5px] border-gray-200 text-gray-400'">
                <Check v-if="isStepComplete('options')" :size="11" :stroke-width="2.5" color="#fff" />
                <span v-else>{{ visibleStepNum('options') }}</span>
              </span>
              <span class="w-[34px] h-[34px] rounded-[9px] bg-indigo-50 flex items-center justify-center flex-shrink-0">
                <FileText :size="18" color="#6C7BFF" :stroke-width="1.75" />
              </span>
              <div class="flex flex-col gap-[1px] flex-1 min-w-0">
                <span class="text-[13.5px] font-semibold text-gray-900 leading-[1.3]">Export Options</span>
                <span class="text-[11.5px] text-gray-400 leading-[1.3]">Choose file format, template and advanced export settings.</span>
              </div>
              <div v-if="expandedStep !== 'options'" class="flex items-center gap-1.5 flex-wrap flex-shrink-0">
                <span class="text-[11.5px] font-medium text-gray-700 bg-gray-100 rounded-[5px] py-[3px] px-2 whitespace-nowrap">Format: {{ formatLabel }}</span>
                <span v-if="templateLabel" class="text-[11.5px] font-medium text-gray-700 bg-gray-100 rounded-[5px] py-[3px] px-2 whitespace-nowrap">Template: {{ templateLabel }}</span>
              </div>
              <component :is="expandedStep === 'options' ? ChevronDown : ChevronRight" :size="15" color="#9CA3AF" :stroke-width="2" />
            </button>
            <OptionsStep v-show="expandedStep === 'options'" />
          </div>

        </div>
      </div>

      <!-- Summary card -->
      <div v-if="selectedType && !selectedHasNoRecords" class="wse-card flex items-center gap-4 flex-wrap py-4 px-5">
        <div class="flex-shrink-0 flex items-center">
          <Zap :size="18" color="#F59E0B" fill="#F59E0B" />
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-[13.5px] font-semibold text-gray-900 m-0 mb-0.5">You're almost ready to export!</p>
          <p class="text-xs text-gray-500 m-0">Review your selections above and run the export.</p>
        </div>
        <div class="flex gap-5 flex-wrap">
          <div
            v-for="stat in [
              { label: 'EXPORT TYPE',    value: typeSummary ?? '—' },
              { label: 'FIELDS',         value: enabledFieldNames.length || '—' },
              { label: 'FILTERS',        value: activeFilterCount },
              { label: 'FORMAT',         value: formatLabel },
              { label: 'ESTIMATED ROWS', value: '~' + estimatedRows.toLocaleString() },
            ]"
            :key="stat.label"
            class="flex flex-col gap-0.5"
          >
            <span class="text-[10px] font-bold tracking-[0.06em] text-gray-400 uppercase">{{ stat.label }}</span>
            <span class="text-[13px] font-semibold text-gray-900">{{ stat.value }}</span>
          </div>
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
          <button class="wse-qe-cancel inline-flex items-center gap-[5px] rounded-lg text-[12.5px] font-semibold py-[7px] px-3.5 cursor-pointer bg-transparent border-[1.5px] border-gray-200 text-gray-700 hover:bg-gray-100" @click="store.reset(); router.push('/')">Cancel</button>
          <BaseButton class="wse-qe-run shadow-[0_1px_4px_rgba(108,123,255,0.3)]" :disabled="!canRunExport" @click="store.startExport()">
            <template #icon><Zap :size="13" color="#fff" fill="#fff" :stroke-width="1.75" /></template>
            Run Export
          </BaseButton>
        </div>
      </div>
    </template>

    <PageFooter :plugin="overviewStore.data?.plugin ?? {}" />

    <ManageFieldsPopup v-if="showManageFields" />
    <ExportProgressDialog v-if="showExportProgress" />
  </main>
</template>
