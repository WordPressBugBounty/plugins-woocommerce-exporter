<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { storeToRefs } from 'pinia'
import {
  Search, Save, RotateCcw, Settings, AlertCircle,
  CheckCircle2, Lightbulb, ArrowRight, Info,
  FileText, SlidersHorizontal
} from 'lucide-vue-next'
import { BaseButton } from '../components/ui/index'
import { useSettingsStore } from '../stores/settings'

import GeneralSection from '../components/Settings/GeneralSection.vue'
import FormatsSection from '../components/Settings/FormatsSection.vue'
import AdvancedSection from '../components/Settings/AdvancedSection.vue'
import PageActions from '../components/PageActions.vue'

const sectionComponents = {
  general: GeneralSection,
  formats: FormatsSection,
  advanced: AdvancedSection
}

const store = useSettingsStore()
const { isLoading, isSaving, error, activeCategory, categories, settings, isDirty, unsavedCount } = storeToRefs(store)

const categoryMeta = {
  general: { icon: Settings, description: 'Export behavior and preferences' },
  formats: { icon: FileText, description: 'Configure CSV, XML and RSS formats' },
  advanced: { icon: SlidersHorizontal, description: 'System, performance and debug options' }
}

// Only free-plugin categories are supported; filter out any premium
// categories the backend may still report so the nav never shows a dead tab.
const visibleCategories = computed(() => categories.value.filter(cat => Object.prototype.hasOwnProperty.call(sectionComponents, cat.key)))

const saveFeedbackText = ref('')
let saveFeedbackTimer = null

async function handleSave () {
  clearTimeout(saveFeedbackTimer)
  try {
    await store.saveSettings()
    saveFeedbackText.value = 'saved'
    saveFeedbackTimer = setTimeout(() => { saveFeedbackText.value = '' }, 3000)
  } catch {
    saveFeedbackText.value = ''
  }
}

const activeCategoryLabel = computed(() => {
  const cat = categories.value.find(c => c.key === activeCategory.value)
  return cat ? cat.label : 'Settings'
})

// Guard against a stale/bookmarked ?tab=settings/{category} pointing at a
// premium section that no longer exists in this (free) build.
if (!Object.prototype.hasOwnProperty.call(sectionComponents, activeCategory.value)) {
  activeCategory.value = 'general'
}

onMounted(() => { if (!store.isSettingsLoaded) store.fetchSettings() })

// Keep ?tab=settings/{category} in sync so page reloads restore the active section.
watch(activeCategory, (newCat) => {
  const url = new URL(window.location.href)
  const tabValue = `settings/${newCat}`
  let qs
  if (!url.search) {
    qs = `?tab=${tabValue}`
  } else if (/[?&]tab=/.test(url.search)) {
    qs = url.search.replace(/([?&]tab=)[^&]*/, `$1${tabValue}`)
  } else {
    qs = url.search + `&tab=${tabValue}`
  }
  history.replaceState(null, '', url.origin + url.pathname + qs)
}, { immediate: true })

const delimiterDisplay = computed(() => {
  const d = settings.value.delimiter
  if (d === ',') return 'Comma (,)'
  if (d === ';') return 'Semicolon (;)'
  if (d === '\t' || d === 'TAB') return 'Tab'
  return d || ','
})
</script>

