<script setup>
import { storeToRefs } from 'pinia'
import { AlignJustify, Columns, WrapText, Code2, Rss, Link } from 'lucide-vue-next'
import { BaseCheckbox, BaseRadio } from '../ui'
import { useSettingsStore } from '../../stores/settings'

const { settings } = storeToRefs(useSettingsStore())

const xmlAttributes = [
  { key: 'xml_attribute_url', label: 'Site Address' },
  { key: 'xml_attribute_title', label: 'Site Title' },
  { key: 'xml_attribute_date', label: 'Export Date' },
  { key: 'xml_attribute_time', label: 'Export Time' },
  { key: 'xml_attribute_export', label: 'Export Type' },
  { key: 'xml_attribute_orderby', label: 'Export Order By' },
  { key: 'xml_attribute_order', label: 'Export Order' },
  { key: 'xml_attribute_limit', label: 'Limit Volume' },
  { key: 'xml_attribute_offset', label: 'Volume Offset' }
]
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

    <!-- XML -->
    <div class="bg-white border border-gray-200 rounded-[10px] overflow-hidden">
      <div class="py-5 px-6 border-b border-gray-100">
        <h2 class="text-[20px] font-bold text-gray-900 m-0 leading-tight">XML Format</h2>
        <p class="text-[13px] text-gray-500 m-0 mt-1">Control which metadata attributes are included in the XML root element.</p>
      </div>

      <div class="py-5 px-6">
        <div class="flex items-start gap-3 mb-4">
          <span class="flex-shrink-0 w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center">
            <Code2 :size="15" :stroke-width="1.75" color="#6C7BFF" />
          </span>
          <div>
            <p class="text-[13px] font-semibold text-gray-900 m-0 mb-0.5">Attribute Display</p>
            <p class="text-[12px] text-gray-400 m-0">Choose which attributes appear on the root XML element.</p>
          </div>
        </div>
        <div class="grid gap-2 pl-11" style="grid-template-columns: repeat(3, 1fr)">
          <BaseCheckbox
            v-for="attr in xmlAttributes"
            :key="attr.key"
            v-model="settings[attr.key]"
            :label="attr.label"
          />
        </div>
      </div>
    </div>

    <!-- RSS -->
    <div class="bg-white border border-gray-200 rounded-[10px] overflow-hidden">
      <div class="py-5 px-6 border-b border-gray-100">
        <h2 class="text-[20px] font-bold text-gray-900 m-0 leading-tight">RSS Format</h2>
        <p class="text-[13px] text-gray-500 m-0 mt-1">Metadata included in the RSS feed header.</p>
      </div>

      <div class="divide-y divide-gray-100">
        <div class="py-5 px-6">
          <div class="flex items-start gap-3 mb-4">
            <span class="flex-shrink-0 w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center">
              <Rss :size="15" :stroke-width="1.75" color="#6C7BFF" />
            </span>
            <div>
              <p class="text-[13px] font-semibold text-gray-900 m-0 mb-0.5">Title Element</p>
              <p class="text-[12px] text-gray-400 m-0">Title of the data feed.</p>
            </div>
          </div>
          <input
            type="text"
            v-model="settings.rss_title"
            placeholder="e.g. Product export for My Store"
            class="w-full py-2 px-3 border border-gray-200 rounded-lg text-[13px] text-gray-900 outline-none focus:border-indigo-400 transition-colors"
          />
        </div>

        <div class="py-5 px-6">
          <div class="flex items-start gap-3 mb-4">
            <span class="flex-shrink-0 w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center">
              <Link :size="15" :stroke-width="1.75" color="#6C7BFF" />
            </span>
            <div>
              <p class="text-[13px] font-semibold text-gray-900 m-0 mb-0.5">Link Element</p>
              <p class="text-[12px] text-gray-400 m-0">A link to your website.</p>
            </div>
          </div>
          <input
            type="url"
            v-model="settings.rss_link"
            placeholder="https://example.com"
            class="w-full py-2 px-3 border border-gray-200 rounded-lg text-[13px] text-gray-900 outline-none focus:border-indigo-400 transition-colors"
          />
        </div>

        <div class="py-5 px-6">
          <div class="flex items-start gap-3 mb-4">
            <span class="flex-shrink-0 w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center">
              <Rss :size="15" :stroke-width="1.75" color="#6C7BFF" />
            </span>
            <div>
              <p class="text-[13px] font-semibold text-gray-900 m-0 mb-0.5">Description Element</p>
              <p class="text-[12px] text-gray-400 m-0">A short description of your data feed.</p>
            </div>
          </div>
          <input
            type="text"
            v-model="settings.rss_description"
            placeholder="e.g. Daily product feed for My Store"
            class="w-full py-2 px-3 border border-gray-200 rounded-lg text-[13px] text-gray-900 outline-none focus:border-indigo-400 transition-colors"
          />
        </div>
      </div>
    </div>

  </div>
</template>
