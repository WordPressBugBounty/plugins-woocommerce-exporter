<script setup>
import { computed, ref, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { FilePen, FileText, Calendar, Shield, Table2, Database, FileInput, Clock, SlidersHorizontal, BookOpen, X } from 'lucide-vue-next'
import { BaseRadio, BaseSelect, BaseInput, FilenameTokenInput } from '../ui'
import { useSettingsStore } from '../../stores/settings'

const { settings } = storeToRefs(useSettingsStore())

const FILENAME_DEFAULT = 'export-{type}-{store}-{date}-{time}'

const filenameVariables = [
  '{date}', '{time}', '{type}', '{store}', '{year}', '{month}', '{day}', '{hour}', '{minute}', '{second}'
]

const filenamePreview = computed(() => {
  const pattern = settings.value.filename
  if (!pattern) return ''
  const now = new Date()
  const pad = (n) => String(n).padStart(2, '0')
  return pattern
    .replace(/\{date\}/g,   `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`)
    .replace(/\{time\}/g,   `${pad(now.getHours())}-${pad(now.getMinutes())}`)
    .replace(/\{year\}/g,   String(now.getFullYear()))
    .replace(/\{month\}/g,  pad(now.getMonth() + 1))
    .replace(/\{day\}/g,    pad(now.getDate()))
    .replace(/\{hour\}/g,   pad(now.getHours()))
    .replace(/\{minute\}/g, pad(now.getMinutes()))
    .replace(/\{second\}/g, pad(now.getSeconds()))
    .replace(/\{type\}/g,   'product')
    .replace(/\{store\}/g,  'my-store')
})

const PREDEFINED_DATE_FORMATS = ['F j, Y', 'Y/m/d', 'm/d/Y', 'd/m/Y']

const dateFormatOptions = [
  { value: 'F j, Y', label: 'May 16, 2026', hint: 'F, j, Y' },
  { value: 'Y/m/d', label: '2026/05/16', hint: 'Y/m/d' },
  { value: 'm/d/Y', label: '05/16/2026', hint: 'm/d/Y' },
  { value: 'd/m/Y', label: '16/05/2026', hint: 'd/m/Y' },
  { value: 'custom', label: 'Custom', hint: 'Define your own' }
]

const encodingOptions = [
  { value: 'UTF-8', label: 'UTF-8' },
  { value: 'UTF-16', label: 'UTF-16' },
  { value: 'ISO-8859-1', label: 'ISO-8859-1' },
  { value: 'Windows-1252', label: 'Windows-1252' }
]

const timeoutOptions = [
  { value: '600', label: '10 minutes' },
  { value: '1800', label: '30 minutes' },
  { value: '3600', label: '1 hour' },
  { value: '0', label: 'Unlimited' }
]

const timeoutValue = computed({
  get: () => String(settings.value.timeout),
  set: (val) => { settings.value.timeout = Number(val) }
})

const DATE_TOKENS_DETAILED = [
  { token: 'Y', description: '4-digit year' },
  { token: 'y', description: '2-digit year' },
  { token: 'm', description: 'Month (01-12)' },
  { token: 'n', description: 'Month (1-12)' },
  { token: 'd', description: 'Day (01-31)' },
  { token: 'j', description: 'Day (1-31)' },
  { token: 'F', description: 'Month name' },
  { token: 'M', description: 'Short month' },
  { token: 'D', description: 'Short day' },
  { token: 'l', description: 'Full day name' },
  { token: '/', description: 'Slash separator' },
  { token: '-', description: 'Dash separator' },
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

function buildDateMap () {
  const now = new Date()
  const pad = n => String(n).padStart(2, '0')
  const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']
  const monthsShort = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
  const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
  const daysShort = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
  return {
    Y: String(now.getFullYear()), y: String(now.getFullYear()).slice(-2),
    m: pad(now.getMonth() + 1), n: String(now.getMonth() + 1),
    d: pad(now.getDate()), j: String(now.getDate()),
    H: pad(now.getHours()), G: String(now.getHours()),
    h: pad(now.getHours() % 12 || 12), g: String(now.getHours() % 12 || 12),
    i: pad(now.getMinutes()), s: pad(now.getSeconds()),
    A: now.getHours() >= 12 ? 'PM' : 'AM', a: now.getHours() >= 12 ? 'pm' : 'am',
    F: months[now.getMonth()], M: monthsShort[now.getMonth()],
    l: days[now.getDay()], D: daysShort[now.getDay()],
    N: String(now.getDay() || 7), w: String(now.getDay())
  }
}

function formatPhpDate (fmt) {
  const map = buildDateMap()
  return fmt.replace(/[YymndjHGhisAaFMlDNw]/g, ch => map[ch] ?? ch)
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
  Y: '4-digit year', y: '2-digit year',
  m: 'Month (01-12)', n: 'Month (1-12)',
  d: 'Day (01-31)', j: 'Day of the month',
  F: 'Full month name', M: 'Short month name',
  l: 'Full day name', D: 'Short day name',
}

const presetFormatBreakdown = computed(() => {
  if (isCustomDate.value) return []
  const fmt = settings.value.date_format
  const map = buildDateMap()
  const items = []
  for (const ch of fmt) {
    if (PRESET_TOKEN_LABELS[ch]) {
      items.push({ token: ch, label: PRESET_TOKEN_LABELS[ch], example: String(map[ch] ?? ch) })
    } else if (ch.trim()) {
      items.push({ token: ch, label: 'Separator', example: ch })
    }
  }
  return items
})

function insertVariable (token) {
  const el = filenameInput.value
  const current = settings.value.filename || ''
  const start = el ? el.selectionStart : current.length
  const end = el ? el.selectionEnd : current.length
  settings.value.filename = current.slice(0, start) + token + current.slice(end)
  const cursor = start + token.length
  if (el) setTimeout(() => el.setSelectionRange(cursor, cursor), 0)
}
</script>

<template>
  <div class="bg-white border border-gray-200 rounded-[10px] overflow-hidden flex-shrink-0">
    <div class="py-5 px-6 border-b border-gray-100">
      <h2 class="text-[20px] font-bold text-gray-900 m-0 leading-tight">General</h2>
      <p class="text-[13px] text-gray-500 m-0 mt-1">Configure the basic behavior and default preferences for exports.</p>
    </div>

    <div class="divide-y divide-gray-100">

      <!-- Export Filename -->
      <div class="py-5 px-6">
        <div class="flex items-start gap-3 mb-3">
          <span class="flex-shrink-0 w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center">
            <FilePen :size="15" :stroke-width="1.75" color="#6C7BFF" />
          </span>
          <div>
            <p class="text-[13px] font-semibold text-gray-900 m-0 mb-0.5">Export Filename</p>
            <p class="text-[12px] text-gray-400 m-0">Set the default filename for exported files.</p>
          </div>
        </div>
        <FilenameTokenInput
          v-model="settings.filename"
          :tokens="filenameVariables"
          :default-value="FILENAME_DEFAULT"
          placeholder="{type}-export-{date}"
        />
        <div v-if="filenamePreview" class="flex items-center gap-2 mt-1.5">
          <span class="text-[11px] text-gray-400 font-medium shrink-0">Live Preview</span>
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
            <p class="text-[13px] font-semibold text-gray-900 m-0 mb-0.5">Character Encoding</p>
            <p class="text-[12px] text-gray-400 m-0">Choose the character encoding for export files.</p>
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
            <p class="text-[13px] font-semibold text-gray-900 m-0 mb-0.5">Date Format</p>
            <p class="text-[12px] text-gray-400 m-0">Choose the date format to use in exports.</p>
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
              placeholder="e.g. Y-m-d H:i"
              class="flex-1 h-[40px] pl-3.5 pr-10 text-[14px] text-gray-900 placeholder:text-gray-400 bg-transparent outline-none"
            />
            <button
              v-if="customDateFormat"
              type="button"
              class="absolute right-3 flex items-center justify-center w-4 h-4 rounded-full bg-gray-300 hover:bg-gray-400 transition-colors cursor-pointer border-0 p-0"
              aria-label="Clear format"
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
            <span class="text-[11px] text-gray-400">Try:</span>
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
          View formatting documentation
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
            <p class="text-[13px] font-semibold text-gray-900 m-0 mb-0.5">Field Escape Formatting</p>
            <p class="text-[12px] text-gray-400 m-0">This option affects how data is written in CSV exports.</p>
          </div>
        </div>
        <div class="flex flex-col gap-2 pl-11">
          <BaseRadio v-model="settings.field_escape" value="all" align="start">
            <span>
              <strong class="block text-[13px] text-gray-900">Escape all cells</strong>
              <span class="text-[12px] text-gray-400">Write field data exactly as it is saved in the database.</span>
            </span>
          </BaseRadio>
          <BaseRadio v-model="settings.field_escape" value="excel" align="start">
            <span>
              <strong class="block text-[13px] text-gray-900">Escape cells as Excel would</strong>
              <span class="text-[12px] text-gray-400">Caution: may affect phone numbers and postcodes with leading zeroes.</span>
            </span>
          </BaseRadio>
          <BaseRadio v-model="settings.field_escape" value="none" align="start">
            <span>
              <strong class="block text-[13px] text-gray-900">Do not escape any cells</strong>
              <span class="text-[12px] text-gray-400">Caution: may affect text fields containing the delimiter character.</span>
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
              <p class="text-[13px] font-semibold text-gray-900 m-0 mb-0.5">Header Formatting</p>
              <p class="text-[12px] text-gray-400 m-0">Include or exclude column headers in exports.</p>
            </div>
          </div>
          <div class="flex flex-col gap-2 pl-11">
            <BaseRadio v-model="settings.header_formatting" value="include" label="Include column headers" />
            <BaseRadio v-model="settings.header_formatting" value="exclude" label="Exclude column headers" />
          </div>
        </div>

        <div class="py-5 px-6 border-l border-gray-100">
          <div class="flex items-start gap-3 mb-3">
            <span class="flex-shrink-0 w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center">
              <Database :size="15" :stroke-width="1.75" color="#6C7BFF" />
            </span>
            <div>
              <p class="text-[13px] font-semibold text-gray-900 m-0 mb-0.5">WordPress Object Cache</p>
              <p class="text-[12px] text-gray-400 m-0">Flush the cache before each export.</p>
            </div>
          </div>
          <div class="flex flex-col gap-2 pl-11">
            <BaseRadio v-model="settings.flush_cache" value="flush" label="Flush before each export" />
            <BaseRadio v-model="settings.flush_cache" value="none" label="Do not flush" />
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
                <p class="text-[13px] font-semibold text-gray-900 m-0 mb-0.5">Add BOM Character</p>
                <p class="text-[12px] text-gray-400 m-0">Mark the CSV file as UTF-8 by prepending a Byte Order Mark.</p>
              </div>
            </div>
            <div class="flex items-center gap-2.5 flex-shrink-0">
              <button
                type="button"
                class="relative w-11 h-6 rounded-full border-none p-0 cursor-pointer flex-shrink-0 transition-colors"
                :class="settings.bom ? 'bg-indigo-600' : 'bg-gray-300'"
                :aria-pressed="settings.bom"
                @click="settings.bom = !settings.bom"
              >
                <span
                  class="absolute top-[3px] left-[3px] w-[18px] h-[18px] rounded-full bg-white shadow-sm transition-transform"
                  :class="settings.bom && 'translate-x-[20px]'"
                />
              </button>
              <span class="text-[13px] font-semibold" :class="settings.bom ? 'text-indigo-600' : 'text-gray-400'">
                {{ settings.bom ? 'Yes' : 'No' }}
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
                <p class="text-[13px] font-semibold text-gray-900 m-0 mb-0.5">Excel Formulas</p>
                <p class="text-[12px] text-gray-400 m-0">Allow Excel formulas in exported files. By default formulas are stripped.</p>
              </div>
            </div>
            <div class="flex items-center gap-2.5 flex-shrink-0">
              <button
                type="button"
                class="relative w-11 h-6 rounded-full border-none p-0 cursor-pointer flex-shrink-0 transition-colors"
                :class="settings.excel_formulas ? 'bg-indigo-600' : 'bg-gray-300'"
                :aria-pressed="settings.excel_formulas"
                @click="settings.excel_formulas = !settings.excel_formulas"
              >
                <span
                  class="absolute top-[3px] left-[3px] w-[18px] h-[18px] rounded-full bg-white shadow-sm transition-transform"
                  :class="settings.excel_formulas && 'translate-x-[20px]'"
                />
              </button>
              <span class="text-[13px] font-semibold" :class="settings.excel_formulas ? 'text-indigo-600' : 'text-gray-400'">
                {{ settings.excel_formulas ? 'Allowed' : 'Stripped' }}
              </span>
            </div>
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
            <p class="text-[13px] font-semibold text-gray-900 m-0 mb-0.5">Script Timeout</p>
            <p class="text-[12px] text-gray-400 m-0">Maximum time allowed for an export to process before it halts.</p>
          </div>
        </div>
        <div class="pl-11">
          <BaseSelect v-model="timeoutValue" :options="timeoutOptions" />
        </div>
      </div>

    </div>
  </div>
</template>
