<script setup>
import { ref, computed } from 'vue'
import { storeToRefs } from 'pinia'
import { ChevronDown, ChevronRight, ArrowUp, ArrowDown, Search } from 'lucide-vue-next'
import { useQuickExportStore } from '../../../stores/quickExport'
import { useToastsStore } from '../../../stores/toasts'
import { filterIcon, filterIconTint, MONTHS_SHORT } from '../../../views/quickExportConstants'
import { BaseButton, BaseSelect, BaseInfoNotice, BaseCheckbox, FilterTabs, RadioPillGroup } from '../../ui'

const store = useQuickExportStore()
const toasts = useToastsStore()
const {
  selectedType, currentFilters,
  filterValues, filterSubValues, sortField, sortDirection,
  config,
} = storeToRefs(store)

// ── Date formatting ──────────────────────────────────────────────────────────
const wpDateFormat = computed(() => config.value?.dateFormat || 'F j, Y')

const MONTHS_FULL = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']
const DAYS_SHORT  = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
const DAYS_FULL   = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
const pad2 = (n) => String(n).padStart(2, '0')

function formatWPDate (iso, fmt) {
  if (!iso) return ''
  const [yStr, mStr, dStr] = String(iso).split('-')
  const y = parseInt(yStr, 10)
  const mi = parseInt(mStr, 10) - 1
  const d = parseInt(dStr, 10)
  if (Number.isNaN(y) || Number.isNaN(mi) || Number.isNaN(d)) return iso
  const dt = new Date(y, mi, d)
  const dow = dt.getDay()
  const token = (ch) => {
    switch (ch) {
      case 'd': return pad2(d)
      case 'j': return String(d)
      case 'D': return DAYS_SHORT[dow]
      case 'l': return DAYS_FULL[dow]
      case 'N': return String(dow === 0 ? 7 : dow)
      case 'm': return pad2(mi + 1)
      case 'n': return String(mi + 1)
      case 'M': return MONTHS_SHORT[mi]
      case 'F': return MONTHS_FULL[mi]
      case 'Y': return String(y)
      case 'y': return String(y).slice(-2)
      case 'S': {
        if (d >= 11 && d <= 13) return 'th'
        switch (d % 10) {
          case 1: return 'st'
          case 2: return 'nd'
          case 3: return 'rd'
          default: return 'th'
        }
      }
      default: return ch
    }
  }
  let out = ''
  for (let i = 0; i < fmt.length; i++) {
    const ch = fmt[i]
    if (ch === '\\' && i + 1 < fmt.length) { out += fmt[++i]; continue }
    out += token(ch)
  }
  return out
}
function formatDisplayDate (iso) {
  if (!iso) return ''
  return formatWPDate(iso, wpDateFormat.value)
}

// ── Filter card expand/collapse ──────────────────────────────────────────────
const expandedFilterKey = ref(null)
function toggleFilterCard (key) {
  expandedFilterKey.value = expandedFilterKey.value === key ? null : key
}

// ── Filter enable/disable ────────────────────────────────────────────────────
function selectionCount (filter) {
  return store.filterSubSelectionCount(selectedType.value, filter)
}
function isFilterActive (filter) {
  return !!filterValues.value[selectedType.value]?.[filter.key] || selectionCount(filter) > 0
}
function setFilterActive (filter, active) {
  store.setFilterValue(selectedType.value, filter.key, active)
  for (const ctrl of (filter.sub_options ?? [])) {
    if (!active) {
      if (ctrl.type === 'multiselect') store.setFilterSubValue(selectedType.value, filter.key, ctrl.name, [])
      else if (ctrl.type === 'date_range') store.setFilterSubValue(selectedType.value, filter.key, ctrl.name, { from: '', to: '' })
    } else if (ctrl.type === 'multiselect') {
      const current = getSubMultiArray(filter.key, ctrl.name)
      if (current.length === 0) {
        store.setFilterSubValue(selectedType.value, filter.key, ctrl.name, (ctrl.options ?? []).map(o => o.value))
      }
    }
  }
}

