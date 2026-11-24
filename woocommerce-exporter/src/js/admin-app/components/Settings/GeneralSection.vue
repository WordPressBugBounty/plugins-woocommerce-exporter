<script setup>
import { computed, ref, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { __ } from '@wordpress/i18n'
import { FilePen, FileText, Calendar, Shield, Table2, Database, FileInput, Clock, SlidersHorizontal, BookOpen, X } from 'lucide-vue-next'
import { BaseRadio, BaseSelect, BaseInput, FilenameTokenInput } from '../ui'
import { useSettingsStore } from '../../stores/settings'
import { formatWpDate } from '../../lib/formatWpDate'
import { DEFAULT_FILENAME_PATTERN, SETTINGS_FILENAME_TOKENS, previewFilename } from '../../lib/filenamePattern'

const { settings } = storeToRefs(useSettingsStore())

const filenamePreview = computed(() => previewFilename(settings.value.export_filename))

const PREDEFINED_DATE_FORMATS = ['F j, Y', 'Y/m/d', 'm/d/Y', 'd/m/Y']
const DATE_FORMAT_SAMPLE = '2026-05-16'

const dateFormatOptions = [
  // Each preset labelled with a sample date, month names in the admin's language.
  { value: 'F j, Y', label: formatWpDate(DATE_FORMAT_SAMPLE, 'F j, Y'), hint: 'F j, Y' },
  ...PREDEFINED_DATE_FORMATS.slice(1).map((format) => ({ value: format, label: formatWpDate(DATE_FORMAT_SAMPLE, format), hint: format })),
  { value: 'custom', label: __('Custom', 'woocommerce-exporter'), hint: __('Define your own', 'woocommerce-exporter') }
]

const encodingOptions = [
  { value: 'UTF-8', label: 'UTF-8' },
  { value: 'UTF-16', label: 'UTF-16' },
  { value: 'ISO-8859-1', label: 'ISO-8859-1' },
  { value: 'Windows-1252', label: 'Windows-1252' }
]

const timeoutOptions = [
  { value: '600', label: __('10 minutes', 'woocommerce-exporter') },
  { value: '1800', label: __('30 minutes', 'woocommerce-exporter') },
  { value: '3600', label: __('1 hour', 'woocommerce-exporter') },
  { value: '0', label: __('Unlimited', 'woocommerce-exporter') }
]

const timeoutValue = computed({
  get: () => String(settings.value.timeout),
  set: (val) => { settings.value.timeout = Number(val) }
})

const DATE_TOKENS_DETAILED = [
  { token: 'Y', description: __('4-digit year', 'woocommerce-exporter') },
  { token: 'y', description: __('2-digit year', 'woocommerce-exporter') },
  { token: 'm', description: __('Month (01-12)', 'woocommerce-exporter') },
  { token: 'n', description: __('Month (1-12)', 'woocommerce-exporter') },
  { token: 'd', description: __('Day (01-31)', 'woocommerce-exporter') },
  { token: 'j', description: __('Day (1-31)', 'woocommerce-exporter') },
  { token: 'F', description: __('Month name', 'woocommerce-exporter') },
  { token: 'M', description: __('Short month', 'woocommerce-exporter') },
  { token: 'D', description: __('Short day', 'woocommerce-exporter') },
  { token: 'l', description: __('Full day name', 'woocommerce-exporter') },
  { token: '/', description: __('Slash separator', 'woocommerce-exporter') },
  { token: '-', description: __('Dash separator', 'woocommerce-exporter') },
]

const DATE_FORMAT_EXAMPLES = ['Y-m-d', 'd/m/Y', 'F j, Y', 'M j, Y', 'Ymd']

// Custom date format state — tracks whether custom mode is active and stores the format string
const isCustomDate = ref(!PREDEFINED_DATE_FORMATS.includes(settings.value.date_format))
const customDateFormat = ref(isCustomDate.value ? settings.value.date_format : '')
const customDateInputEl = ref(null)

function handleDateFormatClick (value) {
  if (value === 'custom') {
    isCustomDate.value = true
    settings.value.date_format = customDateFormat.value
  } else {
    isCustomDate.value = false
    settings.value.date_format = value
  }
}

watch(customDateFormat, (val) => {
  if (isCustomDate.value) {
    settings.value.date_format = val
  }
})

watch(() => settings.value.date_format, (newFmt) => {
  if (PREDEFINED_DATE_FORMATS.includes(newFmt)) {
    isCustomDate.value = false
  } else {
    isCustomDate.value = true
    customDateFormat.value = newFmt
  }
})

function insertDateToken (token) {
  const el = customDateInputEl.value
  const current = customDateFormat.value || ''
  const start = el ? el.selectionStart : current.length
  const end = el ? el.selectionEnd : current.length
  customDateFormat.value = current.slice(0, start) + token + current.slice(end)
  const cursor = start + token.length
  if (el) setTimeout(() => { el.focus(); el.setSelectionRange(cursor, cursor) }, 0)
}

// Preview a PHP date format the way the export writes it (mysql2date), with
// month and day names in the admin's language.
function formatPhpDate (fmt) {
  return formatWpDate(new Date(), fmt)
}

const customDatePreview = computed(() => {
  const fmt = customDateFormat.value
  return fmt ? formatPhpDate(fmt) : ''
})

const customDateFull = computed(() => formatPhpDate('l, F j, Y'))

const presetDateFormatted = computed(() => {
  return isCustomDate.value ? '' : formatPhpDate(settings.value.date_format)
})

const presetDateFull = computed(() => {
  return isCustomDate.value ? '' : formatPhpDate('l, F j, Y')
})

const PRESET_TOKEN_LABELS = {
  Y: __('4-digit year', 'woocommerce-exporter'), y: __('2-digit year', 'woocommerce-exporter'),
  m: __('Month (01-12)', 'woocommerce-exporter'), n: __('Month (1-12)', 'woocommerce-exporter'),
  d: __('Day (01-31)', 'woocommerce-exporter'), j: __('Day of the month', 'woocommerce-exporter'),
  F: __('Full month name', 'woocommerce-exporter'), M: __('Short month name', 'woocommerce-exporter'),
  l: __('Full day name', 'woocommerce-exporter'), D: __('Short day name', 'woocommerce-exporter'),
}

const presetFormatBreakdown = computed(() => {
  if (isCustomDate.value) return []
  const fmt = settings.value.date_format
  const now = new Date()
  const items = []
  for (const ch of fmt) {
    if (PRESET_TOKEN_LABELS[ch]) {
      items.push({ token: ch, label: PRESET_TOKEN_LABELS[ch], example: formatWpDate(now, ch) })
    } else if (ch.trim()) {
      items.push({ token: ch, label: __('Separator', 'woocommerce-exporter'), example: ch })
    }
  }
  return items
})
</script>

<template>
  <div class="bg-white border border-gray-200 rounded-[10px] overflow-hidden flex-shrink-0">
    <div class="py-5 px-6 border-b border-gray-100">
      <h2 class="text-[20px] font-bold text-gray-900 m-0 leading-tight">{{ __('General', 'woocommerce-exporter') }}</h2>
      <p class="text-[13px] text-gray-500 m-0 mt-1">{{ __('Configure the basic behavior and default preferences for exports.', 'woocommerce-exporter') }}</p>
    </div>

    <div class="divide-y divide-gray-100">

      <!-- Export Filename -->
      <div class="py-5 px-6">
        <div class="flex items-start gap-3 mb-3">
          <span class="flex-shrink-0 w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center">
            <FilePen :size="15" :stroke-width="1.75" color="#6C7BFF" />
          </span>
          <div>
            <p class="text-[13px] font-semibold text-gray-900 m-0 mb-0.5">{{ __('Export Filename', 'woocommerce-exporter') }}</p>
            <p class="text-[12px] text-gray-400 m-0">{{ __('Set the default filename for exported files.', 'woocommerce-exporter') }}</p>
          </div>
        </div>
        <FilenameTokenInput
          v-model="settings.export_filename"
          data-testid="wse-settings-export-filename-input"
          :tokens="SETTINGS_FILENAME_TOKENS"
          :default-value="DEFAULT_FILENAME_PATTERN"
          :placeholder="DEFAULT_FILENAME_PATTERN"
        />
        <div v-if="filenamePreview" class="flex items-center gap-2 mt-1.5">
          <span class="text-[11px] text-gray-400 font-medium shrink-0">{{ __('Live Preview', 'woocommerce-exporter') }}</span>
          <code class="text-[11px] text-gray-600 bg-gray-50 border border-gray-200 rounded px-2 py-0.5 font-mono truncate">{{ filenamePreview }}.csv</code>
        </div>
      </div>

      <!-- Character Encoding -->
      <div class="py-5 px-6">
        <div class="flex items-start gap-3 mb-4">
          <span class="flex-shrink-0 w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center">
            <FileText :size="15" :stroke-width="1.75" color="#6C7BFF" />
          </span>
          <div>
            <p class="text-[13px] font-semibold text-gray-900 m-0 mb-0.5">{{ __('Character Encoding', 'woocommerce-exporter') }}</p>
            <p class="text-[12px] text-gray-400 m-0">{{ __('Choose the character encoding for export files.', 'woocommerce-exporter') }}</p>
          </div>
        </div>
        <BaseSelect v-model="settings.encoding" :options="encodingOptions" />
      </div>

      <!-- Date Format -->
      <div class="py-5 px-6">
        <div class="flex items-start gap-3 mb-4">
          <span class="flex-shrink-0 w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center">
            <Calendar :size="15" :stroke-width="1.75" color="#6C7BFF" />
          </span>
          <div>
            <p class="text-[13px] font-semibold text-gray-900 m-0 mb-0.5">{{ __('Date Format', 'woocommerce-exporter') }}</p>
            <p class="text-[12px] text-gray-400 m-0">{{ __('Choose the date format to use in exports.', 'woocommerce-exporter') }}</p>
          </div>
        </div>

        <!-- Format picker — segmented control -->
        <div class="grid grid-cols-5 gap-1 bg-gray-100 rounded-xl p-1">
          <button
            v-for="opt in dateFormatOptions"
            :key="opt.value"
            type="button"
            class="py-2.5 px-2 rounded-lg text-center transition-all"
            :class="(opt.value === 'custom' ? isCustomDate : (!isCustomDate && settings.date_format === opt.value))
              ? 'bg-white shadow-sm'
              : 'hover:bg-white/60'"
            @click="handleDateFormatClick(opt.value)"
          >
            <span
              class="block text-[13px] font-semibold leading-snug truncate"
              :class="(opt.value === 'custom' ? isCustomDate : (!isCustomDate && settings.date_format === opt.value)) ? 'text-indigo-600' : 'text-gray-500'"
            >{{ opt.label }}</span>
            <span
              class="block text-[11px] mt-0.5 truncate"
              :class="(opt.value === 'custom' ? isCustomDate : (!isCustomDate && settings.date_format === opt.value)) ? 'text-indigo-400' : 'text-gray-400'"
            >{{ opt.hint }}</span>
          </button>
        </div>

        <!-- Preset format preview -->
        <div v-if="!isCustomDate && settings.date_format" class="mt-3 bg-indigo-50 rounded-xl px-5 py-4">
          <div class="flex items-center gap-3 mb-3.5">
            <span class="flex-shrink-0 w-9 h-9 bg-white rounded-lg shadow-sm flex items-center justify-center">
              <Calendar :size="16" :stroke-width="1.75" color="#6C7BFF" />
            </span>
            <div>
              <p class="text-[20px] font-bold text-gray-900 m-0 leading-tight">{{ presetDateFormatted }}</p>
              <p class="text-[12px] text-gray-400 m-0 mt-0.5">{{ presetDateFull }}</p>
            </div>
          </div>
          <div class="flex flex-wrap gap-1.5">
            <span
              v-for="(item, i) in presetFormatBreakdown"
              :key="i"
              class="inline-flex items-center gap-1.5 bg-white rounded-lg px-3 py-1.5 border border-indigo-100"
            >
              <span class="text-[12px] font-bold text-indigo-600 font-mono leading-none">{{ item.token }}</span>
              <span class="text-[11px] text-gray-500 leading-none">{{ item.label }}</span>
            </span>
          </div>
        </div>

        <!-- Custom format panel -->
        <div v-if="isCustomDate" class="mt-3">
          <!-- Input -->
          <div class="relative flex items-center border rounded-lg bg-white transition-colors focus-within:border-indigo-500 border-gray-200">
            <input
              ref="customDateInputEl"
              v-model="customDateFormat"
              type="text"
              :placeholder="__('e.g. Y-m-d H:i', 'woocommerce-exporter')"
              class="flex-1 h-[40px] pl-3.5 pr-10 text-[14px] text-gray-900 placeholder:text-gray-400 bg-transparent outline-none"
            />
            <button
              v-if="customDateFormat"
              type="button"
              class="absolute right-3 flex items-center justify-center w-4 h-4 rounded-full bg-gray-300 hover:bg-gray-400 transition-colors cursor-pointer border-0 p-0"
              :aria-label="__('Clear format', 'woocommerce-exporter')"
              @click="customDateFormat = ''"
            >
              <X :size="10" color="#fff" :stroke-width="2.5" />
            </button>
            <span v-else class="absolute right-3 text-[12px] font-semibold text-gray-300 font-mono pointer-events-none select-none">{}</span>
          </div>

          <!-- Preview — matches preset style -->
          <div v-if="customDatePreview" class="mt-3 bg-indigo-50 rounded-xl px-5 py-4">
            <div class="flex items-center gap-3">
              <span class="flex-shrink-0 w-9 h-9 bg-white rounded-lg shadow-sm flex items-center justify-center">
                <Calendar :size="16" :stroke-width="1.75" color="#6C7BFF" />
              </span>
              <div>
                <p class="text-[20px] font-bold text-gray-900 m-0 leading-tight">{{ customDatePreview }}</p>
                <p class="text-[12px] text-gray-400 m-0 mt-0.5">{{ customDateFull }}</p>
              </div>
            </div>
          </div>

          <!-- Token chips -->
          <div class="flex flex-wrap gap-1.5 mt-3">
            <button
              v-for="item in DATE_TOKENS_DETAILED"
              :key="item.token"
              type="button"
              class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-md cursor-pointer transition-colors hover:bg-indigo-50 hover:border-indigo-200 hover:text-indigo-600"
              @mousedown.prevent
              @click="insertDateToken(item.token)"
            >
              <span class="text-[12px] font-bold text-indigo-600 font-mono leading-none">{{ item.token }}</span>
              <span class="text-[11px] text-gray-500 leading-none">{{ item.description }}</span>
            </button>
          </div>

          <!-- Quick examples -->
          <div class="flex items-center gap-1.5 mt-3 flex-wrap">
            <span class="text-[11px] text-gray-400">{{ __('Try:', 'woocommerce-exporter') }}</span>
            <button
              v-for="example in DATE_FORMAT_EXAMPLES"
              :key="example"
              type="button"
              class="text-[11px] font-mono text-indigo-500 px-2 py-1 bg-indigo-50 rounded hover:bg-indigo-100 hover:text-indigo-700 transition-colors"
              @click="customDateFormat = example"
            >{{ example }}</button>
          </div>
        </div>

        <!-- Documentation link -->
        <a
          href="https://www.php.net/manual/en/datetime.format.php"
          target="_blank"
          rel="noopener noreferrer"
          class="inline-flex items-center gap-1.5 mt-3.5 text-[13px] text-indigo-600 no-underline hover:underline"
        >
          <BookOpen :size="13" :stroke-width="1.75" />
          {{ __('View formatting documentation', 'woocommerce-exporter') }}
          <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
        </a>
      </div>

      <!-- Field Escape Formatting -->
      <div class="py-5 px-6">
        <div class="flex items-start gap-3 mb-3">
          <span class="flex-shrink-0 w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center">
            <Shield :size="15" :stroke-width="1.75" color="#6C7BFF" />
          </span>
          <div>
            <p class="text-[13px] font-semibold text-gray-900 m-0 mb-0.5">{{ __('Field Escape Formatting', 'woocommerce-exporter') }}</p>
            <p class="text-[12px] text-gray-400 m-0">{{ __('Controls how cells are quoted in CSV and TSV exports. Excel formats (XLS, XLSX) are not affected.', 'woocommerce-exporter') }}</p>
          </div>
        </div>
        <div class="flex flex-col gap-2 pl-11">
          <BaseRadio v-model="settings.escape_formatting" value="all" align="start">
            <span>
              <strong class="block text-[13px] text-gray-900">{{ __('Escape all cells', 'woocommerce-exporter') }}</strong>
              <span class="text-[12px] text-gray-400">{{ __('Wrap every cell in quotes, so field data is written exactly as it is saved in the database.', 'woocommerce-exporter') }}</span>
            </span>
          </BaseRadio>
          <BaseRadio v-model="settings.escape_formatting" value="excel" align="start">
            <span>
              <strong class="block text-[13px] text-gray-900">{{ __('Escape cells as Excel would', 'woocommerce-exporter') }}</strong>
              <span class="text-[12px] text-gray-400">{{ __('Quote a cell only where a delimiter, quote or line break makes it necessary.', 'woocommerce-exporter') }}</span>
            </span>
          </BaseRadio>
          <BaseRadio v-model="settings.escape_formatting" value="none" align="start">
            <span>
              <strong class="block text-[13px] text-gray-900">{{ __('Do not escape any cells', 'woocommerce-exporter') }}</strong>
              <span class="text-[12px] text-gray-400">{{ __('Quote nothing, except a field containing a line break. Caution: this corrupts any field containing the delimiter character.', 'woocommerce-exporter') }}</span>
            </span>
          </BaseRadio>
        </div>
      </div>

      <!-- Header Formatting | WordPress Object Cache (2-col) -->
      <div class="grid" style="grid-template-columns: 1fr 1fr">
        <div class="py-5 px-6">
          <div class="flex items-start gap-3 mb-3">
            <span class="flex-shrink-0 w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center">
              <Table2 :size="15" :stroke-width="1.75" color="#6C7BFF" />
            </span>
            <div>
              <p class="text-[13px] font-semibold text-gray-900 m-0 mb-0.5">{{ __('Header Formatting', 'woocommerce-exporter') }}</p>
              <p class="text-[12px] text-gray-400 m-0">{{ __('Include or exclude column headers in exports.', 'woocommerce-exporter') }}</p>
            </div>
          </div>
          <div class="flex flex-col gap-2 pl-11">
            <BaseRadio v-model="settings.header_formatting" value="include" :label="__('Include column headers', 'woocommerce-exporter')" />
            <BaseRadio v-model="settings.header_formatting" value="exclude" :label="__('Exclude column headers', 'woocommerce-exporter')" />
          </div>
        </div>

        <div class="py-5 px-6 border-l border-gray-100">
          <div class="flex items-start gap-3 mb-3">
            <span class="flex-shrink-0 w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center">
              <Database :size="15" :stroke-width="1.75" color="#6C7BFF" />
            </span>
            <div>
              <p class="text-[13px] font-semibold text-gray-900 m-0 mb-0.5">{{ __('WordPress Object Cache', 'woocommerce-exporter') }}</p>
              <p class="text-[12px] text-gray-400 m-0">{{ __('Flush the cache before each export.', 'woocommerce-exporter') }}</p>
            </div>
          </div>
          <div class="flex flex-col gap-2 pl-11">
            <BaseRadio v-model="settings.flush_cache" value="flush" :label="__('Flush before each export', 'woocommerce-exporter')" />
            <BaseRadio v-model="settings.flush_cache" value="none" :label="__('Do not flush', 'woocommerce-exporter')" />
          </div>
        </div>
      </div>

      <!-- Add BOM Character | Excel Formulas (2-col) -->
      <div class="grid" style="grid-template-columns: 1fr 1fr">
        <div class="py-5 px-6">
          <div class="flex items-center gap-4">
            <div class="flex items-start gap-3 flex-1 min-w-0">
              <span class="flex-shrink-0 w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center">
                <FileInput :size="15" :stroke-width="1.75" color="#6C7BFF" />
              </span>
              <div class="min-w-0">
                <p class="text-[13px] font-semibold text-gray-900 m-0 mb-0.5">{{ __('Add BOM Character', 'woocommerce-exporter') }}</p>
                <p class="text-[12px] text-gray-400 m-0">{{ __('Mark the CSV file as UTF-8 by prepending a Byte Order Mark.', 'woocommerce-exporter') }}</p>
              </div>
            </div>
            <div class="flex items-center gap-2.5 flex-shrink-0">
              <button
                type="button"
                class="relative w-11 h-6 rounded-full border-none p-0 cursor-pointer flex-shrink-0 transition-colors"
                :class="settings.bom ? 'bg-indigo-600' : 'bg-gray-300'"
                role="switch"
                :aria-checked="settings.bom"
                :aria-label="__('Add BOM Character', 'woocommerce-exporter')"
                @click="settings.bom = !settings.bom"
              >
                <span
                  class="absolute top-[3px] left-[3px] w-[18px] h-[18px] rounded-full bg-white shadow-sm transition-transform"
                  :class="settings.bom && 'translate-x-[20px]'"
                />
              </button>
              <span class="text-[13px] font-semibold" :class="settings.bom ? 'text-indigo-600' : 'text-gray-400'">
                {{ settings.bom ? __('Yes', 'woocommerce-exporter') : __('No', 'woocommerce-exporter') }}
              </span>
            </div>
          </div>
        </div>

        <div class="py-5 px-6 border-l border-gray-100">
          <div class="flex items-center gap-4">
            <div class="flex items-start gap-3 flex-1 min-w-0">
              <span class="flex-shrink-0 w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center">
                <SlidersHorizontal :size="15" :stroke-width="1.75" color="#6C7BFF" />
              </span>
              <div class="min-w-0">
                <p class="text-[13px] font-semibold text-gray-900 m-0 mb-0.5">{{ __('Excel Formulas', 'woocommerce-exporter') }}</p>
                <p class="text-[12px] text-gray-400 m-0">{{ __('Allow Excel formulas in exported files. By default formulas are stripped.', 'woocommerce-exporter') }}</p>
              </div>
            </div>
            <div class="flex items-center gap-2.5 flex-shrink-0">
              <button
                type="button"
                class="relative w-11 h-6 rounded-full border-none p-0 cursor-pointer flex-shrink-0 transition-colors"
                :class="settings.excel_formulas ? 'bg-indigo-600' : 'bg-gray-300'"
                role="switch"
                :aria-checked="settings.excel_formulas"
                :aria-label="__('Excel Formulas', 'woocommerce-exporter')"
                @click="settings.excel_formulas = !settings.excel_formulas"
              >
                <span
                  class="absolute top-[3px] left-[3px] w-[18px] h-[18px] rounded-full bg-white shadow-sm transition-transform"
                  :class="settings.excel_formulas && 'translate-x-[20px]'"
                />
              </button>
              <span class="text-[13px] font-semibold" :class="settings.excel_formulas ? 'text-indigo-600' : 'text-gray-400'">
                {{ settings.excel_formulas ? __('Allowed', 'woocommerce-exporter') : __('Stripped', 'woocommerce-exporter') }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Escape formulas in CSV/TSV -->
      <div class="py-5 px-6">
        <div class="flex items-center gap-4">
          <div class="flex items-start gap-3 flex-1 min-w-0">
            <span class="flex-shrink-0 w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center">
              <SlidersHorizontal :size="15" :stroke-width="1.75" color="#6C7BFF" />
            </span>
            <div class="min-w-0">
              <p class="text-[13px] font-semibold text-gray-900 m-0 mb-0.5">{{ __('Escape Formulas in CSV/TSV', 'woocommerce-exporter') }}</p>
              <p class="text-[12px] text-gray-400 m-0">
                {{ __('Prefix any CSV or TSV cell a spreadsheet would execute with a single quote. Negative amounts and international phone numbers are left untouched.', 'woocommerce-exporter') }}
              </p>
              <p v-if="settings.excel_formulas" class="text-[12px] text-gray-400 italic m-0 mt-1">
                {{ __('Only applies while Excel Formulas is set to Stripped.', 'woocommerce-exporter') }}
              </p>
            </div>
          </div>
          <div class="flex items-center gap-2.5 flex-shrink-0">
            <button
              type="button"
              class="relative w-11 h-6 rounded-full border-none p-0 flex-shrink-0 transition-colors"
              :class="[
                settings.csv_formula_escaping && !settings.excel_formulas ? 'bg-indigo-600' : 'bg-gray-300',
                settings.excel_formulas ? 'cursor-not-allowed opacity-50' : 'cursor-pointer'
              ]"
              :aria-label="__('Escape formulas in CSV/TSV', 'woocommerce-exporter')"
              :aria-pressed="settings.csv_formula_escaping"
              :disabled="settings.excel_formulas"
              @click="settings.csv_formula_escaping = !settings.csv_formula_escaping"
            >
              <span
                class="absolute top-[3px] left-[3px] w-[18px] h-[18px] rounded-full bg-white shadow-sm transition-transform"
                :class="settings.csv_formula_escaping && 'translate-x-[20px]'"
              />
            </button>
            <span
              class="text-[13px] font-semibold"
              :class="settings.csv_formula_escaping && !settings.excel_formulas ? 'text-indigo-600' : 'text-gray-400'"
            >
              {{ settings.csv_formula_escaping ? __('Escaped', 'woocommerce-exporter') : __('Raw', 'woocommerce-exporter') }}
            </span>
          </div>
        </div>
      </div>

      <!-- Script Timeout -->
      <div class="py-5 px-6">
        <div class="flex items-start gap-3 mb-4">
          <span class="flex-shrink-0 w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center">
            <Clock :size="15" :stroke-width="1.75" color="#6C7BFF" />
          </span>
          <div>
            <p class="text-[13px] font-semibold text-gray-900 m-0 mb-0.5">{{ __('Script Timeout', 'woocommerce-exporter') }}</p>
            <p class="text-[12px] text-gray-400 m-0">{{ __('Maximum time allowed for an export to process before it halts.', 'woocommerce-exporter') }}</p>
          </div>
        </div>
        <div class="pl-11">
          <BaseSelect v-model="timeoutValue" :options="timeoutOptions" />
        </div>
      </div>

    </div>
  </div>
</template>
