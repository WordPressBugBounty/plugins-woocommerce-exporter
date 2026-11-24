<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { __, sprintf } from '@wordpress/i18n'
import { storeToRefs } from 'pinia'
import { useRoute, useRouter } from 'vue-router'
import {
  AlertCircle,
  ArrowLeft,
  CheckCircle2,
  HelpCircle
} from 'lucide-vue-next'
import BaseTooltip from '../components/ui/BaseTooltip.vue'
import BaseAvailabilityCheckboxes from '../components/ui/BaseAvailabilityCheckboxes.vue'
import { useCustomMetaFieldsStore } from '../stores/customMetaFields'

const route = useRoute()
const router = useRouter()
const store = useCustomMetaFieldsStore()

const {
  types,
  currentType,
  fieldDefinitions,
  isLoadingForm,
  isLoadingList,
  isSaving,
  formError
} = storeToRefs(store)

// Saved keys of an Order Item Meta / Order Item Product Meta definition that the
// export cannot write: their order_items_<key> column name is a built-in Order
// Items field, or another section's key already claimed it.
function unavailableKeys (defName) {
  return Object.entries(currentType.value?.unavailable_keys?.[defName] ?? {})
    .map(([key, fieldName]) => `${key} (${fieldName})`)
}

function unavailableNotice (defName) {
  return sprintf(
    /* translators: %s: comma-separated meta keys that are not exported, each followed by the column it clashes with */
    __('Not exported: %s. Another column already uses this column name, either a built-in Order Items field or the same key in another section. Rename the key to export it.', 'woocommerce-exporter'),
    unavailableKeys(defName).join(', ')
  )
}

const EXPORT_TYPE_OPTIONS = computed(() =>
  types.value.map(t => ({ value: t.type, label: t.label }))
)

const exportTypeLabel = computed(() => {
  const opt = EXPORT_TYPE_OPTIONS.value.find(o => o.value === route.params.type)
  return opt ? opt.label : route.params.type
})

const pageTitle = computed(() => {
  if (!isEditMode.value) return __('Add Meta Fields', 'woocommerce-exporter')
  return sprintf(
    /* translators: %s: export type name, e.g. Products */
    __('Edit %s Meta Fields', 'woocommerce-exporter'),
    exportTypeLabel.value
  )
})

const pageSubtitle = computed(() => {
  if (!isEditMode.value) return __('Choose an export type and define your custom meta fields.', 'woocommerce-exporter')
  return sprintf(
    /* translators: %s: export type name, e.g. Products */
    __('Define custom meta fields for the %s export type.', 'woocommerce-exporter'),
    exportTypeLabel.value
  )
})

/**
 * True when the current type already has fields saved — switches the form
 * into "edit" mode, which hides the type selector and updates the page title.
 * Falls back to the loaded form data when the types list isn't available
 * (e.g. navigating directly to the URL).
 */
const isEditMode = computed(() => {
  const listEntry = types.value.find(t => t.type === route.params.type)
  if (listEntry) return listEntry.field_count > 0
  return (currentType.value?.field_definitions?.length ?? 0) > 0
})

/** Slugs of export types that already have custom meta fields configured. */
const configuredTypeSlugs = computed(() => {
  return new Set(types.value.filter(t => t.field_count > 0).map(t => t.type))
})

const saveStatus = ref('')      // '' | 'saved' | 'error'
let saveStatusTimer = null

function loadType (typeSlug) {
  store.fetchType(typeSlug)
}

onMounted(() => {
  loadType(route.params.type)
  // Ensure the types list is available for isEditMode and the dropdown's
  // "already configured" guard, even when landing directly on the form URL.
  if (types.value.length === 0) {
    store.fetchTypes()
  }
})

// Reload when the route param changes (user changes type dropdown → router navigates).
watch(() => route.params.type, (newType) => {
  if (newType) loadType(newType)
})

function onTypeChange (event) {
  router.push(`/custom-meta-fields/${event.target.value}`)
}

async function handleSave () {
  clearTimeout(saveStatusTimer)
  saveStatus.value = ''
  try {
    await store.saveType(route.params.type)
    saveStatus.value = 'saved'
    if (!isEditMode.value) {
      // Navigate back to the listing after a brief pause so the user sees the
      // success confirmation before the view changes.
      saveStatusTimer = setTimeout(() => {
        saveStatus.value = ''
        router.push('/custom-meta-fields')
      }, 1200)
    } else {
      saveStatusTimer = setTimeout(() => { saveStatus.value = '' }, 3000)
    }
  } catch {
    saveStatus.value = 'error'
  }
}

function handleCancel () {
  router.push('/custom-meta-fields')
}
</script>

