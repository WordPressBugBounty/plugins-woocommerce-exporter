import { ref, reactive, computed } from 'vue'
import { defineStore } from 'pinia'
import { api } from '../lib/api'

/**
 * Resolve whether debugging mode is genuinely gating exports.
 *
 * WOO_CE_DEBUG is the constant every server-side export gate tests, and
 * bootstrap.php defines it from the `debug_mode` option only when it is not
 * already set. A wp-config.php `define('WOO_CE_DEBUG', true)` therefore wins,
 * and constant and option can legitimately disagree (issue #89 / A25). Reading
 * the raw option would then hide the banner while every export is still
 * refused, with an Advanced toggle that reads "Off" and cannot fix it.
 *
 * So: when the server reports the constant is externally overridden, trust the
 * constant. Otherwise trust the option value the server just confirmed — on a
 * save the constant is one request stale (it froze at `init`, before the write)
 * while the option is exactly what the next request's constant will mirror.
 *
 * @param {object} response      Settings GET/POST response body.
 * @param {*}      optionValue   The `debug_mode` option value the server holds.
 * @returns {boolean}
 */
function resolveDebugModeActive (response, optionValue) {
  if (response?.debug_mode_overridden) return Boolean(response.debug_mode_active)
  return Boolean(optionValue)
}

export const useSettingsStore = defineStore('settings', () => {
  const isLoading = ref(false)
  const isSaving = ref(false)
  const error = ref(null)
  const activeCategory = ref('general')
  // Whether debugging mode is genuinely active on the server. Seeded from the
  // bootstrapped wseAdminData (which mirrors the WOO_CE_DEBUG constant) so the
  // banner is correct on every SPA screen, including Quick Export where
  // fetchSettings() is never called and `settings.debug_mode` is still the
  // hardcoded default. wp_localize_script stringifies scalars, so "1" / "".
  // Later updates go through resolveDebugModeActive(), never the raw option.
  const debugModeActive = ref(Boolean(window.wseAdminData?.debugMode))
  const categories = ref([])
  // Read-only Deluxe-gated format definitions (XML, RSS) for FormatsSection.vue's
  // locked cards — never POSTed back, so it lives outside the `settings` object.
  const premiumFormats = ref([])

  const context = reactive({
    export_templates: [],
    scheduled_exports: [],
    order_statuses: [],
    export_formats: []
  })

  const settings = reactive({
    // General
    filename: 'export-{type}-{store}-{date}-{time}',
    encoding: 'UTF-8',
    date_format: 'F j, Y',
    field_escape: 'all',
    header_formatting: 'include',
    bom: true,
    flush_cache: 'flush',
    excel_formulas: false,
    csv_formula_escaping: true,
    timeout: 0,
    // Archives
    enable_archive: false,
    // Scheduling
    enable_auto: false,
    // Export Formats - CSV
    delimiter: ',',
    category_separator: '|',
    line_ending_formatting: 'windows',
    // CRON Exports
    enable_cron: false,
    secret_key: '',
    cron_fields: 'all',
    cron_export_template: '',
    // Orders Screen
    order_actions_csv: true,
    order_actions_tsv: true,
    order_actions_xls: true,
    order_actions_xlsx: true,
    order_actions_xml: false,
    order_actions_order_items_formatting: 'unique',
    order_actions_fields: 'all',
    order_actions_export_template: '',
    // Export Triggers
    enable_trigger_new_order: false,
    trigger_new_order_status: 'processing',
    trigger_new_order_format: 'csv',
    trigger_new_order_method: 'archive',
    trigger_new_order_method_save_file_path: '',
    trigger_new_order_method_save_filename: '',
    trigger_new_order_method_email_to: '',
    trigger_new_order_method_email_subject: '',
    trigger_new_order_method_email_contents: '',
    trigger_new_order_method_post_to: '',
    trigger_new_order_method_scheduled_export: '',
    trigger_new_order_items_formatting: 'unique',
    trigger_new_order_fields: 'all',
    // Advanced
    debug_mode: false,
    logging_mode: false,
    // Audit Log — default false so the sidebar/section stay hidden until the
    // real value arrives from the server (see App.vue global fetchSettings call).
    audit_log_enabled: false,
    audit_log_sources: { quick_export: true, scheduled: true },
    audit_log_source_levels: { quick_export: 'standard', scheduled: 'standard' },
    audit_log_level: 'standard',
    audit_log_fields: {
      minimal: ['error_message'],
      standard: ['schedule_name', 'row_counts', 'error_message', 'export_format'],
      detailed: ['schedule_name', 'row_counts', 'error_message', 'file_path', 'progress_tracking', 'export_format', 'user_id', 'export_filename']
    }
  })

  const original = ref(null)

  // True once fetchSettings() has successfully returned from the server.
  const isSettingsLoaded = computed(() => original.value !== null)

  const isDirty = computed(() => {
    if (!original.value) return false
    return JSON.stringify(settings) !== JSON.stringify(original.value)
  })

  const unsavedCount = computed(() => {
    if (!original.value) return 0
    return Object.keys(settings).filter(k => settings[k] !== original.value[k]).length
  })

  async function fetchSettings () {
    if (isLoading.value) return // already in-flight; avoid double-fetch
    isLoading.value = true
    error.value = null
    try {
      const data = await api.get('settings')
      Object.keys(settings).forEach(k => {
        if (data[k] !== undefined) settings[k] = data[k]
      })
      categories.value = data.categories ?? []
      premiumFormats.value = data.premium_formats ?? []
      if (data.context) {
        Object.assign(context, data.context)
      }
      original.value = JSON.parse(JSON.stringify(settings))
      debugModeActive.value = resolveDebugModeActive(data, data.debug_mode)
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Failed to load settings.'
    } finally {
      isLoading.value = false
    }
  }

  async function saveSettings () {
    isSaving.value = true
    error.value = null
    try {
      // Snapshot BEFORE the await. `settings` is reactive and the user can keep
      // toggling while the request is in flight, so every post-await read of it
      // would describe a state this request never sent.
      const payload = { ...settings }
      const response = await api.post('settings', payload)
      original.value = JSON.parse(JSON.stringify(payload))
      // Only after the POST resolves: a dirty-but-unsaved toggle, or a save
      // that rejects, must never flip the banner.
      debugModeActive.value = resolveDebugModeActive(response, payload.debug_mode)
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Failed to save settings.'
      throw err
    } finally {
      isSaving.value = false
    }
  }

  function resetToSaved () {
    if (original.value) {
      Object.assign(settings, JSON.parse(JSON.stringify(original.value)))
    }
  }

  async function runAction (action) {
    try {
      return await api.post('settings/actions', { action })
    } catch (err) {
      throw err instanceof Error ? err : new Error('Action failed.')
    }
  }

  return {
    isLoading, isSaving, error, activeCategory, categories, context, premiumFormats,
    debugModeActive,
    settings, isDirty, unsavedCount, isSettingsLoaded,
    fetchSettings, saveSettings, resetToSaved, runAction
  }
})
