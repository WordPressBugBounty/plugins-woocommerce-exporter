<script setup>
import { computed, ref, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { __ } from '@wordpress/i18n'
import { AlignJustify, Columns, WrapText, Lock } from 'lucide-vue-next'
import { BaseRadio, I18nText, RadioPillGroup } from '../ui'
import { useSettingsStore } from '../../stores/settings'
import {
  DELIMITER_OPTIONS,
  CUSTOM_DELIMITER,
  decodeStoredDelimiter,
  encodeStoredDelimiter,
  isValidDelimiter
} from '../../lib/delimiter'

const store = useSettingsStore()
const { settings, premiumFormats, saveErrorCode } = storeToRefs(store)

// The listed delimiters plus a Custom pill that reveals a one-character input,
// so a store already on '^' or '~' keeps working and nobody has to know the
// TAB keyword.
const DELIMITER_PILLS = [
  ...DELIMITER_OPTIONS,
  { value: CUSTOM_DELIMITER, label: __('Custom', 'woocommerce-exporter') }
]
const PILL_VALUES = DELIMITER_OPTIONS.map(({ value }) => value)

// Set when the user picks Custom while a listed delimiter is selected: without
// it, clearing the input would snap the selection back to whichever pill the
// now-empty value happens to match.
const customPicked = ref(false)

// Which pill is lit. A stored delimiter outside the vocabulary lights Custom,
// so the value in force is always visible.
const selectedDelimiterPill = computed(() => {
  const delimiter = decodeStoredDelimiter(settings.value.delimiter)

  return !customPicked.value && PILL_VALUES.includes(delimiter) ? delimiter : CUSTOM_DELIMITER
})

const isCustomDelimiter = computed(() => selectedDelimiterPill.value === CUSTOM_DELIMITER)

function selectDelimiterPill (value) {
  if (value === CUSTOM_DELIMITER) {
    customPicked.value = true
    // Start the input empty when leaving a listed pill.
    if (PILL_VALUES.includes(decodeStoredDelimiter(settings.value.delimiter))) {
      settings.value.delimiter = ''
    }
    return
  }

  customPicked.value = false
  settings.value.delimiter = encodeStoredDelimiter(value)
}

// The server's rejection is about the value that was POSTed, so it stops
// being true the moment the user changes it. When the change discards the
// edits (Reset Changes), the Custom pick is dropped too, so the pills match
// the saved delimiter again.
watch(() => settings.value.delimiter, () => {
  if (!store.isDirty) {
    customPicked.value = false
  }
  if (saveErrorCode.value === 'wse_invalid_delimiter') {
    store.saveErrorCode = ''
    store.saveError = null
  }
})

// The validator returns an error code, not a message: the template picks the
// message, so no code path compares translated strings.
const DELIMITER_ERROR = Object.freeze({
  REFUSED: 'refused',
  EMPTY: 'empty',
  UNUSABLE_CHARACTER: 'unusable_character'
})

const delimiterErrorCode = computed(() => {
  if (saveErrorCode.value === 'wse_invalid_delimiter') {
    return DELIMITER_ERROR.REFUSED
  }

  if (!isCustomDelimiter.value || isValidDelimiter(settings.value.delimiter)) {
    return ''
  }

  // sanitize_text_field() trims whitespace to '' on the way in, so a lone
  // space is an empty box as far as storage goes.
  if (!settings.value.delimiter.trim()) {
    return DELIMITER_ERROR.EMPTY
  }

  return DELIMITER_ERROR.UNUSABLE_CHARACTER
})

// "ASCII" rather than just "character": the rule is one BYTE, so 'é' and '€'
// are refused even though the input's maxlength lets them be typed.
// The refused character is shown as code, not in quotes: a typed '"' in quotes
// read as three quote marks.
const DELIMITER_ERROR_MESSAGES = {
  [DELIMITER_ERROR.REFUSED]: __('The last save was refused: the Field Delimiter must be a single ASCII character, or a Tab.', 'woocommerce-exporter'),
  [DELIMITER_ERROR.EMPTY]: __('Enter a single ASCII character to use as the delimiter.', 'woocommerce-exporter'),
  /* translators: %1$s: the character the user typed, shown as code */
  [DELIMITER_ERROR.UNUSABLE_CHARACTER]: __('%1$s cannot be used as a delimiter. Pick a single ASCII character such as ^ or ~.', 'woocommerce-exporter')
}

const delimiterError = computed(() => DELIMITER_ERROR_MESSAGES[delimiterErrorCode.value] ?? '')

// Referenced by the input's aria-describedby, so the message is announced as
// the field's own description.
const DELIMITER_ERROR_ID = 'wse-delimiter-error'

const xmlFormat = computed(() => premiumFormats.value.find(f => f.key === 'xml'))
const rssFormat = computed(() => premiumFormats.value.find(f => f.key === 'rss'))
</script>

<template>
  <div class="flex flex-col gap-4 flex-shrink-0">

    <!-- CSV -->
    <div class="bg-white border border-gray-200 rounded-[10px] overflow-hidden">
      <div class="py-5 px-6 border-b border-gray-100">
        <h2 class="text-[20px] font-bold text-gray-900 m-0 leading-tight">{{ __('CSV & TSV Format', 'woocommerce-exporter') }}</h2>
        <p class="text-[13px] text-gray-500 m-0 mt-1">{{ __('Configure delimiters and line endings for CSV and TSV exports.', 'woocommerce-exporter') }}</p>
      </div>

      <div class="divide-y divide-gray-100">

        <!-- Field Delimiter, then Category Separator. Stacked rather than a
             half-width 2-column pair: the delimiter is a five-pill group, and
             at half width it wrapped onto a ragged second row. -->
        <div class="divide-y divide-gray-100">
          <div class="py-5 px-6">
            <div class="flex items-start gap-3 mb-4">
              <span class="flex-shrink-0 w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center">
                <AlignJustify :size="15" :stroke-width="1.75" color="#6C7BFF" />
              </span>
              <div>
                <p class="text-[13px] font-semibold text-gray-900 m-0 mb-0.5">{{ __('Field Delimiter', 'woocommerce-exporter') }}</p>
                <p class="text-[12px] text-gray-400 m-0">{{ __('The single character separating each cell. Pick Custom for anything else.', 'woocommerce-exporter') }}</p>
              </div>
            </div>
            <div class="flex flex-col gap-2" data-testid="settings-delimiter">
              <RadioPillGroup
                :options="DELIMITER_PILLS"
                :model-value="selectedDelimiterPill"
                :aria-label="__('Field Delimiter', 'woocommerce-exporter')"
                @update:model-value="selectDelimiterPill"
              />
              <input
                v-if="isCustomDelimiter"
                type="text"
                v-model="settings.delimiter"
                maxlength="1"
                data-testid="settings-delimiter-custom"
                :aria-label="__('Custom field delimiter', 'woocommerce-exporter')"
                :aria-invalid="delimiterError ? 'true' : 'false'"
                :aria-describedby="delimiterError ? DELIMITER_ERROR_ID : undefined"
                :class="[
                  'py-2 px-3 border rounded-lg text-[13px] text-gray-900 outline-none transition-colors w-24 font-mono',
                  delimiterError ? 'border-red-400' : 'border-gray-200 focus:border-indigo-400'
                ]"
              />
              <!-- role="alert" so the refusal is spoken when it appears: the
                   value is rejected as the admin types, with no focus move. -->
              <p v-if="delimiterError" :id="DELIMITER_ERROR_ID" role="alert" class="text-[12px] text-red-600 m-0" data-testid="settings-delimiter-error">
                <I18nText v-if="delimiterErrorCode === DELIMITER_ERROR.UNUSABLE_CHARACTER" :text="delimiterError">
                  <template #1><code class="text-[11px] bg-red-50 px-1 rounded" data-testid="settings-delimiter-error-char">{{ settings.delimiter }}</code></template>
                </I18nText>
                <template v-else>{{ delimiterError }}</template>
              </p>
            </div>
          </div>

          <div class="py-5 px-6">
            <div class="flex items-start gap-3 mb-4">
              <span class="flex-shrink-0 w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center">
                <Columns :size="15" :stroke-width="1.75" color="#6C7BFF" />
              </span>
              <div>
                <p class="text-[13px] font-semibold text-gray-900 m-0 mb-0.5">{{ __('Category Separator', 'woocommerce-exporter') }}</p>
                <p class="text-[12px] text-gray-400 m-0">
                  <!-- translators: %1$s: the LF keyword, shown as code -->
                  <I18nText :text="__('Separates multiple categories per product. Use %1$s for line breaks.', 'woocommerce-exporter')">
                    <template #1><code class="text-[11px] bg-gray-100 px-1 rounded">LF</code></template>
                  </I18nText>
                </p>
              </div>
            </div>
            <input
              type="text"
              v-model="settings.category_separator"
              maxlength="5"
              class="py-2 px-3 border border-gray-200 rounded-lg text-[13px] text-gray-900 outline-none focus:border-indigo-400 transition-colors w-24 font-mono"
            />
          </div>
        </div>

        <!-- Line Ending Formatting -->
        <div class="py-5 px-6">
          <div class="flex items-start gap-3 mb-3">
            <span class="flex-shrink-0 w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center">
              <WrapText :size="15" :stroke-width="1.75" color="#6C7BFF" />
            </span>
            <div>
              <p class="text-[13px] font-semibold text-gray-900 m-0 mb-0.5">{{ __('Line Ending Formatting', 'woocommerce-exporter') }}</p>
              <p class="text-[12px] text-gray-400 m-0">{{ __('Choose line endings that suit the OS you\'ll open the file on.', 'woocommerce-exporter') }}</p>
            </div>
          </div>
          <div class="flex flex-col gap-2 pl-11">
            <BaseRadio v-model="settings.line_ending_formatting" value="windows">
              <span class="text-[13px] text-gray-900 select-none">{{ __('Windows / DOS', 'woocommerce-exporter') }} <span class="text-gray-400 text-[12px]">(CRLF)</span></span>
            </BaseRadio>
            <BaseRadio v-model="settings.line_ending_formatting" value="unix">
              <span class="text-[13px] text-gray-900 select-none">{{ __('Unix', 'woocommerce-exporter') }} <span class="text-gray-400 text-[12px]">(LF)</span></span>
            </BaseRadio>
            <BaseRadio v-model="settings.line_ending_formatting" value="mac">
              <span class="text-[13px] text-gray-900 select-none">{{ __('Mac', 'woocommerce-exporter') }} <span class="text-gray-400 text-[12px]">(CR)</span></span>
            </BaseRadio>
          </div>
        </div>

      </div>
    </div>

    <!-- XML — Deluxe-gated: no form controls, upsell only -->
    <div class="wse-settings-format-locked wse-settings-format-locked--xml bg-white border border-gray-200 rounded-[10px] overflow-hidden">
      <div class="py-5 px-6 border-b border-gray-100 flex items-center justify-between gap-4">
        <div>
          <h2 class="text-[20px] font-bold text-gray-900 m-0 leading-tight">{{ __('XML Format', 'woocommerce-exporter') }}</h2>
          <p class="text-[13px] text-gray-500 m-0 mt-1">{{ __('Control which metadata attributes are included in the XML root element.', 'woocommerce-exporter') }}</p>
        </div>
        <span class="inline-flex items-center gap-1 flex-shrink-0 text-[11px] font-semibold text-amber-700 bg-amber-100 rounded-[6px] py-1 px-2">
          <Lock :size="11" :stroke-width="2.5" />
          {{ __('Deluxe', 'woocommerce-exporter') }}
        </span>
      </div>
      <div class="py-5 px-6 flex items-center justify-between gap-4">
        <p class="text-[13px] text-gray-500 m-0 max-w-[380px]">{{ __('Exporting to XML with configurable root-element attributes is available in Store Exporter Deluxe.', 'woocommerce-exporter') }}</p>
        <a
          v-if="xmlFormat?.upsell_url"
          :href="xmlFormat?.upsell_url"
          target="_blank"
          rel="noopener noreferrer"
          class="wse-settings-format-upsell inline-flex items-center flex-shrink-0 py-2 px-3.5 bg-amber-600 hover:bg-amber-700 !text-white text-[12.5px] font-semibold rounded-lg no-underline transition-colors"
        >{{ __('Upgrade to Deluxe', 'woocommerce-exporter') }}</a>
      </div>
    </div>

    <!-- RSS — Deluxe-gated: no form controls, upsell only -->
    <div class="wse-settings-format-locked wse-settings-format-locked--rss bg-white border border-gray-200 rounded-[10px] overflow-hidden">
      <div class="py-5 px-6 border-b border-gray-100 flex items-center justify-between gap-4">
        <div>
          <h2 class="text-[20px] font-bold text-gray-900 m-0 leading-tight">{{ __('RSS Format', 'woocommerce-exporter') }}</h2>
          <p class="text-[13px] text-gray-500 m-0 mt-1">{{ __('Metadata included in the RSS feed header.', 'woocommerce-exporter') }}</p>
        </div>
        <span class="inline-flex items-center gap-1 flex-shrink-0 text-[11px] font-semibold text-amber-700 bg-amber-100 rounded-[6px] py-1 px-2">
          <Lock :size="11" :stroke-width="2.5" />
          {{ __('Deluxe', 'woocommerce-exporter') }}
        </span>
      </div>
      <div class="py-5 px-6 flex items-center justify-between gap-4">
        <p class="text-[13px] text-gray-500 m-0 max-w-[380px]">{{ __('Exporting an RSS feed of your store data is available in Store Exporter Deluxe.', 'woocommerce-exporter') }}</p>
        <a
          v-if="rssFormat?.upsell_url"
          :href="rssFormat?.upsell_url"
          target="_blank"
          rel="noopener noreferrer"
          class="wse-settings-format-upsell inline-flex items-center flex-shrink-0 py-2 px-3.5 bg-amber-600 hover:bg-amber-700 !text-white text-[12.5px] font-semibold rounded-lg no-underline transition-colors"
        >{{ __('Upgrade to Deluxe', 'woocommerce-exporter') }}</a>
      </div>
    </div>

  </div>
</template>