// ── Multiselect sub-controls ─────────────────────────────────────────────────
function getSubMultiArray (filterKey, controlName) {
  const v = store.getFilterSubValue(selectedType.value, filterKey, controlName)
  return Array.isArray(v) ? v : []
}
function syncFilterEnabled (filterKey) {
  const subs = filterSubValues.value[selectedType.value]?.[filterKey] ?? {}
  const any = Object.values(subs).some(v => {
    if (Array.isArray(v)) return v.length > 0
    if (v && typeof v === 'object') return !!(v.from || v.to)
    return v !== '' && v !== null && v !== undefined && v !== false
  })
  store.setFilterValue(selectedType.value, filterKey, any)
}
function toggleSubMulti (filterKey, controlName, value) {
  const current = getSubMultiArray(filterKey, controlName)
  const next = current.includes(value) ? current.filter(v => v !== value) : [...current, value]
  store.setFilterSubValue(selectedType.value, filterKey, controlName, next)
  syncFilterEnabled(filterKey)
}
function clearSubMulti (filterKey, controlName) {
  store.setFilterSubValue(selectedType.value, filterKey, controlName, [])
  syncFilterEnabled(filterKey)
}
function selectAllSubMulti (filterKey, controlName, options) {
  store.setFilterSubValue(selectedType.value, filterKey, controlName, (options ?? []).map(o => o.value))
  syncFilterEnabled(filterKey)
}

// ── Radio group sub-controls ─────────────────────────────────────────────────
function getRadioValue (filterKey, controlName, defaultValue) {
  const v = store.getFilterSubValue(selectedType.value, filterKey, controlName)
  return v !== undefined && v !== null ? v : (defaultValue ?? null)
}
function setRadioValue (filterKey, controlName, value, defaultValue) {
  store.setFilterSubValue(selectedType.value, filterKey, controlName, value)
  const isDefault = value === (defaultValue ?? null)
  store.setFilterValue(selectedType.value, filterKey, !isDefault)
}

const multiSearch = ref({})
const multiSearchKey = (fk, cn) => `${fk}::${cn}`
function getMultiSearch (fk, cn) { return multiSearch.value[multiSearchKey(fk, cn)] ?? '' }
function setMultiSearch (fk, cn, v) { multiSearch.value = { ...multiSearch.value, [multiSearchKey(fk, cn)]: v } }
function filteredMultiOptions (fk, cn, options) {
  const q = getMultiSearch(fk, cn).trim().toLowerCase()
  if (!q) return options ?? []
  return (options ?? []).filter(o => String(o.label).toLowerCase().includes(q))
}
const showMultiSearch = (options) => (options?.length ?? 0) > 6

// ── Date range sub-controls ──────────────────────────────────────────────────
function getSubDate (filterKey, controlName, side) {
  const v = store.getFilterSubValue(selectedType.value, filterKey, controlName)
  return (v && typeof v === 'object' && v[side]) ? v[side] : ''
}
function setSubDate (filterKey, controlName, side, value) {
  const v = store.getFilterSubValue(selectedType.value, filterKey, controlName) ?? {}
  store.setFilterSubValue(selectedType.value, filterKey, controlName, { ...v, [side]: value })
  syncFilterEnabled(filterKey)
}
function hasDateRange (filter) {
  return (filter?.sub_options ?? []).some(c => c.type === 'date_range')
}
function dateRangeSummary (filter) {
  if (!filter?.sub_options?.length) return ''
  for (const ctrl of filter.sub_options) {
    if (ctrl.type !== 'date_range') continue
    const from = getSubDate(filter.key, ctrl.name, 'from')
    const to   = getSubDate(filter.key, ctrl.name, 'to')
    if (!from && !to) continue
    if (from && to) return `${formatDisplayDate(from)} – ${formatDisplayDate(to)}`
    return from ? `From ${formatDisplayDate(from)}` : `Until ${formatDisplayDate(to)}`
  }
  return ''
}

