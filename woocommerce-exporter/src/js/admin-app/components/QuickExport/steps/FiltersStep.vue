<script setup>
import { ref, computed } from 'vue'
import { storeToRefs } from 'pinia'
import { __, _n, sprintf } from '@wordpress/i18n'
import { ChevronDown, ChevronRight, ArrowUp, ArrowDown, Search } from 'lucide-vue-next'
import { useQuickExportStore } from '../../../stores/quickExport'
import { useToastsStore } from '../../../stores/toasts'
import { filterIcon, filterIconTint } from '../../../views/quickExportConstants'
import { formatWpDate, toIsoDate } from '../../../lib/formatWpDate'
import { BaseButton, BaseSelect, BaseInfoNotice, BaseCheckbox, FilterTabs, RadioPillGroup } from '../../ui'

const store = useQuickExportStore()
const toasts = useToastsStore()
const {
  selectedType, currentFilters,
  filterValues, filterSubValues, sortField, sortDirection, sortLevels, sortLevelFieldOptions,
  config,
} = storeToRefs(store)

// ── Date formatting ──────────────────────────────────────────────────────────
const wpDateFormat = computed(() => config.value?.dateFormat || 'F j, Y')

function formatDisplayDate (iso) {
  if (!iso) return ''
  return formatWpDate(iso, wpDateFormat.value)
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
/**
 * Clear one sub-control back to the state an untouched card renders it in.
 *
 * The reset has to reach EVERY control type. A card renders active on
 * `filterValues[key] || filterSubSelectionCount(filter) > 0`, so a sub-value
 * left behind kept the card drawn as on while `filterValues[key]` was already
 * false and buildFormData() skipped the card for being disabled. The next
 * click then re-ran the off branch instead of switching the card back on.
 * Hence the trailing `else`: a control type nobody has written an arm for
 * still gets cleared.
 *
 * @param {Object} filter The filter card definition.
 * @param {Object} ctrl   The sub-control definition to clear.
 */
function resetSubControl (filter, ctrl) {
  if (ctrl.type === 'multiselect') store.setFilterSubValue(selectedType.value, filter.key, ctrl.name, [])
  else if (ctrl.type === 'date_range') store.setFilterSubValue(selectedType.value, filter.key, ctrl.name, { from: '', to: '' })
  else if (ctrl.type === 'text' || ctrl.type === 'number') store.setFilterSubValue(selectedType.value, filter.key, ctrl.name, '')
  else if (ctrl.type === 'comparison_number') {
    // Clear both halves — leaving the operator behind would make the next
    // syncFilterEnabled() call treat the emptied filter as active again.
    // '' rather than null, because getCompOp() uses `||` and relies on the
    // empty string falling through to the operator the card displays.
    store.setFilterSubValue(selectedType.value, filter.key, ctrl.name, '')
    store.setFilterSubValue(selectedType.value, filter.key, ctrl.operator_name, '')
  } else {
    // The catch-all: `select`, `radio_group`, and any type added later.
    // null, not '', so getRadioValue() falls back to the default and the
    // control redisplays whatever an untouched card showed.
    store.setFilterSubValue(selectedType.value, filter.key, ctrl.name, null)
  }
}
/**
 * Switch a filter card on or off, resetting every sub-control on the way off.
 *
 * Switching a card ON seeds an empty multiselect with every option, so that a
 * ticked card narrows nothing until the admin unticks a term. A modifier picked
 * while the card was off — a Match type sitting on Exclude — would turn that
 * seeded list into "exclude everything" and send back an empty export, with
 * nothing on the collapsed card to show why. So a seeded list resets its
 * sibling controls as well, which puts the card in the same state the
 * off-switch leaves it in.
 *
 * @param {Object}  filter The filter card definition.
 * @param {boolean} active Whether the card is being switched on.
 */
function setFilterActive (filter, active) {
  store.setFilterValue(selectedType.value, filter.key, active)
  // Read before the loop seeds anything, or the first multiselect it fills
  // would hide the fact that the list started out empty.
  const seedsEmptyList = active && (filter.sub_options ?? []).some(
    ctrl => ctrl.type === 'multiselect' && getSubMultiArray(filter.key, ctrl.name).length === 0
  )
  for (const ctrl of (filter.sub_options ?? [])) {
    if (!active) {
      resetSubControl(filter, ctrl)
    } else if (ctrl.type === 'multiselect') {
      const current = getSubMultiArray(filter.key, ctrl.name)
      if (current.length === 0) {
        store.setFilterSubValue(selectedType.value, filter.key, ctrl.name, (ctrl.options ?? []).map(o => o.value))
      }
    } else if (seedsEmptyList) {
      resetSubControl(filter, ctrl)
    }
  }
}

// ── Multiselect sub-controls ─────────────────────────────────────────────────
function getSubMultiArray (filterKey, controlName) {
  const v = store.getFilterSubValue(selectedType.value, filterKey, controlName)
  return Array.isArray(v) ? v : []
}
/**
 * Re-derive whether a card counts as on from the sub-values it now holds.
 *
 * Whether a multiselect card is live is decided by the store's
 * `filterHasMultiselectSelection()`, which the "X selected" badge reads too —
 * one copy of the rule, so the tick and the badge cannot disagree.
 *
 * The full `setFilterActive(filter, false)` reset runs only on the ON → OFF
 * transition, i.e. when emptying the list is what switched the card off. A
 * Match type left on Exclude is invisible once the card collapses, and
 * re-ticking the card seeds the empty list with EVERY option, turning the card
 * into "exclude everything" and emptying the export.
 *
 * On a card that was ALREADY off the pick is kept instead. The card body stays
 * rendered while the card is off, so picking Exclude before ticking a term is
 * a normal order to work in — and because setRadioValue() calls through to
 * here, resetting on that path snapped the pill straight back to Include.
 *
 * @param {string} filterKey The card's key.
 */
function syncFilterEnabled (filterKey) {
  const filter = (currentFilters.value?.filters ?? []).find(f => f.key === filterKey)
  const hasMultiSelection = store.filterHasMultiselectSelection(selectedType.value, filter)
  if (hasMultiSelection !== null) {
    if (hasMultiSelection) store.setFilterValue(selectedType.value, filterKey, true)
    else if (filterValues.value[selectedType.value]?.[filterKey]) setFilterActive(filter, false)
    else store.setFilterValue(selectedType.value, filterKey, false)
    return
  }
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
/**
 * Store a `select` / `radio_group` choice and re-derive whether the card is on.
 *
 * Picking the default IS how a card is switched off, so the default is stored
 * as null rather than verbatim. A truthy default — a Match type's '0', a status
 * group's 'all' — otherwise stayed in filterSubSelectionCount(), leaving the
 * card lit and badged while buildFormData() skipped it, and kept
 * syncFilterEnabled() reading a Cleared multiselect as a live filter.
 *
 * The on/off state is then re-derived by syncFilterEnabled() rather than from
 * this pick alone: on a card with a multiselect only the list decides, and on
 * every other card the card's remaining sub-values get a say too.
 *
 * @param {string} filterKey      The card's key.
 * @param {string} controlName    The sub-control's name.
 * @param {string} value          The chosen value.
 * @param {string} [defaultValue] The control's served `default`, if it has one.
 */
function setRadioValue (filterKey, controlName, value, defaultValue) {
  const isDefault = value === (defaultValue ?? null)
  store.setFilterSubValue(selectedType.value, filterKey, controlName, isDefault ? null : value)
  syncFilterEnabled(filterKey)
}

// ── Comparison-number sub-controls (Stock Quantity, …) ───────────────────────
function getCompOp (filterKey, operatorName, firstOperatorValue) {
  const v = store.getFilterSubValue(selectedType.value, filterKey, operatorName)
  // `||`, not `??`: setCompNum stores '' to clear the operator, and `??` would
  // return that empty string instead of falling back to the operator the
  // select is displaying.
  return v || firstOperatorValue || '>'
}
function setCompOp (filterKey, operatorName, value) {
  // Deliberately does NOT sync the enabled state: an operator on its own filters
  // nothing (PHP needs both halves — see setCompNum), so picking one must not
  // light the card up as active.
  store.setFilterSubValue(selectedType.value, filterKey, operatorName, value)
}
function getCompNum (filterKey, controlName) {
  const v = store.getFilterSubValue(selectedType.value, filterKey, controlName)
  return v === undefined || v === null ? '' : String(v)
}
function setCompNum (filterKey, ctrl, value) {
  store.setFilterSubValue(selectedType.value, filterKey, ctrl.name, value)
  // The operator select DISPLAYS operators[0] until the user picks another, but
  // nothing is stored for it. `woo_ce_product_dataset_args()` builds
  // product_quantity by concatenating the operator then the value and yields
  // false when the operator is missing, so a value typed without ever touching
  // the select would silently filter nothing. Persist the displayed default
  // alongside a value, and drop it again when the value is cleared so a stray
  // operator can't leave the filter looking active while filtering nothing.
  store.setFilterSubValue(
    selectedType.value,
    filterKey,
    ctrl.operator_name,
    value === '' ? '' : getCompOp(filterKey, ctrl.operator_name, ctrl.operators?.[0]?.value)
  )
  syncFilterEnabled(filterKey)
}

// ── Text sub-controls (custom-meta value filters, customer/coupon, …) ────────
function getTextValue (filterKey, controlName) {
  const v = store.getFilterSubValue(selectedType.value, filterKey, controlName)
  return typeof v === 'string' ? v : ''
}
function setTextValue (filterKey, controlName, value) {
  store.setFilterSubValue(selectedType.value, filterKey, controlName, value)
  syncFilterEnabled(filterKey)
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
    if (from) {
      /* translators: %s: formatted start date */
      return sprintf(__('From %s', 'woocommerce-exporter'), formatDisplayDate(from))
    }
    /* translators: %s: formatted end date */
    return sprintf(__('Until %s', 'woocommerce-exporter'), formatDisplayDate(to))
  }
  return ''
}

// ── Date presets ─────────────────────────────────────────────────────────────
const DATE_PRESETS = [
  { key: 'today',     label: __('Today', 'woocommerce-exporter'),        days: 0 },
  { key: 'yesterday', label: __('Yesterday', 'woocommerce-exporter'),    days: 1, single: true },
  { key: 'last7',     label: __('Last 7 days', 'woocommerce-exporter'),  days: 7 },
  { key: 'last30',    label: __('Last 30 days', 'woocommerce-exporter'), days: 30 },
  { key: 'custom',    label: __('Custom range', 'woocommerce-exporter') },
]
function applyDatePreset (filterKey, controlName, preset) {
  if (preset.key === 'custom') return
  const today = new Date()
  let range
  if (preset.key === 'today') { const iso = toIsoDate(today); range = { from: iso, to: iso } }
  else if (preset.key === 'yesterday') { const y = new Date(today); y.setDate(today.getDate() - 1); const iso = toIsoDate(y); range = { from: iso, to: iso } }
  else { const from = new Date(today); from.setDate(today.getDate() - preset.days); range = { from: toIsoDate(from), to: toIsoDate(today) } }
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
  if (preset.key === 'today') { const iso = toIsoDate(today); return from === iso && to === iso }
  if (preset.key === 'yesterday') { const y = new Date(today); y.setDate(today.getDate() - 1); const iso = toIsoDate(y); return from === iso && to === iso }
  const start = new Date(today); start.setDate(today.getDate() - preset.days)
  return from === toIsoDate(start) && to === toIsoDate(today)
}

// ── Dynamic labels built around server-provided control labels ───────────────
function selectCtrlLabel (ctrl) {
  /* translators: %s: filter control label, e.g. Categories */
  return sprintf(__('Select %s', 'woocommerce-exporter'), ctrl.label)
}
function noOptionsTitle (ctrl) {
  /* translators: %s: filter control label, e.g. Categories */
  return sprintf(__('No %s available', 'woocommerce-exporter'), ctrl.label || __('options', 'woocommerce-exporter'))
}
function noOptionsText (ctrl) {
  /* translators: %s: filter control label, e.g. Categories */
  return sprintf(__('There are no %s to filter by.', 'woocommerce-exporter'), ctrl.label || __('options', 'woocommerce-exporter'))
}
function searchPlaceholder (ctrl) {
  /* translators: %s: filter control label, e.g. Categories */
  return sprintf(__('Search %s...', 'woocommerce-exporter'), ctrl.label || __('options', 'woocommerce-exporter'))
}
function multiSelectedLabel (filter, ctrl) {
  const count = getSubMultiArray(filter.key, ctrl.name).length
  /* translators: 1: number of selected items, 2: filter control label, e.g. Categories */
  return sprintf(_n('%1$s %2$s selected', '%1$s %2$s selected', count, 'woocommerce-exporter'), count, ctrl.label || __('items', 'woocommerce-exporter'))
}
function selectedCountLabel (filter) {
  const count = selectionCount(filter)
  /* translators: %s: number of selected items */
  return sprintf(_n('%s selected', '%s selected', count, 'woocommerce-exporter'), count)
}
function sampleRowLabel (i) {
  /* translators: %s: sample row number */
  return sprintf(__('Sample row %s', 'woocommerce-exporter'), i)
}

// ── "Then by" level field ────────────────────────────────────────────────────
// A level whose column was deselected after choosing it shows as unset, the
// same way the store leaves it out of the export.
function sortLevelField (idx) {
  const field = sortLevels.value[selectedType.value]?.[idx]?.field ?? ''
  return sortLevelFieldOptions.value.some(o => o.value === field) ? field : ''
}

// ── Sort field label (for preview table) ─────────────────────────────────────
const sortFieldLabel = computed(() => {
  if (!selectedType.value) return null
  const sf = sortField.value[selectedType.value]
  if (!sf || !currentFilters.value?.sort?.options) return null
  return currentFilters.value.sort.options.find(o => o.value === sf)?.label ?? sf
})

// Accessible names for the "Then by" sort level controls.
function thenByAriaLabel (level) {
  /* translators: %d: sort level number, starting at 1 */
  return sprintf(__('Then by, level %d', 'woocommerce-exporter'), level)
}

function directionAriaLabel (level) {
  /* translators: %d: sort level number, starting at 1 */
  return sprintf(__('Direction, level %d', 'woocommerce-exporter'), level)
}
</script>

<template>
  <div class="pt-1 px-[18px] pb-4 border-t border-gray-50">
    <div v-if="currentFilters && currentFilters.loading" class="flex items-center gap-2 text-[12.5px] text-gray-500 py-3">
      <span class="wse-spinner" /> {{ __('Loading…', 'woocommerce-exporter') }}
    </div>
    <template v-else-if="currentFilters">
      <div class="grid gap-7 mt-2" style="grid-template-columns: minmax(0, 1fr) minmax(0, 1fr)">
        <!-- LEFT: Filters -->
        <div class="min-w-0">
          <h4 class="text-sm font-semibold text-gray-900 m-0 mb-0.5">{{ __('Filters', 'woocommerce-exporter') }}</h4>
          <p class="text-xs text-gray-500 m-0 mb-3">{{ __('Choose the data you want to include in your export.', 'woocommerce-exporter') }}</p>

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
                  {{ selectedCountLabel(filter) }}
                </span>
                <component :is="expandedFilterKey === filter.key ? ChevronDown : ChevronRight" :size="15" color="#9CA3AF" :stroke-width="2" class="flex-shrink-0" />
              </div>

              <div v-show="expandedFilterKey === filter.key" class="py-3.5 px-3.5">
                <div v-if="!filter.sub_options || !filter.sub_options.length" class="text-xs text-gray-500 pt-2.5">
                  {{ __('No additional options — enable the checkbox to apply this filter.', 'woocommerce-exporter') }}
                </div>
                <template v-else>
                  <div v-for="ctrl in filter.sub_options" :key="ctrl.name">
                    <label
                      v-if="ctrl.label"
                      class="block mb-2"
                      :class="ctrl.type === 'multiselect' ? 'text-[13.5px] font-semibold text-gray-900' : 'text-[11px] font-bold tracking-[0.06em] text-gray-400 uppercase'"
                    >
                      <template v-if="ctrl.type === 'multiselect'">{{ selectCtrlLabel(ctrl) }}</template>
                      <template v-else>{{ ctrl.label }}</template>
                    </label>

                    <template v-if="ctrl.type === 'multiselect'">
                      <div v-if="!ctrl.options?.length" class="flex flex-col items-center gap-1 py-7 px-4 border-[1.5px] border-dashed border-gray-200 rounded-[10px] bg-gray-50 text-center">
                        <span class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center mb-1">
                          <component :is="filterIcon(filter.icon)" :size="18" :stroke-width="1.75" :color="filterIconTint(filter.icon).color" />
                        </span>
                        <p class="text-[13px] font-semibold text-gray-900 m-0">{{ noOptionsTitle(ctrl) }}</p>
                        <p class="text-xs text-gray-500 m-0">{{ noOptionsText(ctrl) }}</p>
                      </div>
                      <template v-else>
                        <div v-if="showMultiSearch(ctrl.options)" class="relative mb-2">
                          <Search :size="13" color="#9CA3AF" :stroke-width="1.75" class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                          <input
                            type="text"
                            class="w-full py-[9px] pr-8 pl-3 border-[1.5px] border-gray-200 rounded-lg text-[12.5px] text-gray-900 bg-white outline-none focus:border-indigo-400 focus:shadow-[0_0_0_3px_#EFF1FF]"
                            :placeholder="searchPlaceholder(ctrl)"
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
                          <p v-if="filteredMultiOptions(filter.key, ctrl.name, ctrl.options).length === 0" class="text-[12.5px] text-gray-400 mt-2.5 mb-0">{{ __('No matches.', 'woocommerce-exporter') }}</p>
                        </div>
                        <div class="flex items-center justify-between mt-2.5">
                          <span class="text-[11.5px] font-medium text-gray-700 bg-gray-100 rounded-[5px] py-1 px-[9px]">
                            {{ multiSelectedLabel(filter, ctrl) }}
                          </span>
                          <div class="flex items-center gap-2.5">
                            <button type="button" class="bg-transparent border-none cursor-pointer text-[12.5px] font-medium text-gray-600 py-1 px-0 hover:text-gray-900 hover:underline" @click="clearSubMulti(filter.key, ctrl.name)">{{ __('Clear', 'woocommerce-exporter') }}</button>
                            <span class="w-px h-4 bg-gray-200" aria-hidden="true" />
                            <button type="button" class="bg-white border-[1.5px] border-gray-200 rounded-[7px] py-[5px] px-3 text-xs font-semibold text-gray-700 cursor-pointer hover:bg-gray-50 hover:border-gray-300 hover:text-gray-900" @click="selectAllSubMulti(filter.key, ctrl.name, ctrl.options)">{{ __('Select All', 'woocommerce-exporter') }}</button>
                          </div>
                        </div>
                      </template>
                    </template>

                    <template v-else-if="ctrl.type === 'select'">
                      <BaseSelect
                        variant="form"
                        :options="ctrl.options"
                        :model-value="getRadioValue(filter.key, ctrl.name, ctrl.default)"
                        :data-qe-filter-input="ctrl.name"
                        class="max-w-[400px]"
                        @update:model-value="setRadioValue(filter.key, ctrl.name, $event, ctrl.default)"
                      />
                    </template>

                    <template v-else-if="ctrl.type === 'radio_group'">
                      <RadioPillGroup
                        :options="ctrl.options"
                        :model-value="getRadioValue(filter.key, ctrl.name, ctrl.default)"
                        :data-qe-filter-input="ctrl.name"
                        :aria-label="ctrl.label"
                        @update:model-value="setRadioValue(filter.key, ctrl.name, $event, ctrl.default)"
                      />
                    </template>

                    <template v-else-if="ctrl.type === 'number'">
                      <input
                        type="number"
                        min="1"
                        step="1"
                        class="wse-qe-filter-number w-full max-w-[200px] py-[9px] px-3 border-[1.5px] border-gray-200 rounded-lg text-[12.5px] text-gray-900 bg-white outline-none focus:border-indigo-400 focus:shadow-[0_0_0_3px_#EFF1FF]"
                        :data-qe-filter-input="ctrl.name"
                        :aria-label="ctrl.label"
                        :placeholder="ctrl.placeholder || ''"
                        :value="getTextValue(filter.key, ctrl.name)"
                        @input="setTextValue(filter.key, ctrl.name, $event.target.value)"
                      />
                    </template>

                    <template v-else-if="ctrl.type === 'text'">
                      <input
                        type="text"
                        class="wse-qe-filter-text w-full max-w-[400px] py-[9px] px-3 border-[1.5px] border-gray-200 rounded-lg text-[12.5px] text-gray-900 bg-white outline-none focus:border-indigo-400 focus:shadow-[0_0_0_3px_#EFF1FF]"
                        :data-qe-filter-input="ctrl.name"
                        :placeholder="ctrl.placeholder || ''"
                        :value="getTextValue(filter.key, ctrl.name)"
                        @input="setTextValue(filter.key, ctrl.name, $event.target.value)"
                      />
                    </template>

                    <template v-else-if="ctrl.type === 'comparison_number'">
                      <div class="flex items-center gap-2.5">
                        <BaseSelect
                          variant="form"
                          :options="ctrl.operators"
                          :model-value="getCompOp(filter.key, ctrl.operator_name, ctrl.operators?.[0]?.value)"
                          :data-qe-filter-input="ctrl.operator_name"
                          class="w-[170px] flex-shrink-0"
                          @update:model-value="setCompOp(filter.key, ctrl.operator_name, $event)"
                        />
                        <input
                          type="number"
                          class="wse-qe-filter-number w-full max-w-[200px] py-[9px] px-3 border-[1.5px] border-gray-200 rounded-lg text-[12.5px] text-gray-900 bg-white outline-none focus:border-indigo-400 focus:shadow-[0_0_0_3px_#EFF1FF]"
                          :data-qe-filter-input="ctrl.name"
                          placeholder="0"
                          :value="getCompNum(filter.key, ctrl.name)"
                          @input="setCompNum(filter.key, ctrl, $event.target.value)"
                        />
                      </div>
                    </template>

                    <template v-else-if="ctrl.type === 'date_range'">
                      <div class="grid gap-3.5 mt-1" style="grid-template-columns: 1fr 1fr">
                        <div v-for="side in ['from', 'to']" :key="side" class="flex flex-col gap-1.5">
                          <span class="text-[12.5px] font-medium text-gray-900">{{ side === 'from' ? __('From', 'woocommerce-exporter') : __('To', 'woocommerce-exporter') }}</span>
                          <div
                            class="relative flex items-center gap-2.5 py-2.5 px-3 bg-white border-[1.5px] rounded-[9px] transition-[border-color,box-shadow] hover:border-indigo-200 focus-within:border-indigo-400 focus-within:shadow-[0_0_0_3px_#EFF1FF]"
                            :class="getSubDate(filter.key, ctrl.name, side) ? 'border-gray-300' : 'border-gray-200'"
                          >
                            <span class="flex-shrink-0 z-0 pointer-events-none text-gray-500">
                              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            </span>
                            <span class="flex-1 min-w-0 text-[13px] pointer-events-none whitespace-nowrap overflow-hidden text-ellipsis z-0" :class="getSubDate(filter.key, ctrl.name, side) ? 'text-gray-900' : 'text-gray-400'">
                              {{ formatDisplayDate(getSubDate(filter.key, ctrl.name, side)) || __('Select date', 'woocommerce-exporter') }}
                            </span>
                            <input
                              type="date"
                              :value="getSubDate(filter.key, ctrl.name, side)"
                              :aria-label="side === 'from' ? __('From date', 'woocommerce-exporter') : __('To date', 'woocommerce-exporter')"
                              class="qev-date-native"
                              @input="setSubDate(filter.key, ctrl.name, side, $event.target.value)"
                            />
                            <span v-if="getSubDate(filter.key, ctrl.name, side)" class="relative z-[2] inline-flex items-center gap-2.5 flex-shrink-0 bg-white pl-1">
                              <span class="w-px h-[18px] bg-gray-200" aria-hidden="true" />
                              <button type="button" class="bg-transparent border-none cursor-pointer text-[12.5px] font-medium text-gray-600 p-0 hover:text-gray-900" @click="setSubDate(filter.key, ctrl.name, side, '')">{{ __('Clear', 'woocommerce-exporter') }}</button>
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
                      <BaseInfoNotice class="mt-3">{{ __('Dates are inclusive.', 'woocommerce-exporter') }}</BaseInfoNotice>
                    </template>
                  </div>
                </template>
              </div>
            </div>
          </div>
          <p v-else class="text-[12.5px] text-gray-400 mt-2.5">{{ __('No filters available for this export type.', 'woocommerce-exporter') }}</p>

          <BaseInfoNotice v-if="currentFilters.filters.length" class="mt-3.5">
            <strong>{{ __('Tip:', 'woocommerce-exporter') }}</strong> {{ __('Combine multiple filters to narrow down your export data.', 'woocommerce-exporter') }}
          </BaseInfoNotice>
        </div>

        <!-- RIGHT: Sorting -->
        <div class="min-w-0">
          <h4 class="text-sm font-semibold text-gray-900 m-0 mb-0.5">{{ __('Sorting', 'woocommerce-exporter') }}</h4>
          <p class="text-xs text-gray-500 m-0 mb-3">{{ __('Choose how you want your data to be sorted.', 'woocommerce-exporter') }}</p>

          <div v-if="currentFilters.sort" class="bg-white border-[1.5px] border-gray-200 rounded-[10px] p-3.5">
            <BaseSelect
              :model-value="sortField[selectedType] ?? ''"
              :label="__('Sort by', 'woocommerce-exporter')"
              :options="[{ value: '', label: __('Default order', 'woocommerce-exporter') }, ...(currentFilters.sort.options ?? [])]"
              clearable
              default-value=""
              @update:model-value="store.setSortField(selectedType, $event)"
            />
            <label class="block text-xs font-semibold text-gray-700 mb-1.5 mt-3.5">{{ __('Order', 'woocommerce-exporter') }}</label>
            <div class="grid gap-2.5" style="grid-template-columns: 1fr 1fr">
              <button
                type="button"
                class="inline-flex items-center justify-center gap-1.5 h-[50px] px-3.5 bg-white border-[1.5px] border-gray-200 rounded-lg text-[12.5px] font-semibold text-gray-700 cursor-pointer transition-[border-color,background,color] hover:border-indigo-300 disabled:opacity-50 disabled:cursor-not-allowed"
                :class="(sortDirection[selectedType] ?? 'ASC') === 'ASC' && 'border-gray-400 bg-gray-100 text-gray-900'"
                :disabled="!sortField[selectedType]"
                @click="store.setSortDirection(selectedType, 'ASC')"
              >
                <ArrowUp :size="14" :stroke-width="2" />
                {{ __('Ascending', 'woocommerce-exporter') }}
              </button>
              <button
                type="button"
                class="inline-flex items-center justify-center gap-1.5 h-[50px] px-3.5 bg-white border-[1.5px] border-gray-200 rounded-lg text-[12.5px] font-semibold text-gray-700 cursor-pointer transition-[border-color,background,color] hover:border-indigo-300 disabled:opacity-50 disabled:cursor-not-allowed"
                :class="sortDirection[selectedType] === 'DESC' && 'border-gray-400 bg-gray-100 text-gray-900'"
                :disabled="!sortField[selectedType]"
                @click="store.setSortDirection(selectedType, 'DESC')"
              >
                <ArrowDown :size="14" :stroke-width="2" />
                {{ __('Descending', 'woocommerce-exporter') }}
              </button>
            </div>
            <template v-if="currentFilters.sort.levels?.length">
              <div
                v-for="(level, idx) in currentFilters.sort.levels"
                :key="level.field_key"
                :data-testid="`wse-qe-sort-level-${idx}`"
                class="grid gap-2.5 mt-3.5"
                style="grid-template-columns: minmax(0, 2fr) minmax(0, 1fr)"
              >
                <BaseSelect
                  :model-value="sortLevelField(idx)"
                  :label="__('Then by', 'woocommerce-exporter')"
                  :aria-label="thenByAriaLabel(idx + 1)"
                  :options="[{ value: '', label: __('None', 'woocommerce-exporter') }, ...sortLevelFieldOptions]"
                  @update:model-value="store.setSortLevelField(selectedType, idx, $event)"
                />
                <BaseSelect
                  :model-value="sortLevels[selectedType]?.[idx]?.direction ?? 'ASC'"
                  :label="__('Direction', 'woocommerce-exporter')"
                  :aria-label="directionAriaLabel(idx + 1)"
                  :options="[{ value: 'ASC', label: __('Ascending', 'woocommerce-exporter') }, { value: 'DESC', label: __('Descending', 'woocommerce-exporter') }]"
                  :disabled="!sortLevelField(idx)"
                  @update:model-value="store.setSortLevelDirection(selectedType, idx, $event)"
                />
              </div>
              <p class="text-[11.5px] text-gray-500 m-0 mt-2">{{ __('Then by levels only break ties of Sort by, in the order above (with Default order they lead; ID and Random leave no ties).', 'woocommerce-exporter') }}</p>
            </template>
            <BaseInfoNotice class="mt-3.5">{{ __('Sorting helps organize your data in the exported file.', 'woocommerce-exporter') }}</BaseInfoNotice>
          </div>

          <div v-if="sortFieldLabel" class="mt-4 bg-white border-[1.5px] border-gray-200 rounded-[10px] overflow-hidden">
            <div class="py-2.5 px-3.5 bg-gray-50 text-[12.5px] font-semibold text-gray-900 border-b border-gray-100">{{ __('Sort Preview', 'woocommerce-exporter') }}</div>
            <table class="w-full border-collapse">
              <thead>
                <tr>
                  <th class="py-2 px-3.5 text-left text-[11px] font-semibold text-gray-500 border-b border-gray-100 bg-white">#</th>
                  <th class="py-2 px-3.5 text-left text-[11px] font-semibold text-gray-500 border-b border-gray-100 bg-white">{{ sortFieldLabel }}</th>
                  <th class="py-2 px-3.5 text-left text-[11px] font-semibold text-gray-500 border-b border-gray-100 bg-white">{{ __('Preview', 'woocommerce-exporter') }}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="i in 3" :key="i">
                  <td class="py-2 px-3.5 text-left text-xs text-gray-700 border-b border-gray-100">{{ i }}</td>
                  <td class="py-2 px-3.5 text-left text-xs text-gray-700 border-b border-gray-100">—</td>
                  <td class="py-2 px-3.5 text-left text-xs text-gray-700 border-b border-gray-100">{{ sampleRowLabel(i) }}</td>
                </tr>
              </tbody>
            </table>
            <p class="py-2 px-3.5 text-[11.5px] text-gray-400 m-0 border-t border-gray-100">{{ __('Preview shows the first 3 rows after sorting.', 'woocommerce-exporter') }}</p>
          </div>
        </div>
      </div>
    </template>

    <div class="flex items-center justify-end gap-2 pt-3.5">
      <button class="wse-qe-step-cancel inline-flex items-center gap-[5px] rounded-lg text-[12.5px] font-semibold py-[7px] px-3.5 cursor-pointer bg-transparent border-[1.5px] border-gray-200 text-gray-700 hover:bg-gray-100" @click="store.setExpandedStep('')">{{ __('Cancel', 'woocommerce-exporter') }}</button>
      <BaseButton class="wse-qe-filters-continue" @click="store.confirmFilters(); toasts.push(__('Filters & sorting saved', 'woocommerce-exporter'))">
        {{ __('Continue', 'woocommerce-exporter') }} <ChevronRight :size="13" :stroke-width="2" />
      </BaseButton>
    </div>
  </div>
</template>