<template>
  <main
    class="wse-main wse-settings"
    style="height: calc(100vh - 32px); overflow: hidden; padding: 0; gap: 0"
  >

    <!-- Page header -->
    <div class="flex items-start justify-between px-7 pt-6 pb-5 flex-shrink-0">
      <div>
        <h1 class="wse-page-title">Settings</h1>
        <p class="wse-page-sub">Manage export behavior, formatting, automation, and integrations.</p>
      </div>
      <div class="flex items-center gap-3">
        <div class="wse-search-field flex items-center gap-2 py-2 px-3 border border-gray-200 rounded-lg bg-white min-w-[210px] focus-within:border-indigo-400 transition-colors">
          <Search :size="14" color="#9CA3AF" :stroke-width="1.75" />
          <input type="text" placeholder="Search settings..." class="flex-1 text-[13px] text-gray-900 placeholder:text-gray-400" />
        </div>
        <button
          type="button"
          class="wse-settings-reset inline-flex items-center gap-2 py-[9px] px-4 border border-gray-200 rounded-lg bg-white text-gray-700 text-[13px] font-medium leading-none transition-colors hover:border-indigo-300 hover:text-indigo-600"
          @click="store.resetToSaved()"
        >
          <RotateCcw :size="13" :stroke-width="2" />
          Reset Defaults
        </button>
        <BaseButton class="wse-settings-save" :loading="isSaving" @click="handleSave">
          <template #icon><Save :size="14" :stroke-width="2" /></template>
          {{ isSaving ? 'Saving…' : saveFeedbackText === 'saved' ? 'Saved!' : 'Save Changes' }}
        </BaseButton>
        <PageActions />
      </div>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="flex-1 wse-loading">
      <span class="wse-spinner" />Loading settings&hellip;
    </div>

    <!-- Error -->
    <div v-else-if="error" class="mx-7 flex-shrink-0 wse-error">
      <AlertCircle :size="16" color="#DC2626" :stroke-width="1.75" />{{ error }}
    </div>

    <template v-else>

      <!-- 3-column grid -->
      <div
        class="flex-1 min-h-0 grid gap-5"
        style="grid-template-columns: 268px 1fr 258px; grid-template-rows: 1fr; padding: 0 28px"
      >

        <!-- Left: Category Nav -->
        <div class="overflow-hidden bg-white border border-gray-200 rounded-[10px] flex flex-col mb-4">
          <div class="py-3 px-4 border-b border-gray-100 flex-shrink-0">
            <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-[0.08em] m-0">Settings</p>
          </div>
          <nav class="py-2 overflow-y-auto flex-1">
            <button
              v-for="cat in visibleCategories"
              :key="cat.key"
              type="button"
              class="wse-settings-nav w-full flex items-center gap-3 py-2.5 px-3 cursor-pointer border-none text-left transition-all relative"
              :class="[`wse-settings-nav--${cat.key}`, activeCategory === cat.key ? 'bg-indigo-50' : 'bg-transparent hover:bg-gray-50']"
              @click="activeCategory = cat.key"
            >
              <span
                v-if="activeCategory === cat.key"
                class="absolute left-0 top-[8px] bottom-[8px] w-[3px] bg-indigo-600 rounded-r-full"
              />
              <span
                class="flex-shrink-0 w-[34px] h-[34px] rounded-lg flex items-center justify-center transition-colors"
                :class="activeCategory === cat.key ? 'bg-indigo-100' : 'bg-gray-100'"
              >
                <component
                  :is="(categoryMeta[cat.key] || {}).icon || Settings"
                  :size="16"
                  :stroke-width="1.75"
                  :color="activeCategory === cat.key ? '#6C7BFF' : '#6B7280'"
                />
              </span>
              <span class="flex-1 min-w-0">
                <span
                  class="block text-[13px] font-semibold leading-tight"
                  :class="activeCategory === cat.key ? 'text-indigo-700' : 'text-gray-800'"
                >{{ cat.label }}</span>
                <span class="block text-[11px] text-gray-400 leading-tight mt-[2px] truncate">
                  {{ (categoryMeta[cat.key] || {}).description || '' }}
                </span>
              </span>
            </button>
          </nav>
        </div>

        <!-- Centre: Section content (scrollable) -->
        <div class="overflow-y-auto min-h-0 flex flex-col gap-4 pb-6">
          <component
            :is="sectionComponents[activeCategory]"
            v-if="sectionComponents[activeCategory]"
          />
        </div>

        <!-- Right: Sidebar (fixed) -->
        <div class="overflow-hidden flex flex-col gap-4 pb-6">

          <!-- Configuration Summary -->
          <div class="bg-white border border-gray-200 rounded-[10px] p-4 flex-shrink-0">
            <div class="flex items-center gap-2 mb-3">
              <SlidersHorizontal :size="15" color="#7986FF" :stroke-width="1.75" />
              <h3 class="text-[13px] font-semibold text-gray-900 m-0">Configuration Summary</h3>
            </div>
            <div class="flex flex-col gap-2.5">
              <div class="flex items-center justify-between text-[12px]">
                <span class="text-gray-500">Archives</span>
                <span
                  class="inline-flex items-center py-[2px] px-2 rounded-full text-[11px] font-semibold border"
                  :class="settings.enable_archive
                    ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                    : 'bg-gray-50 text-gray-500 border-gray-200'"
                >{{ settings.enable_archive ? 'Enabled' : 'Disabled' }}</span>
              </div>
              <div class="flex items-center justify-between text-[12px]">
                <span class="text-gray-500">Date Format</span>
                <span class="font-semibold text-gray-900 font-mono text-[11px]">{{ settings.date_format }}</span>
              </div>
              <div class="flex items-center justify-between text-[12px]">
                <span class="text-gray-500">CSV Delimiter</span>
                <span class="font-semibold text-gray-900">{{ delimiterDisplay }}</span>
              </div>
              <div class="flex items-center justify-between text-[12px]">
                <span class="text-gray-500">Character Encoding</span>
                <span class="font-semibold text-gray-900">{{ settings.encoding }}</span>
              </div>
              <div class="flex items-center justify-between text-[12px]">
                <span class="text-gray-500">Object Cache</span>
                <span class="font-semibold text-gray-900">{{ settings.flush_cache === 'flush' ? 'Flush Enabled' : 'Disabled' }}</span>
              </div>
              <div class="flex items-center justify-between text-[12px]">
                <span class="text-gray-500">Scheduled Exports</span>
                <span
                  class="inline-flex items-center py-[2px] px-2 rounded-full text-[11px] font-semibold border"
                  :class="settings.enable_auto
                    ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                    : 'bg-gray-50 text-gray-500 border-gray-200'"
                >{{ settings.enable_auto ? 'Enabled' : 'Disabled' }}</span>
              </div>
              <div class="flex items-center justify-between text-[12px]">
                <span class="text-gray-500">CRON</span>
                <span
                  class="inline-flex items-center py-[2px] px-2 rounded-full text-[11px] font-semibold border"
                  :class="settings.enable_cron
                    ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                    : 'bg-gray-50 text-gray-500 border-gray-200'"
                >{{ settings.enable_cron ? 'Enabled' : 'Disabled' }}</span>
              </div>
            </div>
          </div>

          <!-- Tips -->
          <div class="bg-white border border-gray-200 rounded-[10px] p-4 flex-shrink-0">
            <div class="flex items-center gap-2 mb-3">
              <Lightbulb :size="14" color="#7986FF" :stroke-width="1.75" />
              <h3 class="text-[13px] font-semibold text-gray-900 m-0">Tips</h3>
            </div>
            <ul class="m-0 p-0 list-none flex flex-col gap-2 mb-3">
              <li
                v-for="tip in [
                  'Use export tags to make filenames dynamic.',
                  'Enable archives to keep a history of exported files.',
                  'UTF-8 is recommended for international data.'
                ]"
                :key="tip"
                class="flex items-start gap-1.5 text-[12px] text-gray-500"
              >
                <CheckCircle2 :size="13" color="#22C55E" :stroke-width="2" class="flex-shrink-0 mt-[1px]" />
                {{ tip }}
              </li>
            </ul>
            <a
              href="https://visser.com.au/documentation/store-exporter-deluxe/"
              target="_blank"
              rel="noopener noreferrer"
              class="text-[12px] text-indigo-600 no-underline hover:underline inline-flex items-center gap-1"
            >
              See all tips
              <ArrowRight :size="11" :stroke-width="2" />
            </a>
          </div>

        </div>
      </div>

      <!-- Unsaved changes bar -->
      <div
        v-if="isDirty"
        class="flex-shrink-0 px-7 py-3.5 bg-white border-t border-gray-200 flex items-center justify-between gap-3"
      >
        <div class="flex items-center gap-2.5">
          <span class="flex-shrink-0 w-7 h-7 bg-indigo-50 rounded-lg flex items-center justify-center">
            <Info :size="14" color="#6C7BFF" :stroke-width="2" />
          </span>
          <span class="text-[13px] text-indigo-700">
            You have <strong>{{ unsavedCount }} unsaved change{{ unsavedCount !== 1 ? 's' : '' }}</strong>
          </span>
        </div>
        <div class="flex items-center gap-2.5">
          <button
            type="button"
            class="wse-settings-reset inline-flex items-center gap-2 py-[9px] px-4 border border-gray-200 rounded-lg bg-white text-gray-700 text-[13px] font-medium leading-none transition-colors hover:border-indigo-300 hover:text-indigo-600"
            @click="store.resetToSaved()"
          >
            <RotateCcw :size="13" :stroke-width="2" />
            Reset Changes
          </button>
          <BaseButton class="wse-settings-save" :loading="isSaving" @click="handleSave">
            <template #icon><Save :size="14" :stroke-width="2" /></template>
            {{ isSaving ? 'Saving…' : 'Save Changes' }}
          </BaseButton>
        </div>
      </div>

    </template>
  </main>
</template>