// ── Date presets ─────────────────────────────────────────────────────────────
const DATE_PRESETS = [
  { key: 'today',     label: 'Today',        days: 0 },
  { key: 'yesterday', label: 'Yesterday',    days: 1, single: true },
  { key: 'last7',     label: 'Last 7 days',  days: 7 },
  { key: 'last30',    label: 'Last 30 days', days: 30 },
  { key: 'custom',    label: 'Custom range' },
]
function isoDate (d) {
  return `${d.getFullYear()}-${pad2(d.getMonth() + 1)}-${pad2(d.getDate())}`
}
function applyDatePreset (filterKey, controlName, preset) {
  if (preset.key === 'custom') return
  const today = new Date()
  let range
  if (preset.key === 'today') { const iso = isoDate(today); range = { from: iso, to: iso } }
  else if (preset.key === 'yesterday') { const y = new Date(today); y.setDate(today.getDate() - 1); const iso = isoDate(y); range = { from: iso, to: iso } }
  else { const from = new Date(today); from.setDate(today.getDate() - preset.days); range = { from: isoDate(from), to: isoDate(today) } }
  store.setFilterSubValue(selectedType.value, filterKey, controlName, range)
  syncFilterEnabled(filterKey)
}
function matchesPreset (filterKey, controlName, preset) {
  const from = getSubDate(filterKey, controlName, 'from')
  const to   = getSubDate(filterKey, controlName, 'to')
  if (preset.key === 'custom') {
    if (!from && !to) return false
    return !DATE_PRESETS.some(p => p.key !== 'custom' && matchesPreset(filterKey, controlName, p))
  }
  if (!from || !to) return false
  const today = new Date()
  if (preset.key === 'today') { const iso = isoDate(today); return from === iso && to === iso }
  if (preset.key === 'yesterday') { const y = new Date(today); y.setDate(today.getDate() - 1); const iso = isoDate(y); return from === iso && to === iso }
  const start = new Date(today); start.setDate(today.getDate() - preset.days)
  return from === isoDate(start) && to === isoDate(today)
}

// ── Sort field label (for preview table) ─────────────────────────────────────
const sortFieldLabel = computed(() => {
  if (!selectedType.value) return null
  const sf = sortField.value[selectedType.value]
  if (!sf || !currentFilters.value?.sort?.options) return null
  return currentFilters.value.sort.options.find(o => o.value === sf)?.label ?? sf
})
</script>