<template>
  <main class="wse-main" data-testid="app-main">

    <!-- Page header -->
    <div class="flex items-start justify-between gap-4 mb-6">
      <div>
        <h1 class="m-0 text-[24px] font-bold text-[#111827] leading-snug">
          {{ pageTitle }}
        </h1>
        <p class="m-0 mt-1 text-[14px] text-[#6B7280]">
          {{ pageSubtitle }}
        </p>
      </div>
      <button
        type="button"
        class="wse-cmf-back-btn"
        @click="handleCancel"
      >
        <ArrowLeft :size="15" :stroke-width="2" />
        {{ __('Custom Meta Fields', 'woocommerce-exporter') }}
      </button>
    </div>

    <!-- Loading -->
    <div v-if="isLoadingForm" class="flex items-center gap-2.5 text-[13px] text-gray-500 py-10 justify-center">
      <span class="wse-spinner" />
      {{ __('Loading…', 'woocommerce-exporter') }}
    </div>

    <!-- Error -->
    <div v-else-if="formError && !currentType" class="flex items-center gap-2 text-[13px] text-red-600 py-8 justify-center">
      <AlertCircle :size="16" :stroke-width="1.75" />
      {{ formError }}
    </div>

    <!-- Form card -->
    <section v-else class="bg-white border border-[#E5E7EB] rounded-[10px] overflow-hidden">

      <!-- Card header -->
      <div class="px-6 py-5 border-b border-[#F3F4F6]">
        <h2 class="m-0 text-[15px] font-semibold text-[#111827]">{{ __('Custom Field Configuration', 'woocommerce-exporter') }}</h2>
        <p class="m-0 mt-1 text-[13px] text-[#6B7280]">
          {{ isEditMode
            ? __('Define the meta keys and export availability for each custom field.', 'woocommerce-exporter')
            : __('Choose export type and define custom meta fields.', 'woocommerce-exporter') }}
        </p>
      </div>

      <div class="px-6">

        <!-- Export Type row — shown only when adding a new configuration -->
        <div v-if="!isEditMode" class="wse-cmf-form-row border-b border-[#F3F4F6]">
          <div class="wse-cmf-form-label">
            <label class="block text-[14px] font-semibold text-[#111827]" for="cmf-export-type">
              {{ __('Export Type', 'woocommerce-exporter') }}
            </label>
            <p class="m-0 mt-1 text-[13px] text-[#6B7280]">
              {{ __('Select the export type these fields belong to.', 'woocommerce-exporter') }}
            </p>
          </div>
          <div class="wse-cmf-form-field">
            <div class="flex items-center gap-2">
              <select
                id="cmf-export-type"
                class="wse-cmf-select flex-1"
                :value="route.params.type"
                :disabled="isLoadingList"
                @change="onTypeChange"
              >
                <option
                  v-for="opt in EXPORT_TYPE_OPTIONS"
                  :key="opt.value"
                  :value="opt.value"
                  :disabled="configuredTypeSlugs.has(opt.value)"
                >{{ configuredTypeSlugs.has(opt.value) ? '✓ ' + opt.label : opt.label }}</option>
              </select>
              <BaseTooltip :content="__('Select the export type these custom meta fields apply to. Each type maintains its own independent set of meta fields.', 'woocommerce-exporter')">
                <button type="button" class="flex-shrink-0 p-1 border-0 bg-transparent cursor-pointer text-[#9CA3AF] hover:text-gray-600">
                  <HelpCircle :size="16" :stroke-width="1.75" />
                </button>
              </BaseTooltip>
            </div>
          </div>
        </div>

        <!-- One row per field definition -->
        <div
          v-for="(def, idx) in fieldDefinitions"
          :key="def.name"
          class="wse-cmf-form-row"
          :class="idx < fieldDefinitions.length - 1 ? 'border-b border-[#F3F4F6]' : ''"
        >
          <div class="wse-cmf-form-label">
            <label
              :for="`cmf-meta-keys-${def.name}`"
              class="block text-[14px] font-semibold text-[#111827]"
            >
              {{ def.label }}
            </label>
            <p class="m-0 mt-1 text-[13px] text-[#6B7280]">
              {{ def.description || __('Enter one meta key per line.', 'woocommerce-exporter') }}
            </p>
          </div>
          <div class="wse-cmf-form-field">
            <textarea
              :id="`cmf-meta-keys-${def.name}`"
              v-model="def.metaKeysText"
              class="wse-cmf-textarea"
              placeholder="_meta_key_name"
              spellcheck="false"
            />
            <p
              v-if="unavailableKeys(def.name).length"
              class="wse-cmf-unavailable-notice"
              role="status"
            >
              {{ unavailableNotice(def.name) }}
            </p>
            <div class="wse-cmf-availability-box">
              <span class="wse-cmf-availability-box__heading">{{ __('Available In', 'woocommerce-exporter') }}</span>
              <BaseAvailabilityCheckboxes v-model="def.availability" />
              <p class="wse-cmf-availability-box__hint">
                {{ __('Selected export areas will have these fields available for use.', 'woocommerce-exporter') }}
              </p>
            </div>
          </div>
        </div>

      </div>

      <!-- Card footer — actions -->
      <div class="flex items-center justify-between gap-4 px-6 py-4 border-t border-[#F3F4F6]">
        <!-- Save feedback -->
        <div class="flex items-center gap-2 text-[13px]">
          <template v-if="saveStatus === 'saved'">
            <CheckCircle2 :size="15" color="#059669" :stroke-width="1.75" />
            <span class="text-emerald-600 font-medium">{{ __('Fields saved.', 'woocommerce-exporter') }}</span>
          </template>
          <template v-else-if="saveStatus === 'error' || formError">
            <AlertCircle :size="15" color="#DC2626" :stroke-width="1.75" />
            <span class="text-red-600">{{ formError ?? __('Save failed.', 'woocommerce-exporter') }}</span>
          </template>
        </div>

        <div class="flex items-center gap-3">
          <button
            type="button"
            class="wse-cmf-btn-cancel"
            :disabled="isSaving"
            @click="handleCancel"
          >
            {{ __('Cancel', 'woocommerce-exporter') }}
          </button>
          <button
            type="button"
            class="wse-cmf-btn-save"
            :disabled="isSaving"
            @click="handleSave"
          >
            <span v-if="isSaving" class="wse-spinner wse-spinner--sm wse-spinner--white" />
            {{ isSaving ? __('Saving…', 'woocommerce-exporter') : __('Save Fields', 'woocommerce-exporter') }}
          </button>
        </div>
      </div>

    </section>

  </main>
</template>

<style lang="scss" scoped src="./CustomMetaFieldsFormView.scss"></style>
