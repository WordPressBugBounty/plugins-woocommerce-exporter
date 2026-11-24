<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { storeToRefs } from 'pinia'
import { __, _n } from '@wordpress/i18n'
import {
  Search, Save, RotateCcw, Settings, AlertCircle,
  CheckCircle2, Lightbulb, ArrowRight, Info,
  FileText, SlidersHorizontal
} from 'lucide-vue-next'
import { BaseButton, I18nText } from '../components/ui/index'
import { formatCount } from '../lib/formatWpDate'
import { useSettingsStore } from '../stores/settings'
import { SETTINGS_SUMMARY_LABELS, decodeStoredDelimiter, isValidDelimiter } from '../lib/delimiter'

import GeneralSection from '../components/Settings/GeneralSection.vue'
import FormatsSection from '../components/Settings/FormatsSection.vue'
import AdvancedSection from '../components/Settings/AdvancedSection.vue'
import PageActions from '../components/PageActions.vue'
import { KB_HUB_URL } from '../docLinks'

const sectionComponents = {
  general: GeneralSection,
  formats: FormatsSection,
  advanced: AdvancedSection
}

const store = useSettingsStore()
const { isLoading, isSaving, error, saveError, activeCategory, categories, settings, isDirty, unsavedCount } = storeToRefs(store)

const categoryMeta = {
  general: { icon: Settings, description: __('Export behavior and preferences', 'woocommerce-exporter') },
  formats: { icon: FileText, description: __('Configure CSV export formatting. XML and RSS require Store Exporter Deluxe.', 'woocommerce-exporter') },
  advanced: { icon: SlidersHorizontal, description: __('System, performance and debug options', 'woocommerce-exporter') }
}

// Only free-plugin categories are supported; filter out any premium
// categories the backend may still report so the nav never shows a dead tab.
const visibleCategories = computed(() => categories.value.filter(cat => Object.prototype.hasOwnProperty.call(sectionComponents, cat.key)))

const tips = [
  __('Use export tags to make filenames dynamic.', 'woocommerce-exporter'),
  __('Enable archives to keep a history of exported files.', 'woocommerce-exporter'),
  __('UTF-8 is recommended for international data.', 'woocommerce-exporter')
]

// One plural sentence so translators control the whole notice; the count is
// filled by I18nText slot 1 and shown in bold.
/* translators: %1$s: number of unsaved settings changes, shown in bold */
const unsavedMessage = computed(() => _n(
  'You have %1$s unsaved change',
  'You have %1$s unsaved changes',
  unsavedCount.value,
  'woocommerce-exporter'
))

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
  return cat ? cat.label : __('Settings', 'woocommerce-exporter')
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

// Anything the writer cannot use is resolved to a comma BEFORE the lookup, so
// the summary always names the separator an export would really apply. A
// delimiter outside the table (a Custom '^' or '~') is shown as itself.
const delimiterDisplay = computed(() => {
  const delimiter = decodeStoredDelimiter(settings.value.delimiter)
  const effective = isValidDelimiter(delimiter) ? delimiter : ','

  return SETTINGS_SUMMARY_LABELS[effective] ?? effective
})

// The settings endpoint refuses a delimiter the CSV writer cannot use, and it
// refuses the WHOLE payload, so a half-typed Custom delimiter would take every
// other edit on the screen down with it. The same rule runs server-side.
const delimiterIsUsable = computed(() => isValidDelimiter(settings.value.delimiter))

// Shown as text beside Save, not only as its title: a disabled button takes no
// focus, so a tooltip on it reaches neither a screen reader nor a mouse.
const saveDisabledReason = computed(() => (
  delimiterIsUsable.value
    ? ''
    : __('Enter a valid Field Delimiter under Export Formats before saving.', 'woocommerce-exporter')
))
</script>

