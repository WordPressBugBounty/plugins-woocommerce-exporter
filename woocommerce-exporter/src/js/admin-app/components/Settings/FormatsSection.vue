<script setup>
import { computed } from 'vue'
import { storeToRefs } from 'pinia'
import { AlignJustify, Columns, WrapText, Lock } from 'lucide-vue-next'
import { BaseRadio } from '../ui'
import { useSettingsStore } from '../../stores/settings'

const { settings, premiumFormats } = storeToRefs(useSettingsStore())

const xmlFormat = computed(() => premiumFormats.value.find(f => f.key === 'xml'))
const rssFormat = computed(() => premiumFormats.value.find(f => f.key === 'rss'))
</script>

<template>
  <div class="flex flex-col gap-4 flex-shrink-0">

    <!-- CSV -->
    <div class="bg-white border border-gray-200 rounded-[10px] overflow-hidden">
      <div class="py-5 px-6 border-b border-gray-100">
        <h2 class="text-[20px] font-bold text-gray-900 m-0 leading-tight">CSV Format</h2>
        <p class="text-[13px] text-gray-500 m-0 mt-1">Configure delimiters and line endings for CSV and TSV exports.</p>
      </div>

      <div class="divide-y divide-gray-100">

        <!-- Field Delimiter | Category Separator (2-col) -->
        <div class="grid" style="grid-template-columns: 1fr 1fr">
          <div class="py-5 px-6">
            <div class="flex items-start gap-3 mb-4">
              <span class="flex-shrink-0 w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center">
                <AlignJustify :size="15" :stroke-width="1.75" color="#6C7BFF" />
              </span>
              <div>
                <p class="text-[13px] font-semibold text-gray-900 m-0 mb-0.5">Field Delimiter</p>
                <p class="text-[12px] text-gray-400 m-0">Character separating each cell. Use <code class="text-[11px] bg-gray-100 px-1 rounded">TAB</code> for tab-separated.</p>
              </div>
            </div>
            <input
              type="text"
              v-model="settings.delimiter"
              maxlength="5"
              class="py-2 px-3 border border-gray-200 rounded-lg text-[13px] text-gray-900 outline-none focus:border-indigo-400 transition-colors w-24 font-mono"
            />
          </div>

          <div class="py-5 px-6 border-l border-gray-100">
            <div class="flex items-start gap-3 mb-4">
              <span class="flex-shrink-0 w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center">
                <Columns :size="15" :stroke-width="1.75" color="#6C7BFF" />
              </span>
              <div>
                <p class="text-[13px] font-semibold text-gray-900 m-0 mb-0.5">Category Separator</p>
                <p class="text-[12px] text-gray-400 m-0">Separates multiple categories per product. Use <code class="text-[11px] bg-gray-100 px-1 rounded">LF</code> for line breaks.</p>
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
              <p class="text-[13px] font-semibold text-gray-900 m-0 mb-0.5">Line Ending Formatting</p>
              <p class="text-[12px] text-gray-400 m-0">Choose line endings that suit the OS you'll open the file on.</p>
            </div>
          </div>
          <div class="flex flex-col gap-2 pl-11">
            <BaseRadio v-model="settings.line_ending_formatting" value="windows">
              <span class="text-[13px] text-gray-900 select-none">Windows / DOS <span class="text-gray-400 text-[12px]">(CRLF)</span></span>
            </BaseRadio>
            <BaseRadio v-model="settings.line_ending_formatting" value="unix">
              <span class="text-[13px] text-gray-900 select-none">Unix <span class="text-gray-400 text-[12px]">(LF)</span></span>
            </BaseRadio>
            <BaseRadio v-model="settings.line_ending_formatting" value="mac">
              <span class="text-[13px] text-gray-900 select-none">Mac <span class="text-gray-400 text-[12px]">(CR)</span></span>
            </BaseRadio>
          </div>
        </div>

      </div>
    </div>

    <!-- XML — Deluxe-gated: no form controls, upsell only -->
    <div class="wse-settings-format-locked wse-settings-format-locked--xml bg-white border border-gray-200 rounded-[10px] overflow-hidden">
      <div class="py-5 px-6 border-b border-gray-100 flex items-center justify-between gap-4">
        <div>
          <h2 class="text-[20px] font-bold text-gray-900 m-0 leading-tight">XML Format</h2>
          <p class="text-[13px] text-gray-500 m-0 mt-1">Control which metadata attributes are included in the XML root element.</p>
        </div>
        <span class="inline-flex items-center gap-1 flex-shrink-0 text-[11px] font-semibold text-amber-700 bg-amber-100 rounded-[6px] py-1 px-2">
          <Lock :size="11" :stroke-width="2.5" />
          Deluxe
        </span>
      </div>
      <div class="py-5 px-6 flex items-center justify-between gap-4">
        <p class="text-[13px] text-gray-500 m-0 max-w-[380px]">Exporting to XML with configurable root-element attributes is available in Store Exporter Deluxe.</p>
        <a
          v-if="xmlFormat?.upsell_url"
          :href="xmlFormat?.upsell_url"
          target="_blank"
          rel="noopener noreferrer"
          class="wse-settings-format-upsell inline-flex items-center flex-shrink-0 py-2 px-3.5 bg-amber-600 hover:bg-amber-700 !text-white text-[12.5px] font-semibold rounded-lg no-underline transition-colors"
        >Upgrade to Deluxe</a>
      </div>
    </div>

    <!-- RSS — Deluxe-gated: no form controls, upsell only -->
    <div class="wse-settings-format-locked wse-settings-format-locked--rss bg-white border border-gray-200 rounded-[10px] overflow-hidden">
      <div class="py-5 px-6 border-b border-gray-100 flex items-center justify-between gap-4">
        <div>
          <h2 class="text-[20px] font-bold text-gray-900 m-0 leading-tight">RSS Format</h2>
          <p class="text-[13px] text-gray-500 m-0 mt-1">Metadata included in the RSS feed header.</p>
        </div>
        <span class="inline-flex items-center gap-1 flex-shrink-0 text-[11px] font-semibold text-amber-700 bg-amber-100 rounded-[6px] py-1 px-2">
          <Lock :size="11" :stroke-width="2.5" />
          Deluxe
        </span>
      </div>
      <div class="py-5 px-6 flex items-center justify-between gap-4">
        <p class="text-[13px] text-gray-500 m-0 max-w-[380px]">Exporting an RSS feed of your store data is available in Store Exporter Deluxe.</p>
        <a
          v-if="rssFormat?.upsell_url"
          :href="rssFormat?.upsell_url"
          target="_blank"
          rel="noopener noreferrer"
          class="wse-settings-format-upsell inline-flex items-center flex-shrink-0 py-2 px-3.5 bg-amber-600 hover:bg-amber-700 !text-white text-[12.5px] font-semibold rounded-lg no-underline transition-colors"
        >Upgrade to Deluxe</a>
      </div>
    </div>

  </div>
</template>