<template>
  <div class="pt-1 px-[18px] pb-4 border-t border-gray-50">
    <div v-if="currentFilters && currentFilters.loading" class="flex items-center gap-2 text-[12.5px] text-gray-500 py-3">
      <span class="wse-spinner" /> Loading…
    </div>
    <template v-else-if="currentFilters">
      <div class="grid gap-7 mt-2" style="grid-template-columns: minmax(0, 1fr) minmax(0, 1fr)">
        <!-- LEFT: Filters -->
        <div class="min-w-0">
          <h4 class="text-sm font-semibold text-gray-900 m-0 mb-0.5">Filters</h4>
          <p class="text-xs text-gray-500 m-0 mb-3">Choose the data you want to include in your export.</p>

          <div v-if="currentFilters.filters.length" class="flex flex-col gap-2">
            <div
              v-for="filter in currentFilters.filters"
              :key="filter.key"
              class="bg-white rounded-[10px] transition-[border-color,box-shadow]"
              :class="[
                'border-[1.5px]',
                isFilterActive(filter) ? 'border-indigo-600' : 'border-gray-200 hover:border-indigo-200',
                expandedFilterKey === filter.key ? 'shadow-[0_0_0_3px_rgba(108,123,255,0.06)] border-indigo-600' : ''
              ]"
            >
              <div class="flex items-center gap-3 py-3 px-3.5 cursor-pointer" @click="toggleFilterCard(filter.key)">
                <span class="flex items-center flex-shrink-0" @click.stop>
                  <BaseCheckbox
                    :model-value="isFilterActive(filter)"
                    :id="`qev-flt-${filter.key}`"
                    @update:model-value="setFilterActive(filter, $event)"
                  />
                </span>
                <span class="w-[30px] h-[30px] rounded-[7px] flex items-center justify-center flex-shrink-0" :style="{ background: filterIconTint(filter.icon).bg }">
                  <component :is="filterIcon(filter.icon)" :size="15" :stroke-width="1.75" :color="filterIconTint(filter.icon).color" />
                </span>
                <div class="flex flex-col gap-[1px] flex-1 min-w-0">
                  <span class="text-[12.5px] font-semibold text-gray-900">{{ filter.label }}</span>
                  <span v-if="filter.description" class="text-[11.5px] text-gray-500 leading-[1.3]">{{ filter.description }}</span>
                </div>
                <template v-if="hasDateRange(filter) && dateRangeSummary(filter)">
                  <span class="inline-flex items-center gap-2 text-[11.5px] font-medium text-gray-700 bg-gray-100 rounded-lg py-[5px] px-2.5 whitespace-nowrap flex-shrink-0">
                    <span>{{ dateRangeSummary(filter) }}</span>
                  </span>
                </template>
                <span v-else-if="selectionCount(filter) > 0" class="text-[11px] font-medium text-gray-700 bg-gray-100 rounded-[5px] py-[3px] px-2 whitespace-nowrap flex-shrink-0">
                  {{ selectionCount(filter) }} selected
                </span>
                <component :is="expandedFilterKey === filter.key ? ChevronDown : ChevronRight" :size="15" color="#9CA3AF" :stroke-width="2" class="flex-shrink-0" />
              </div>

              <div v-show="expandedFilterKey === filter.key" class="py-3.5 px-3.5">
                <div v-if="!filter.sub_options || !filter.sub_options.length" class="text-xs text-gray-500 pt-2.5">
                  No additional options — enable the checkbox to apply this filter.
                </div>
                <template v-else>
                  <div v-for="ctrl in filter.sub_options" :key="ctrl.name">
                    <label
                      v-if="ctrl.label"
                      class="block mb-2"
                      :class="ctrl.type === 'multiselect' ? 'text-[13.5px] font-semibold text-gray-900' : 'text-[11px] font-bold tracking-[0.06em] text-gray-400 uppercase'"
                    >
                      <template v-if="ctrl.type === 'multiselect'">Select {{ ctrl.label.toLowerCase() }}</template>
                      <template v-else>{{ ctrl.label }}</template>
                    </label>

                    <template v-if="ctrl.type === 'multiselect'">
                      <div v-if="!ctrl.options?.length" class="flex flex-col items-center gap-1 py-7 px-4 border-[1.5px] border-dashed border-gray-200 rounded-[10px] bg-gray-50 text-center">
                        <span class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center mb-1">
                          <component :is="filterIcon(filter.icon)" :size="18" :stroke-width="1.75" :color="filterIconTint(filter.icon).color" />
                        </span>
                        <p class="text-[13px] font-semibold text-gray-900 m-0">No {{ (ctrl.label || 'options').toLowerCase() }} available</p>
                        <p class="text-xs text-gray-500 m-0">There are no {{ (ctrl.label || 'options').toLowerCase() }} to filter by.</p>
                      </div>
                      <template v-else>
                        <div v-if="showMultiSearch(ctrl.options)" class="relative mb-2">
                          <Search :size="13" color="#9CA3AF" :stroke-width="1.75" class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                          <input
                            type="text"
                            class="w-full py-[9px] pr-8 pl-3 border-[1.5px] border-gray-200 rounded-lg text-[12.5px] text-gray-900 bg-white outline-none focus:border-indigo-400 focus:shadow-[0_0_0_3px_#EFF1FF]"
                            :placeholder="`Search ${(ctrl.label || 'options').toLowerCase()}...`"
                            :value="getMultiSearch(filter.key, ctrl.name)"
                            @input="setMultiSearch(filter.key, ctrl.name, $event.target.value)"
                          />
                        </div>
                        <div class="max-h-[240px] overflow-y-auto border-[1.5px] border-gray-200 rounded-[10px] py-1.5 px-3 bg-white">
                          <label
                            v-for="opt in filteredMultiOptions(filter.key, ctrl.name, ctrl.options)"
                            :key="opt.value"
                            class="flex items-center gap-2.5 text-[12.5px] text-gray-900 py-[7px] px-1 cursor-pointer hover:text-gray-700"
                          >
                            <BaseCheckbox :model-value="getSubMultiArray(filter.key, ctrl.name).includes(opt.value)" @update:model-value="toggleSubMulti(filter.key, ctrl.name, opt.value)" />
                            <span>{{ opt.label }}</span>
                          </label>
                          <p v-if="filteredMultiOptions(filter.key, ctrl.name, ctrl.options).length === 0" class="text-[12.5px] text-gray-400 mt-2.5 mb-0">No matches.</p>
                        </div>
                        <div class="flex items-center justify-between mt-2.5">
                          <span class="text-[11.5px] font-medium text-gray-700 bg-gray-100 rounded-[5px] py-1 px-[9px]">
                            {{ getSubMultiArray(filter.key, ctrl.name).length }} {{ (ctrl.label || 'items').toLowerCase() }} selected
                          </span>
                          <div class="flex items-center gap-2.5">
                            <button type="button" class="bg-transparent border-none cursor-pointer text-[12.5px] font-medium text-gray-600 py-1 px-0 hover:text-gray-900 hover:underline" @click="clearSubMulti(filter.key, ctrl.name)">Clear</button>
                            <span class="w-px h-4 bg-gray-200" aria-hidden="true" />
                            <button type="button" class="bg-white border-[1.5px] border-gray-200 rounded-[7px] py-[5px] px-3 text-xs font-semibold text-gray-700 cursor-pointer hover:bg-gray-50 hover:border-gray-300 hover:text-gray-900" @click="selectAllSubMulti(filter.key, ctrl.name, ctrl.options)">Select All</button>
                          </div>
                        </div>
                      </template>
                    </template>

                    <template v-else-if="ctrl.type === 'select'">
                      <BaseSelect
                        variant="form"
                        :options="ctrl.options"
                        :model-value="getRadioValue(filter.key, ctrl.name, ctrl.default)"
                        class="max-w-[400px]"
                        @update:model-value="setRadioValue(filter.key, ctrl.name, $event, ctrl.default)"
                      />
                    </template>

                    <template v-else-if="ctrl.type === 'radio_group'">
                      <RadioPillGroup
                        :options="ctrl.options"
                        :model-value="getRadioValue(filter.key, ctrl.name, ctrl.default)"
                        @update:model-value="setRadioValue(filter.key, ctrl.name, $event, ctrl.default)"
                      />
                    </template>

                    <template v-else-if="ctrl.type === 'date_range'">
                      <div class="grid gap-3.5 mt-1" style="grid-template-columns: 1fr 1fr">
                        <div v-for="side in ['from', 'to']" :key="side" class="flex flex-col gap-1.5">
                          <span class="text-[12.5px] font-medium text-gray-900">{{ side === 'from' ? 'From' : 'To' }}</span>
                          <div
                            class="relative flex items-center gap-2.5 py-2.5 px-3 bg-white border-[1.5px] rounded-[9px] transition-[border-color,box-shadow] hover:border-indigo-200 focus-within:border-indigo-400 focus-within:shadow-[0_0_0_3px_#EFF1FF]"
                            :class="getSubDate(filter.key, ctrl.name, side) ? 'border-gray-300' : 'border-gray-200'"
                          >
                            <span class="flex-shrink-0 z-0 pointer-events-none text-gray-500">
                              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            </span>
                            <span class="flex-1 min-w-0 text-[13px] pointer-events-none whitespace-nowrap overflow-hidden text-ellipsis z-0" :class="getSubDate(filter.key, ctrl.name, side) ? 'text-gray-900' : 'text-gray-400'">
                              {{ formatDisplayDate(getSubDate(filter.key, ctrl.name, side)) || 'Select date' }}
                            </span>
                            <input
                              type="date"
                              :value="getSubDate(filter.key, ctrl.name, side)"
                              :aria-label="side === 'from' ? 'From date' : 'To date'"
                              class="qev-date-native"
                              @input="setSubDate(filter.key, ctrl.name, side, $event.target.value)"
                            />
                            <span v-if="getSubDate(filter.key, ctrl.name, side)" class="relative z-[2] inline-flex items-center gap-2.5 flex-shrink-0 bg-white pl-1">
                              <span class="w-px h-[18px] bg-gray-200" aria-hidden="true" />
                              <button type="button" class="bg-transparent border-none cursor-pointer text-[12.5px] font-medium text-gray-600 p-0 hover:text-gray-900" @click="setSubDate(filter.key, ctrl.name, side, '')">Clear</button>
                            </span>
                          </div>
                        </div>
                      </div>
                      <div class="flex flex-wrap gap-2 mt-3.5">
                        <button
                          v-for="preset in DATE_PRESETS"
                          :key="preset.key"
                          type="button"
                          class="inline-flex items-center gap-1.5 py-[7px] px-3.5 bg-white border-[1.5px] border-gray-200 rounded-lg text-[12.5px] font-medium text-gray-900 cursor-pointer whitespace-nowrap transition-[border-color,background,color] hover:border-indigo-300"
                          :class="matchesPreset(filter.key, ctrl.name, preset) && 'bg-gray-100 border-gray-400 text-gray-900'"
                          @click="applyDatePreset(filter.key, ctrl.name, preset)"
                        >
                          {{ preset.label }}
                        </button>
                      </div>
                      <BaseInfoNotice class="mt-3">Dates are inclusive.</BaseInfoNotice>
                    </template>
                  </div>
                </template>
              </div>
            </div>
          </div>
          <p v-else class="text-[12.5px] text-gray-400 mt-2.5">No filters available for this export type.</p>

          <BaseInfoNotice v-if="currentFilters.filters.length" class="mt-3.5">
            <strong>Tip:</strong> Combine multiple filters to narrow down your export data.
          </BaseInfoNotice>
        </div>

        <!-- RIGHT: Sorting -->
        <div class="min-w-0">
          <h4 class="text-sm font-semibold text-gray-900 m-0 mb-0.5">Sorting</h4>
          <p class="text-xs text-gray-500 m-0 mb-3">Choose how you want your data to be sorted.</p>

          <div v-if="currentFilters.sort" class="bg-white border-[1.5px] border-gray-200 rounded-[10px] p-3.5">
            <BaseSelect
              :model-value="sortField[selectedType] ?? ''"
              label="Sort by"
              :options="[{ value: '', label: 'Default order' }, ...(currentFilters.sort.options ?? [])]"
              clearable
              default-value=""
              @update:model-value="store.setSortField(selectedType, $event)"
            />
            <label class="block text-xs font-semibold text-gray-700 mb-1.5 mt-3.5">Order</label>
            <div class="grid gap-2.5" style="grid-template-columns: 1fr 1fr">
              <button
                type="button"
                class="inline-flex items-center justify-center gap-1.5 h-[50px] px-3.5 bg-white border-[1.5px] border-gray-200 rounded-lg text-[12.5px] font-semibold text-gray-700 cursor-pointer transition-[border-color,background,color] hover:border-indigo-300 disabled:opacity-50 disabled:cursor-not-allowed"
                :class="(sortDirection[selectedType] ?? 'ASC') === 'ASC' && 'border-gray-400 bg-gray-100 text-gray-900'"
                :disabled="!sortField[selectedType]"
                @click="store.setSortDirection(selectedType, 'ASC')"
              >
                <ArrowUp :size="14" :stroke-width="2" />
                Ascending
              </button>
              <button
                type="button"
                class="inline-flex items-center justify-center gap-1.5 h-[50px] px-3.5 bg-white border-[1.5px] border-gray-200 rounded-lg text-[12.5px] font-semibold text-gray-700 cursor-pointer transition-[border-color,background,color] hover:border-indigo-300 disabled:opacity-50 disabled:cursor-not-allowed"
                :class="sortDirection[selectedType] === 'DESC' && 'border-gray-400 bg-gray-100 text-gray-900'"
                :disabled="!sortField[selectedType]"
                @click="store.setSortDirection(selectedType, 'DESC')"
              >
                <ArrowDown :size="14" :stroke-width="2" />
                Descending
              </button>
            </div>
            <BaseInfoNotice class="mt-3.5">Sorting helps organize your data in the exported file.</BaseInfoNotice>
          </div>

          <div v-if="sortFieldLabel" class="mt-4 bg-white border-[1.5px] border-gray-200 rounded-[10px] overflow-hidden">
            <div class="py-2.5 px-3.5 bg-gray-50 text-[12.5px] font-semibold text-gray-900 border-b border-gray-100">Sort Preview</div>
            <table class="w-full border-collapse">
              <thead>
                <tr>
                  <th class="py-2 px-3.5 text-left text-[11px] font-semibold text-gray-500 border-b border-gray-100 bg-white">#</th>
                  <th class="py-2 px-3.5 text-left text-[11px] font-semibold text-gray-500 border-b border-gray-100 bg-white">{{ sortFieldLabel }}</th>
                  <th class="py-2 px-3.5 text-left text-[11px] font-semibold text-gray-500 border-b border-gray-100 bg-white">Preview</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="i in 3" :key="i">
                  <td class="py-2 px-3.5 text-left text-xs text-gray-700 border-b border-gray-100">{{ i }}</td>
                  <td class="py-2 px-3.5 text-left text-xs text-gray-700 border-b border-gray-100">—</td>
                  <td class="py-2 px-3.5 text-left text-xs text-gray-700 border-b border-gray-100">Sample row {{ i }}</td>
                </tr>
              </tbody>
            </table>
            <p class="py-2 px-3.5 text-[11.5px] text-gray-400 m-0 border-t border-gray-100">Preview shows the first 3 rows after sorting.</p>
          </div>
        </div>
      </div>
    </template>

    <div class="flex items-center justify-end gap-2 pt-3.5">
      <button class="wse-qe-step-cancel inline-flex items-center gap-[5px] rounded-lg text-[12.5px] font-semibold py-[7px] px-3.5 cursor-pointer bg-transparent border-[1.5px] border-gray-200 text-gray-700 hover:bg-gray-100" @click="store.setExpandedStep('')">Cancel</button>
      <BaseButton class="wse-qe-filters-continue" @click="store.confirmFilters(); toasts.push('Filters & sorting saved')">
        Continue <ChevronRight :size="13" :stroke-width="2" />
      </BaseButton>
    </div>
  </div>
</template>