<template>
  <main
    class="wse-main wse-settings"
    style="height: calc(100vh - 32px); overflow: hidden; padding: 0; gap: 0"
    data-testid="app-main"
  >

    <!-- Page header -->
    <div class="flex items-start justify-between px-7 pt-6 pb-5 flex-shrink-0">
      <div>
        <h1 class="wse-page-title">{{ __('Settings', 'woocommerce-exporter') }}</h1>
        <p class="wse-page-sub">{{ __('Manage export behavior, formatting, automation, and integrations.', 'woocommerce-exporter') }}</p>
      </div>
      <div class="flex items-center gap-3">
        <div class="wse-search-field flex items-center gap-2 py-2 px-3 border border-gray-200 rounded-lg bg-white min-w-[210px] focus-within:border-indigo-400 transition-colors">
          <Search :size="14" color="#9CA3AF" :stroke-width="1.75" />
          <input type="text" :placeholder="__('Search settings...', 'woocommerce-exporter')" class="flex-1 text-[13px] text-gray-900 placeholder:text-gray-400" />
        </div>
        <button
          type="button"
          class="wse-settings-reset inline-flex items-center gap-2 py-[9px] px-4 border border-gray-200 rounded-lg bg-white text-gray-700 text-[13px] font-medium leading-none transition-colors hover:border-indigo-300 hover:text-indigo-600"
          @click="store.resetToSaved()"
        >
          <RotateCcw :size="13" :stroke-width="2" />
          {{ __('Reset Defaults', 'woocommerce-exporter') }}
        </button>
        <span
          v-if="saveDisabledReason"
          role="status"
          class="max-w-[220px] text-[12px] text-red-600 leading-tight"
          data-testid="settings-header-save-disabled-reason"
        >{{ saveDisabledReason }}</span>
        <BaseButton class="wse-settings-save" :loading="isSaving" :disabled="!delimiterIsUsable" :title="saveDisabledReason" data-testid="settings-header-save" @click="handleSave">
          <template #icon><Save :size="14" :stroke-width="2" /></template>
          {{ isSaving ? __('Saving…', 'woocommerce-exporter') : saveFeedbackText === 'saved' ? __('Saved!', 'woocommerce-exporter') : __('Save Changes', 'woocommerce-exporter') }}
        </BaseButton>
        <PageActions />
      </div>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="flex-1 wse-loading">
      <span class="wse-spinner" />{{ __('Loading settings…', 'woocommerce-exporter') }}
    </div>

    <!-- Error -->
    <div v-else-if="error" class="mx-7 flex-shrink-0 wse-error">
      <AlertCircle :size="16" color="#DC2626" :stroke-width="1.75" />{{ error }}
    </div>

    <template v-else>

      <!-- A rejected save: shown ABOVE the form, never in place of it, so the
           control the message is about is still on screen to be corrected. -->
      <div v-if="saveError" class="mx-7 mb-4 flex-shrink-0 wse-error" data-testid="settings-save-error">
        <AlertCircle :size="16" color="#DC2626" :stroke-width="1.75" />{{ saveError }}
      </div>

      <!-- 3-column grid -->
      <div
        class="flex-1 min-h-0 grid gap-5"
        style="grid-template-columns: 268px 1fr 258px; grid-template-rows: 1fr; padding: 0 28px"
      >

        <!-- Left: Category Nav -->
        <div class="overflow-hidden bg-white border border-gray-200 rounded-[10px] flex flex-col mb-4">
          <div class="py-3 px-4 border-b border-gray-100 flex-shrink-0">
            <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-[0.08em] m-0">{{ __('Settings', 'woocommerce-exporter') }}</p>
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
              <h3 class="text-[13px] font-semibold text-gray-900 m-0">{{ __('Configuration Summary', 'woocommerce-exporter') }}</h3>
            </div>
            <div class="flex flex-col gap-2.5">
              <div class="flex items-center justify-between text-[12px]">
                <span class="text-gray-500">{{ __('Archives', 'woocommerce-exporter') }}</span>
                <span
                  class="inline-flex items-center py-[2px] px-2 rounded-full text-[11px] font-semibold border"
                  :class="settings.enable_archive
                    ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                    : 'bg-gray-50 text-gray-500 border-gray-200'"
                >{{ settings.enable_archive ? __('Enabled', 'woocommerce-exporter') : __('Disabled', 'woocommerce-exporter') }}</span>
              </div>
              <div class="flex items-center justify-between text-[12px]">
                <span class="text-gray-500">{{ __('Date Format', 'woocommerce-exporter') }}</span>
                <span class="font-semibold text-gray-900 font-mono text-[11px]">{{ settings.date_format }}</span>
              </div>
              <div class="flex items-center justify-between text-[12px]">
                <span class="text-gray-500">{{ __('CSV Delimiter', 'woocommerce-exporter') }}</span>
                <span class="font-semibold text-gray-900" data-testid="settings-summary-csv-delimiter">{{ delimiterDisplay }}</span>
              </div>
              <div class="flex items-center justify-between text-[12px]">
                <span class="text-gray-500">{{ __('Character Encoding', 'woocommerce-exporter') }}</span>
                <span class="font-semibold text-gray-900">{{ settings.encoding }}</span>
              </div>
              <div class="flex items-center justify-between text-[12px]">
                <span class="text-gray-500">{{ __('Object Cache', 'woocommerce-exporter') }}</span>
                <span class="font-semibold text-gray-900">{{ settings.flush_cache === 'flush' ? __('Flush Enabled', 'woocommerce-exporter') : __('Disabled', 'woocommerce-exporter') }}</span>
              </div>
              <div class="flex items-center justify-between text-[12px]">
                <span class="text-gray-500">{{ __('Scheduled Exports', 'woocommerce-exporter') }}</span>
                <span
                  class="inline-flex items-center py-[2px] px-2 rounded-full text-[11px] font-semibold border"
                  :class="settings.enable_auto
                    ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                    : 'bg-gray-50 text-gray-500 border-gray-200'"
                >{{ settings.enable_auto ? __('Enabled', 'woocommerce-exporter') : __('Disabled', 'woocommerce-exporter') }}</span>
              </div>
              <div class="flex items-center justify-between text-[12px]">
                <span class="text-gray-500">{{ __('CRON', 'woocommerce-exporter') }}</span>
                <span
                  class="inline-flex items-center py-[2px] px-2 rounded-full text-[11px] font-semibold border"
                  :class="settings.enable_cron
                    ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                    : 'bg-gray-50 text-gray-500 border-gray-200'"
                >{{ settings.enable_cron ? __('Enabled', 'woocommerce-exporter') : __('Disabled', 'woocommerce-exporter') }}</span>
              </div>
            </div>
          </div>

          <!-- Tips -->
          <div class="bg-white border border-gray-200 rounded-[10px] p-4 flex-shrink-0">
            <div class="flex items-center gap-2 mb-3">
              <Lightbulb :size="14" color="#7986FF" :stroke-width="1.75" />
              <h3 class="text-[13px] font-semibold text-gray-900 m-0">{{ __('Tips', 'woocommerce-exporter') }}</h3>
            </div>
            <ul class="m-0 p-0 list-none flex flex-col gap-2 mb-3">
              <li
                v-for="tip in tips"
                :key="tip"
                class="flex items-start gap-1.5 text-[12px] text-gray-500"
              >
                <CheckCircle2 :size="13" color="#22C55E" :stroke-width="2" class="flex-shrink-0 mt-[1px]" />
                {{ tip }}
              </li>
            </ul>
            <a
              :href="KB_HUB_URL"
              target="_blank"
              rel="noopener noreferrer"
              class="text-[12px] text-indigo-600 no-underline hover:underline inline-flex items-center gap-1"
            >
              {{ __('See all tips', 'woocommerce-exporter') }}
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
            <I18nText :text="unsavedMessage">
              <template #1><strong>{{ formatCount(unsavedCount) }}</strong></template>
            </I18nText>
          </span>
        </div>
        <div class="flex items-center gap-2.5">
          <button
            type="button"
            class="wse-settings-reset inline-flex items-center gap-2 py-[9px] px-4 border border-gray-200 rounded-lg bg-white text-gray-700 text-[13px] font-medium leading-none transition-colors hover:border-indigo-300 hover:text-indigo-600"
            @click="store.resetToSaved()"
          >
            <RotateCcw :size="13" :stroke-width="2" />
            {{ __('Reset Changes', 'woocommerce-exporter') }}
          </button>
          <span
            v-if="saveDisabledReason"
            role="status"
            class="max-w-[260px] text-[12px] text-red-600 leading-tight"
            data-testid="settings-footer-save-disabled-reason"
          >{{ saveDisabledReason }}</span>
          <!-- Same guard as the header Save above. Without it this button posts
               the unusable delimiter and the endpoint refuses the WHOLE payload. -->
          <BaseButton class="wse-settings-save" :loading="isSaving" :disabled="!delimiterIsUsable" :title="saveDisabledReason" data-testid="settings-footer-save" @click="handleSave">
            <template #icon><Save :size="14" :stroke-width="2" /></template>
            {{ isSaving ? __('Saving…', 'woocommerce-exporter') : __('Save Changes', 'woocommerce-exporter') }}
          </BaseButton>
        </div>
      </div>

    </template>
  </main>
</template>
