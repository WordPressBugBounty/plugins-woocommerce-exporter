import { __ } from '@wordpress/i18n'
import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import { api } from '../lib/api'
import { prepareExport, processBatch, clearExport, getExportNonce } from '../lib/exportApi'
import { useUpsellStore } from './upsell'

// Steps: type → fields → filters → options
export const STEPS = ['type', 'fields', 'filters', 'options']

// Custom meta definitions whose export field name is NOT the meta key.
// custom_order_items and custom_order_products register their fields as
// `order_items_<sanitize_key(key)>`, custom_attributes as `attribute_<slug>`.
// The browser cannot reproduce sanitize_key() faithfully, so a key of these
// definitions is posted under the field name the server resolves for it
// (`meta_field_names`), never under a name built here.
// Keep in sync with PREFIXED_DEFINITIONS in
// includes/Classes/REST/Custom_Meta_Fields_Controller.php.
// The longest time a new export waits for a cancelled export to stop. A hung
// request must not keep the new export on "Preparing". If the server still
// holds the run lock after this time, the prepare step shows the error.
const PREVIOUS_RUN_TIMEOUT_MS = 30000

const PREFIXED_META_DEFINITIONS = ['custom_order_items', 'custom_order_products', 'custom_attributes']

/**
 * Stable sort of fields by their name's index in the saved column order.
 *
 * Shared by the store (exported column order) and the Manage Fields popup
 * (row order), so the two cannot drift apart.
 *
 * @param {Array}         items  Field names, or rows carrying a name.
 * @param {string[]|null} order  Saved column order, if any.
 * @param {Function}      nameOf Maps an item to its field name.
 * @return {Array} Items whose name is in `order` first, by index; the rest after, in place.
 */
export function orderByFieldOrder (items, order, nameOf = item => item) {
  if (!order?.length) return items
  const rank = (item) => {
    const i = order.indexOf(nameOf(item))
    return i === -1 ? Infinity : i
  }
  return items
    .map((item, position) => ({ item, position, rank: rank(item) }))
    .sort((a, b) => (a.rank === b.rank ? a.position - b.position : (a.rank < b.rank ? -1 : 1)))
    .map(entry => entry.item)
}

export const useQuickExportStore = defineStore('quickExport', () => {
  const upsell = useUpsellStore()

  // ─── Config (loaded once) ────────────────────────────────────────────────
  const config = ref(null)
  const configLoading = ref(false)
  const configError = ref(null)

  // ─── Step state ──────────────────────────────────────────────────────────
  const activeStep = ref('type')    // furthest step reached
  const expandedStep = ref('type')  // which accordion is open

  // ─── Type selection ──────────────────────────────────────────────────────
  const selectedType = ref(null)

  // ─── Fields ──────────────────────────────────────────────────────────────
  // { [type]: { groups: [...], loading: bool, error: str|null } }
  const fieldsCache = ref({})
  // In-flight fetch promises per type so callers can await an already-running
  // load instead of racing it (the cache holds a { loading: true } placeholder
  // the instant a fetch starts, so a plain cache check can't tell "loaded" from
  // "still loading").
  const fieldsLoading = {}
  const filtersLoading = {}
  // { [type]: { [fieldName]: bool } } – overrides the defaults from fieldsCache
  const fieldSelections = ref({})
  // { [type]: string[] } – ordered list of enabled field names
  const fieldOrder = ref({})
  const showManageFields = ref(false)

  // ─── Filters & Sorting ───────────────────────────────────────────────────
  // { [type]: { filters: [...], sort: {...}|null, loading: bool, error: str|null } }
  const filtersCache = ref({})
  // { [type]: { [filterKey]: bool } }
  const filterValues = ref({})
  // Per-filter sub-control values keyed by the form-data name returned by the
  // REST endpoint. Shape: { [type]: { [filterKey]: { [controlName]: any } } }.
  const filterSubValues = ref({})
  const sortField = ref({})     // { [type]: string }
  const sortDirection = ref({}) // { [type]: 'ASC'|'DESC' }
  // { [type]: [{ field, direction }, { field, direction }] } – "Then by" levels
  // that break ties of the primary sort (only types whose sort advertises levels).
  const sortLevels = ref({})

  // ─── Export Options ──────────────────────────────────────────────────────
  const exportFormat = ref('csv')
  const exportTemplate = ref('')
  const exportLimit = ref('')
  const exportOffset = ref('')
  const exportBatchSize = ref('')
  const customMetaKeys = ref('')
  // Per-type custom labels: { [type]: { [fieldName]: 'Custom Label' } }
  const fieldLabels = ref({})
  // Per-type extra option values (description_excerpt_formatting, product_image_formatting, ...).
  const extraOptionValues = ref({})
  // Per-type custom meta textareas: { [type]: { [optionName]: 'lines\nof\nkeys' } }
  const customMetaValues = ref({})

  // ─── Export progress (modal) ─────────────────────────────────────────────
  const showExportProgress = ref(false)
  const exportState = ref('idle') // idle | preparing | exporting | done | error
  // Label/value rows returned by the server's debugging-mode gate, rendered in
  // the progress dialog so the refusal explains which request was refused.
  const exportDiagnostics = ref([])
  const exportProgress = ref(0)
  const exportTotal = ref(0)
  const exportProcessed = ref(0)
  const exportError = ref(null)
  const exportDownloadUrl = ref(null)
  // Post-export prompt from the last batch reply ('upgrade' or null) and the
  // upgrade link for the Export Complete dialog's upgrade card.
  const postExportPrompt = ref(null)
  const upgradeUrl = ref(null)
  // Incremented by cancelExport(). A running startExport() compares it with
  // its own value and stops, so a cancelled export does not reach `done`.
  let exportRun = 0
  // Settles when the last export has stopped and, after a cancel, has released
  // the server's run lock. A new export waits for it, so a rerun right after a
  // cancel does not fail with "An export is already running".
  let lastRunSettled = Promise.resolve()

  // ─── Computed ────────────────────────────────────────────────────────────
  const currentTypeConfig = computed(() => {
    if (!config.value || !selectedType.value) return null
    return config.value.types.find(t => t.slug === selectedType.value) ?? null
  })

  const currentFields = computed(() => {
    if (!selectedType.value) return null
    return fieldsCache.value[selectedType.value] ?? null
  })

  const currentFilters = computed(() => {
    if (!selectedType.value) return null
    return filtersCache.value[selectedType.value] ?? null
  })

  // True when the selected export type exposes at least one filter or a
  // sort control. Drives whether the "Filters & Sorting" step renders and
  // whether navigation skips past it.
  const hasFiltersStep = computed(() => {
    const f = currentFilters.value
    if (!f || f.loading) return false
    return (Array.isArray(f.filters) && f.filters.length > 0) || !!f.sort
  })

  const enabledFieldNames = computed(() => {
    if (!selectedType.value) return []
    const type = selectedType.value
    const cached = fieldsCache.value[type]
    if (!cached || !cached.groups) return []

    const overrides = fieldSelections.value[type]
    const orderedNames = fieldOrder.value[type]

    // Build the flat list of fields respecting selection state
    const allFields = []
    cached.groups.forEach(g => g.fields.forEach(f => allFields.push(f)))

    const enabled = allFields.filter(f => {
      if (overrides && Object.prototype.hasOwnProperty.call(overrides, f.name)) {
        return overrides[f.name]
      }
      return f.selected
    })

    if (orderedNames && orderedNames.length) {
      enabled.sort((a, b) => {
        const ai = orderedNames.indexOf(a.name)
        const bi = orderedNames.indexOf(b.name)
        if (ai === -1 && bi === -1) return 0
        if (ai === -1) return 1
        if (bi === -1) return -1
        return ai - bi
      })
    }

    return enabled.map(f => f.name)
  })

  const isExporting = computed(() => ['preparing', 'exporting'].includes(exportState.value))

  const canRunExport = computed(() => !!selectedType.value && !isExporting.value)

  // ─── Actions ─────────────────────────────────────────────────────────────

  // The first non-premium format in the server-provided list, falling back to
  // 'csv' — used to seed exportFormat so reordering that list server-side can
  // never default a free site onto a gated (Deluxe-only) format.
  function firstFreeFormat () {
    return config.value?.formats?.find(f => !f.premium)?.value ?? 'csv'
  }

  async function loadConfig () {
    if (config.value) return
    configLoading.value = true
    configError.value = null
    try {
      config.value = await api.get('quick-export/config')
      if (config.value?.formats?.length) {
        exportFormat.value = firstFreeFormat()
      }
    } catch (err) {
      configError.value = err instanceof Error ? err.message : __('Failed to load export types.', 'woocommerce-exporter')
    } finally {
      configLoading.value = false
    }
  }

  // Force-refetch the config (record counts) without a page reload.
  // The `refresh=1` flag tells the REST endpoint to bust the cached
  // record-count transient before recomputing.
  async function refreshConfig () {
    const fresh = await api.get('quick-export/config', { params: { refresh: 1 } })
    config.value = fresh
  }

  async function loadFields (type) {
    const cached = fieldsCache.value[type]
    if (cached && !cached.loading) return
    if (fieldsLoading[type]) return fieldsLoading[type]
    fieldsCache.value[type] = { groups: [], loading: true, error: null }
    fieldsLoading[type] = (async () => {
      try {
        const data = await api.get('quick-export/fields', { userLocale: false, params: { type } })
        const customMeta = data.custom_meta_fields ?? []
        fieldsCache.value[type] = { groups: data.groups, customMeta, metaFieldNames: data.meta_field_names ?? {}, loading: false, error: null }
        seedSelectedCustomMeta(type, customMeta)
        seedFieldOrder(type, customMeta, data.field_order ?? [], data.saved_field_order ?? [])
      } catch (err) {
        fieldsCache.value[type] = { groups: [], loading: false, error: err instanceof Error ? err.message : __('Failed to load fields.', 'woocommerce-exporter') }
      } finally {
        delete fieldsLoading[type]
      }
    })()
    return fieldsLoading[type]
  }

  // Registered custom meta keys are not part of the served field groups: they
  // are listed once, in their dedicated Manage Fields section, which reads
  // customMetaValues. Seed the keys the user saved as enabled so they stay
  // ticked, but never overwrite a selection the user already made.
  function seedSelectedCustomMeta (type, customMeta) {
    const selectedByDefinition = {}
    customMeta.forEach((entry) => {
      // Only entries offered by the Quick Export picker are seeded.
      if (!entry.selected || entry.available === false) return
      if (!selectedByDefinition[entry.definition]) selectedByDefinition[entry.definition] = []
      selectedByDefinition[entry.definition].push(entry.name)
    })

    if (!customMetaValues.value[type]) customMetaValues.value[type] = {}
    Object.entries(selectedByDefinition).forEach(([definition, keys]) => {
      const current = customMetaValues.value[type][definition]
      if (current === undefined || current === '') {
        customMetaValues.value[type][definition] = keys.join('\n')
      }
    })
  }

  // Seed the column order for a type that has none yet. An order the user
  // dragged this session is never overwritten.
  //
  // `savedOrder` is the order the last export of this type ran with, flat and
  // across groups. Without it the order is read off the served groups, which
  // keeps a move within a group but loses one between groups — so a column
  // dragged out of its section reset on every reload. It is empty until the
  // store has run an export of this type — the positions are recorded by the
  // export, not by the reorder — which keeps a store that has never exported
  // on the grouped order.
  //
  // `servedOrder` is the pre-strip group order, the fallback that predates it:
  // a saved meta key used to be served as an ordinary field and exported at its
  // group position, so seeding it keeps that position on the first export after
  // the upgrade that moved the key into its own section.
  function seedFieldOrder (type, customMeta, servedOrder, savedOrder) {
    if (fieldOrder.value[type]?.length) return
    if (savedOrder.length) {
      fieldOrder.value[type] = [...savedOrder]
      return
    }
    if (!servedOrder.length) return
    const hasSavedMeta = customMeta.some(entry => entry.selected && entry.available !== false)
    if (hasSavedMeta) fieldOrder.value[type] = [...servedOrder]
  }

  async function loadFilters (type) {
    const cached = filtersCache.value[type]
    if (cached && !cached.loading) return
    if (filtersLoading[type]) return filtersLoading[type]
    filtersCache.value[type] = { filters: [], sort: null, customMeta: [], extraOptions: [], typeLabel: '', loading: true, error: null }
    filtersLoading[type] = (async () => {
      try {
        // The context names the requesting surface, so the endpoint can apply
        // each custom meta definition's "Available In" Quick Export flag.
        const data = await api.get('quick-export/filters', { params: { type, context: 'quick-export' } })
        filtersCache.value[type] = {
          filters: data.filters,
          sort: data.sort,
          customMeta: data.custom_meta ?? [],
          extraOptions: data.extra_options ?? [],
          typeLabel: data.type_label ?? type,
          loading: false,
          error: null
        }
        // Seed default extra-option / custom-meta values from server.
        if (!extraOptionValues.value[type]) extraOptionValues.value[type] = {}
        ;(data.extra_options ?? []).forEach((opt) => {
          if (extraOptionValues.value[type][opt.key] === undefined) {
            extraOptionValues.value[type][opt.key] = opt.value
          }
        })
        if (!customMetaValues.value[type]) customMetaValues.value[type] = {}
        ;(data.custom_meta ?? []).forEach((def) => {
          if (customMetaValues.value[type][def.name] === undefined) {
            // Seed as empty so no meta keys are pre-selected when the popup opens.
            // The available keys come from the custom-meta-fields REST API loaded by
            // ManageFieldsPopup; customMetaValues tracks only what the user explicitly
            // selects for this export run.
            customMetaValues.value[type][def.name] = ''
          }
        })
      } catch (err) {
        filtersCache.value[type] = { filters: [], sort: null, customMeta: [], extraOptions: [], typeLabel: type, loading: false, error: err instanceof Error ? err.message : __('Failed to load filters.', 'woocommerce-exporter') }
      } finally {
        delete filtersLoading[type]
      }
    })()
    return filtersLoading[type]
  }

  function selectType (slug) {
    // Premium export types are Deluxe-only — ignore selection so the free
    // plugin never loads their fields or runs their export. The UI routes
    // premium tiles to the upsell link instead.
    const type = config.value?.types?.find(t => t.slug === slug)
    if (type?.premium) return
    selectedType.value = slug
    expandedStep.value = 'fields'
    activeStep.value = 'fields'
    loadFields(slug)
    loadFilters(slug)
  }

  /**
   * Select an export type requested from outside the app (?type=product).
   * Unknown or missing slugs are ignored so Quick Export opens with nothing
   * selected. A premium type is ignored by selectType() as well, so its tile
   * keeps the upsell behaviour.
   *
   * @param {string} slug - Export type slug from the URL.
   */
  function preselectType (slug) {
    if (!slug || !config.value?.types?.some(t => t.slug === slug)) return
    selectType(slug)
  }

  function setExpandedStep (step) {
    expandedStep.value = step
  }

  // Field helpers
  function getFieldEnabled (type, fieldName) {
    const overrides = fieldSelections.value[type]
    if (overrides && Object.prototype.hasOwnProperty.call(overrides, fieldName)) {
      return overrides[fieldName]
    }
    const cached = fieldsCache.value[type]
    if (cached?.groups) {
      for (const g of cached.groups) {
        const f = g.fields.find(f => f.name === fieldName)
        if (f) return f.selected
      }
    }
    return true
  }

  function toggleFieldSelection (type, fieldName) {
    if (!fieldSelections.value[type]) fieldSelections.value[type] = {}
    const current = getFieldEnabled(type, fieldName)
    fieldSelections.value[type] = { ...fieldSelections.value[type], [fieldName]: !current }
  }

  function removeField (type, fieldName) {
    if (!fieldSelections.value[type]) fieldSelections.value[type] = {}
    fieldSelections.value[type] = { ...fieldSelections.value[type], [fieldName]: false }
  }

  function setFieldLabel (type, fieldName, label) {
    if (!fieldLabels.value[type]) fieldLabels.value[type] = {}
    const trimmed = (label ?? '').trim()
    const next = { ...fieldLabels.value[type] }
    if (trimmed) {
      next[fieldName] = trimmed
    } else {
      delete next[fieldName]
    }
    fieldLabels.value[type] = next
  }

  function getFieldLabel (type, fieldName, fallback) {
    return fieldLabels.value[type]?.[fieldName] ?? fallback
  }

  function setAllFields (type, enabled) {
    const cached = fieldsCache.value[type]
    if (!cached?.groups) return
    const all = {}
    cached.groups.forEach(g => g.fields.forEach(f => { all[f.name] = enabled }))
    fieldSelections.value[type] = all
  }

  // Reset selections back to the server-provided defaults (the `selected` flag
  // on each field) and clear any drag-ordered or renamed customizations.
  function resetFieldsToDefault (type) {
    if (!type) return
    const next = { ...fieldSelections.value }
    delete next[type]
    fieldSelections.value = next
    const order = { ...fieldOrder.value }
    delete order[type]
    fieldOrder.value = order
    const labels = { ...fieldLabels.value }
    delete labels[type]
    fieldLabels.value = labels
  }

  function saveFields () {
    showManageFields.value = false
    // Skip the Filters & Sorting step when the selected export type has
    // neither filters nor sortable fields (e.g. Attributes) so the user
    // isn't dropped onto an empty panel.
    const next = hasFiltersStep.value ? 'filters' : 'options'
    expandedStep.value = next
    activeStep.value = next
  }

  function confirmFilters () {
    expandedStep.value = 'options'
    activeStep.value = 'options'
  }

  function confirmOptions () {
    expandedStep.value = 'custom'
    activeStep.value = 'custom'
  }

  function setFilterValue (type, key, value) {
    if (!filterValues.value[type]) filterValues.value[type] = {}
    filterValues.value[type] = { ...filterValues.value[type], [key]: value }
  }

  function setFilterSubValue (type, filterKey, controlName, value) {
    if (!filterSubValues.value[type]) filterSubValues.value[type] = {}
    const forType = { ...filterSubValues.value[type] }
    const forFilter = { ...(forType[filterKey] ?? {}), [controlName]: value }
    forType[filterKey] = forFilter
    filterSubValues.value[type] = forType
  }

  function getFilterSubValue (type, filterKey, controlName) {
    return filterSubValues.value[type]?.[filterKey]?.[controlName]
  }

  /**
   * Whether a card that carries a multiselect currently holds a selection.
   *
   * A card with a multiselect is live only while that list holds something. Its
   * other sub-control modifies the list — the Match type on Categories / Tags /
   * SKU — rather than filtering on its own, and "exclude nothing" narrows
   * nothing, so counting that modifier after a Clear badged a card the export
   * then ignored.
   *
   * This is the one place that rule lives: the "X selected" chip reads it here
   * and FiltersStep's syncFilterEnabled() reads it through the store, so the
   * tick and the badge cannot drift apart again.
   *
   * @param {string} type   Export type slug.
   * @param {Object} filter The filter card definition.
   * @return {?boolean} Whether a list holds something, or null when the card
   *   carries no multiselect and the rule does not apply to it.
   */
  function filterHasMultiselectSelection (type, filter) {
    const multiselects = (filter?.sub_options ?? []).filter(ctrl => ctrl.type === 'multiselect')
    if (!multiselects.length) return null
    return multiselects.some(ctrl => {
      const v = filterSubValues.value[type]?.[filter.key]?.[ctrl.name]
      return Array.isArray(v) && v.length > 0
    })
  }

  // Count of items selected for the sub-controls of a single filter — used
  // for the "X selected" chip in the Filters list.
  function filterSubSelectionCount (type, filter) {
    if (!filter?.sub_options?.length) return 0
    if (filterHasMultiselectSelection(type, filter) === false) return 0
    let count = 0
    for (const ctrl of filter.sub_options) {
      const v = filterSubValues.value[type]?.[filter.key]?.[ctrl.name]
      if (Array.isArray(v)) count += v.length
      else if (ctrl.type === 'date_range' && v && typeof v === 'object') {
        if (v.from) count += 1
        if (v.to) count += 1
      } else if (v !== undefined && v !== null && v !== '' && v !== false) {
        count += 1
      }
    }
    return count
  }

  function setSortField (type, value) {
    sortField.value = { ...sortField.value, [type]: value }
  }

  function setSortDirection (type, value) {
    sortDirection.value = { ...sortDirection.value, [type]: value }
  }

  function updateSortLevel (type, index, patch) {
    const levels = (sortLevels.value[type] ?? [
      { field: '', direction: 'ASC' },
      { field: '', direction: 'ASC' }
    ]).map(level => ({ ...level }))
    levels[index] = { ...levels[index], ...patch }
    sortLevels.value = { ...sortLevels.value, [type]: levels }
  }

  function setSortLevelField (type, index, value) {
    updateSortLevel(type, index, { field: value })
  }

  function setSortLevelDirection (type, index, value) {
    updateSortLevel(type, index, { direction: value })
  }

  // Export
  async function startExport () {
    if (!selectedType.value) return

    const exportPromise = runExport(++exportRun, lastRunSettled)
    lastRunSettled = exportPromise.catch(() => {})
    return exportPromise
  }

  /**
   * Run one Quick Export: prepare it, then send its batches.
   *
   * @param {number}  run         - This export's value of `exportRun`.
   * @param {Promise} previousRun - Settles when the previous export has stopped.
   */
  async function runExport (run, previousRun) {
    showExportProgress.value = true
    exportState.value = 'preparing'
    exportProgress.value = 0
    exportTotal.value = 0
    exportProcessed.value = 0
    exportError.value = null
    exportDownloadUrl.value = null
    exportDiagnostics.value = []
    postExportPrompt.value = null
    upgradeUrl.value = null

    const nonce = getExportNonce()
    const type = selectedType.value

    // selectType() kicks off loadFields()/loadFilters() fire-and-forget. If the
    // export is triggered before those requests resolve, buildFormData() would
    // omit the field selection and the extra-option values, and the server
    // would fall back to defaults. Await both so the payload always reflects
    // the loaded configuration. A cancelled previous export can still have a
    // request in flight and hold the server's run lock, so wait for it too,
    // but not longer than PREVIOUS_RUN_TIMEOUT_MS.
    const previousRunTimeout = new Promise(resolve => setTimeout(resolve, PREVIOUS_RUN_TIMEOUT_MS))
    await Promise.all([loadFields(type), loadFilters(type), Promise.race([previousRun, previousRunTimeout])])

    const formData = buildFormData(type)

    // After a cancel, stop sending batches. Otherwise the export finishes in the
    // background and counts toward the post-export prompt. `holdsLock` is true
    // while the server holds its 30-minute run lock for this export; release
    // it so the next export can start. The final batch already released it.
    // When the request in flight at the cancel is the final batch, the server
    // has already counted the export, but the dialog is closed, so its upgrade
    // card does not show.
    const cancelled = async (holdsLock) => {
      if (run === exportRun) return false
      if (holdsLock) await clearExport({ type, nonce }).catch(() => {})
      return true
    }
    // A cancel can come while the fields, the filters or the previous export load.
    if (await cancelled(false)) return

    try {
      const { total, batches } = await prepareExport({ type, nonce, formData })
      if (await cancelled(true)) return
      exportTotal.value = total
      exportState.value = 'exporting'

      // The prepare step returns `batches` as the array of 1-based batch keys
      // (e.g. [1, 2, 3]); iterate those keys and send each as the step. Fall
      // back to a numeric count for resilience if a number is ever returned.
      const batchKeys = Array.isArray(batches)
        ? batches
        : Array.from({ length: Number(batches) || 0 }, (_, i) => i + 1)

      for (let i = 0; i < batchKeys.length; i++) {
        const batchData = await processBatch({ type, nonce, step: batchKeys[i] })
        if (await cancelled(!batchData.done)) return
        exportProcessed.value = batchData.exported ?? exportProcessed.value
        exportProgress.value = Math.round(((i + 1) / batchKeys.length) * 100)
        if (batchData.done) {
          exportDownloadUrl.value = batchData.file_url ?? null
          postExportPrompt.value = batchData.post_export_prompt ?? null
          upgradeUrl.value = batchData.upgrade_url ?? null
          break
        }
      }

      exportState.value = 'done'
      exportProgress.value = 100
    } catch (err) {
      // The server already released its lock for a failed request, and a
      // cancelled export has no dialog left to show the error in.
      if (run !== exportRun) return
      // A premium format/type reaching the server gate surfaces the shared
      // upsell modal instead of generic error text in the progress dialog.
      // Either gate can fire, so name whichever one is actually premium.
      if (err?.premium) {
        const fmt = config.value?.formats?.find(f => f.value === exportFormat.value)
        const type = config.value?.types?.find(t => t.slug === selectedType.value)
        showExportProgress.value = false
        exportState.value = 'idle'
        upsell.open({
          label: err.feature || (fmt?.premium ? fmt.label : (type?.label ?? '')),
          description: err.message,
          url: err.upsell,
        })
        return
      }
      // The debugging-mode gate refuses every export method. Keep its
      // diagnostics so the dialog can show what was refused; the error state
      // already suppresses the Download File button.
      if (err?.debug) {
        exportDiagnostics.value = Array.isArray(err.diagnostics) ? err.diagnostics : []
      }
      exportState.value = 'error'
      exportError.value = err instanceof Error ? err.message : __('An unexpected error occurred.', 'woocommerce-exporter')
    }
  }

  function closeExportProgress () {
    if (exportState.value === 'done' || exportState.value === 'error') {
      showExportProgress.value = false
      if (exportState.value === 'done') {
        reset()
      } else {
        exportState.value = 'idle'
        exportError.value = null
        exportDiagnostics.value = []
      }
    }
  }

  /**
   * Hide the upgrade card ("Maybe later"). The server shows it again on the
   * next 10th Quick Export.
   */
  function dismissPostExportPrompt () {
    postExportPrompt.value = null
    upgradeUrl.value = null
  }

  function cancelExport () {
    exportRun++
    showExportProgress.value = false
    exportState.value = 'idle'
    exportError.value = null
    exportDiagnostics.value = []
  }

  /**
   * Seed the Quick Export store from a saved template.
   * Called when the user clicks "Run Export" from the Templates list.
   * Awaits field loading so selections can be applied immediately.
   *
   * @param {object} templateData - Full template object from the REST API.
   */
  async function applyTemplate (templateData) {
    const type = templateData.type
    if (!type) return

    // Select the type and open the fields step
    selectedType.value = type
    expandedStep.value = 'fields'
    activeStep.value = 'fields'

    // Apply format — REST returns uppercase ('CSV'), store expects lowercase
    const format = (templateData.format ?? 'csv').toLowerCase()
    exportFormat.value = format

    // Await field loading so we can apply selections immediately
    await loadFields(type)
    loadFilters(type) // fire-and-forget

    const templateFields = templateData.fields ?? []
    if (templateFields.length > 0 && fieldsCache.value[type]?.groups) {
      // Disable every field, then enable only the template's fields
      const overrides = {}
      fieldsCache.value[type].groups.forEach(g => {
        g.fields.forEach(f => { overrides[f.name] = false })
      })
      const templateNameSet = new Set(templateFields.map(f => f.name))
      Object.keys(overrides).forEach(name => {
        if (templateNameSet.has(name)) overrides[name] = true
      })
      fieldSelections.value[type] = overrides

      // Restore the saved field order
      fieldOrder.value[type] = [...templateFields]
        .sort((a, b) => (a.order ?? 0) - (b.order ?? 0))
        .map(f => f.name)
    }
  }

  function reset () {
    selectedType.value = null
    exportFormat.value = firstFreeFormat()
    exportTemplate.value = ''
    exportLimit.value = ''
    exportOffset.value = ''
    exportBatchSize.value = ''
    customMetaKeys.value = ''
    activeStep.value = 'type'
    expandedStep.value = 'type'
    showManageFields.value = false
    showExportProgress.value = false
    exportState.value = 'idle'
    exportProgress.value = 0
    exportTotal.value = 0
    exportProcessed.value = 0
    exportError.value = null
    exportDownloadUrl.value = null
    postExportPrompt.value = null
    upgradeUrl.value = null
  }

  /**
   * Replace a type's resolved prefixed meta field names.
   *
   * The Manage Fields popup re-reads the saved definitions each time it opens,
   * so a key added in Custom Meta Fields after the fields were cached gets its
   * field name here instead of being dropped from the export.
   *
   * @param {string} type  Export type slug.
   * @param {Object} names Field names keyed by definition, then by meta key.
   */
  function setMetaFieldNames (type, names) {
    if (!fieldsCache.value[type] || !names) return
    fieldsCache.value[type].metaFieldNames = names
  }

  // The bare meta keys of the PLAIN picker definitions. A prefixed definition
  // (Order Item Meta, attributes) is listed under its bare key too, but its
  // exported column is the derived field name, so its key must not be read as
  // a column name.
  function plainCustomMetaNames (type) {
    const prefixed = new Set(Object.keys(fieldsCache.value[type]?.metaFieldNames ?? {}))
    return (fieldsCache.value[type]?.customMeta ?? [])
      .filter(entry => !prefixed.has(entry.definition))
      .map(entry => entry.name)
  }

  /**
   * The `column_key` the fields endpoint served for each picker row.
   *
   * @param {string} type Export type slug.
   * @return {Map<string, string>} Keyed by "<definition>\n<meta key>".
   */
  function pickerColumnKeys (type) {
    const served = new Map()
    ;(fieldsCache.value[type]?.customMeta ?? []).forEach((entry) => {
      served.set(`${entry.definition}\n${entry.name}`, entry.column_key || entry.name)
    })
    return served
  }

  /**
   * The name the saved column order and label override hold a meta row under.
   *
   * Normally the bare meta key — that is how the Manage Fields popup has always
   * placed a custom meta row. The exception is a prefixed definition (Order Item
   * Meta, attributes) whose key a plain definition registers as well: the two
   * export different columns (`order_items_gift` and `gift`), so sharing the
   * bare key would give them one slot in the saved order and reset the second
   * one onto the first's position. Such a row is held under its derived field
   * name instead.
   *
   * The endpoint decides this (`column_key`), so the browser cannot drift from
   * it; the rule is only recomputed for a key the picker does not list yet.
   *
   * @param {string}             type       Export type slug.
   * @param {string}             definition Definition the key belongs to.
   * @param {string}             key        The bare meta key.
   * @param {Map<string,string>} served     From pickerColumnKeys(), to build many at once.
   * @return {string} The name the column is held under.
   */
  function metaColumnKey (type, definition, key, served = pickerColumnKeys(type)) {
    const columnKey = served.get(`${definition}\n${key}`)
    if (columnKey) return columnKey
    const fieldName = fieldsCache.value[type]?.metaFieldNames?.[definition]?.[key]
    return fieldName && plainCustomMetaNames(type).includes(key) ? fieldName : key
  }

  /**
   * The column key of every ticked custom meta column, by the field name the
   * export posts it as.
   *
   * Mirrors selectedCustomMetaFields(), which decides what is posted, so a
   * column cannot be posted under one name and placed under another's key.
   *
   * @param {string} type Export type slug.
   * @return {Map<string, string>} Posted field name to the name it is held under.
   */
  function metaColumnKeysByFieldName (type) {
    const served = pickerColumnKeys(type)
    const prefixedNames = fieldsCache.value[type]?.metaFieldNames ?? {}
    const byFieldName = new Map()

    Object.entries(customMetaValues.value[type] ?? {}).forEach(([definition, raw]) => {
      const isPrefixed = PREFIXED_META_DEFINITIONS.includes(definition)
      parseMetaKeys(raw).forEach((key) => {
        const fieldName = isPrefixed ? prefixedNames[definition]?.[key] : key
        if (!fieldName || byFieldName.has(fieldName)) return
        byFieldName.set(fieldName, metaColumnKey(type, definition, key, served))
      })
    })

    return byFieldName
  }

  // ─── Internal ────────────────────────────────────────────────────────────

  /**
   * Split one custom meta textarea value into its keys.
   *
   * @param {string} raw Newline- or comma-separated meta keys.
   * @return {string[]} The trimmed, non-empty keys.
   */
  function parseMetaKeys (raw) {
    return String(raw ?? '')
      .split(/[\n,]+/)
      .map(k => k.trim())
      .filter(Boolean)
  }

  /**
   * The custom meta fields ticked for this type, as field name → meta key.
   *
   * Ordered by the definitions as `customMetaValues` holds them, and within a
   * definition by the order the keys were ticked.
   *
   * Keyed off `selectedType`, the same source `enabledFieldNames` reads, so
   * the meta keys and the dedupe set can never come from different types.
   *
   * A key of a prefixed definition is mapped to the field name the server
   * resolved for it. A key it has no name for (no longer registered, or its
   * column name owned by another field) is dropped rather than posted under a
   * name the engine does not know. The meta key is the raw key the user
   * typed; for a prefixed definition it differs from the field name.
   *
   * @return {Map<string, string>} Field name to meta key.
   */
  function selectedCustomMetaFields () {
    const fields = new Map()
    selectedCustomMetaColumns().forEach(({ fieldName, metaKey }) => fields.set(fieldName, metaKey))
    return fields
  }

  /**
   * The custom meta columns ticked for the selected type, in the order of
   * selectedCustomMetaFields(), with what a chip needs to show and edit them.
   *
   * The one place that decides which ticked keys are exported, so a view that
   * lists the meta columns (the Fields step chips) shows exactly the columns
   * the export posts, under the column key their position and label use.
   *
   * @return {Array<{fieldName: string, metaKey: string, definition: string, columnKey: string}>} The columns.
   */
  function selectedCustomMetaColumns () {
    const type = selectedType.value
    const metaValues = customMetaValues.value[type] ?? {}
    const prefixedNames = fieldsCache.value[type]?.metaFieldNames ?? {}
    const alreadyAsked = new Set(enabledFieldNames.value)
    const served = pickerColumnKeys(type)
    const seen = new Set()
    const columns = []

    Object.entries(metaValues).forEach(([definition, raw]) => {
      const isPrefixed = PREFIXED_META_DEFINITIONS.includes(definition)
      parseMetaKeys(raw).forEach((rawKey) => {
        const key = isPrefixed ? prefixedNames[definition]?.[rawKey] : rawKey
        if (!key) return
        // A key can still arrive from two places (a definition listed twice,
        // or a key equal to a built-in field). It cannot reach the file twice —
        // the params are a map keyed by field name — but asking twice would
        // leave a gap in the fields_order sequence, so it is only added once.
        if (alreadyAsked.has(key) || seen.has(key)) return
        seen.add(key)
        columns.push({
          fieldName: key,
          metaKey: rawKey,
          definition,
          columnKey: metaColumnKey(type, definition, rawKey, served)
        })
      })
    })

    return columns
  }

  /**
   * The columns a "Then by" level can sort on: the enabled fields plus the
   * ticked custom meta keys, labelled as the exported column header is.
   */
  const sortLevelFieldOptions = computed(() => {
    const type = selectedType.value
    if (!type) return []
    const labels = fieldLabels.value[type] ?? {}
    const fieldLabel = {}
    ;(fieldsCache.value[type]?.groups ?? []).forEach(g => g.fields.forEach(f => {
      fieldLabel[f.name] = f.label
    }))
    // A custom meta key has no served label; it gets the same "_my_key" →
    // "My key" label the Manage Fields popup shows for it.
    const metaLabel = (key) => {
      const s = key.replace(/^_+/, '').replace(/_/g, ' ').trim()
      return s ? s.charAt(0).toUpperCase() + s.slice(1) : key
    }
    // A rename is stored under the column key the popup names the row by, so
    // the lookup uses the same key buildFormData() labels the exported column
    // with. The default text still comes from the bare meta key.
    const metaFields = selectedCustomMetaFields()
    const columnKeys = metaColumnKeysByFieldName(type)
    const columnKeyOf = name => columnKeys.get(name) ?? name
    return [...enabledFieldNames.value, ...metaFields.keys()].map(name => ({
      value: name,
      label: labels[columnKeyOf(name)] || labels[name] || fieldLabel[name] || metaLabel(metaFields.get(name) ?? name)
    }))
  })

  /**
   * The field names the export posts, in column order: the ticked regular
   * fields, then the ticked custom meta fields, merged by the saved column order.
   *
   * @param {string} type Export type slug.
   * @return {{names: string[], columnKeyOf: Function}} The names, and a map from a name to the one its position and label are saved under.
   */
  function exportColumns (type) {
    const metaFields = selectedCustomMetaFields()
    const columnKeys = metaColumnKeysByFieldName(type)
    const columnKeyOf = name => columnKeys.get(name) ?? name
    const requested = [...enabledFieldNames.value, ...metaFields.keys()]
    return { names: orderByFieldOrder(requested, fieldOrder.value[type], columnKeyOf), columnKeyOf }
  }

  // Number of columns the export will post, for the field counters. Read from
  // the same list buildFormData() posts, so a counter cannot leave out the
  // custom meta fields the file gets.
  const exportFieldCount = computed(() => (selectedType.value ? exportColumns(selectedType.value).names.length : 0))

  function buildFormData (type) {
    const params = new URLSearchParams()
    params.set('dataset', type)
    params.set('export_format', exportFormat.value)

    if (exportTemplate.value) params.set('export_template', exportTemplate.value)
    if (exportLimit.value) params.set('limit_volume', exportLimit.value)
    if (exportOffset.value) params.set('offset', exportOffset.value)
    if (exportBatchSize.value) params.set('batch_size', exportBatchSize.value)

    // Regular fields first, then the custom meta keys the user ticked in the
    // Manage Fields popup. A meta key does not come through enabledFieldNames:
    // the fields endpoint strips registered meta keys from the served groups
    // (they are listed once, in their dedicated popup section), so the key is
    // added here, from the only place that knows it was ticked.
    //
    // The engine is already able to build the column. A definition's keys are
    // saved to the woo_ce_<definition> option, and the export type's
    // get_default_fields() registers each of them as a real field under that
    // exact name, which is what get_export_columns() matches a posted field
    // name against. Before this the browser simply never asked for it, so the
    // column was not blank — it was never created at all.
    //
    // The two lists are then merged by the saved column order so a meta column
    // keeps the position the user gave it. Names with no saved position stay
    // after the ordered ones, in their relative order.
    //
    // The popup saves a meta row's position and label under its column key, so
    // a prefixed field (order_items_<key>, attribute_<slug>) is placed and
    // labelled by its meta key — or by its own field name when a plain
    // definition registers that key too.
    const { names, columnKeyOf } = exportColumns(type)
    const labels = fieldLabels.value[type] ?? {}
    names.forEach((name, i) => {
      params.set(`${type}_fields[${name}]`, 'on')
      params.set(`${type}_fields_order[${name}]`, String(i + 1))
      const label = labels[columnKeyOf(name)] || labels[name]
      if (label) {
        params.set(`${type}_labels[${name}]`, label)
      }
    })

    // Legacy "${type}_custom_fields[]" — only set when the user typed keys in
    // the generic textarea (kept for backwards compatibility).
    const metaKeys = parseMetaKeys(customMetaKeys.value)
    metaKeys.forEach((key, i) => {
      params.set(`${type}_custom_fields[${i}]`, key)
    })

    // Type-specific custom meta textareas (custom_products, custom_orders, …)
    const metaValues = customMetaValues.value[type] ?? {}
    Object.entries(metaValues).forEach(([name, raw]) => {
      parseMetaKeys(raw).forEach((key, i) => {
        params.set(`${name}[${i}]`, key)
      })
    })

    // Extra options (description_excerpt_formatting, product_image_formatting, …)
    const extras = extraOptionValues.value[type] ?? {}
    Object.entries(extras).forEach(([key, val]) => {
      if (val !== '' && val !== null && val !== undefined) {
        params.set(key, String(val))
      }
    })

    const fv = filterValues.value[type] ?? {}
    const definedFilters = filtersCache.value[type]?.filters ?? []
    for (const [key, val] of Object.entries(fv)) {
      if (val !== false && val !== '' && val !== null && val !== undefined) {
        params.set(key, val === true ? '1' : String(val))
      }
    }
    // Merge per-filter sub-control values for any enabled filters.
    for (const filter of definedFilters) {
      if (!fv[filter.key]) continue
      const subs = filterSubValues.value[type]?.[filter.key] ?? {}
      for (const ctrl of filter.sub_options ?? []) {
        const raw = subs[ctrl.name]
        if (raw === undefined || raw === null) continue
        if (ctrl.type === 'multiselect' && Array.isArray(raw)) {
          // PHP form-data: repeat the bracketed name for each value.
          for (const v of raw) {
            if (v !== '' && v !== null && v !== undefined) params.append(ctrl.name, String(v))
          }
        } else if (ctrl.type === 'date_range' && typeof raw === 'object') {
          // Legacy readers key the bounds off <type>_dates_from / _to and switch
          // on a preset key (e.g. user_dates_filter) — the "specific" branch is
          // the only one that consumes the submitted bounds. Some controls use
          // non-derivable key names, so honour explicit keys when supplied.
          if (raw.from || raw.to) {
            // Fallback key derivation assumes the control name ends in `_filter`
            // (e.g. user_dates_filter -> user_dates_from/_to). Any control whose
            // name does not follow that convention MUST supply explicit
            // from_key/to_key (and preset_key) from the controller, or the
            // reader will never see the emitted bounds.
            const presetKey = ctrl.preset_key ?? ctrl.name
            const fromKey = ctrl.from_key ?? `${ctrl.name.replace(/_filter$/, '')}_from`
            const toKey = ctrl.to_key ?? `${ctrl.name.replace(/_filter$/, '')}_to`
            params.set(presetKey, 'specific')
            if (raw.from) params.set(fromKey, String(raw.from))
            if (raw.to)   params.set(toKey, String(raw.to))
          }
        } else if (ctrl.type === 'comparison_number') {
          // Both halves or neither. `woo_ce_product_dataset_args()` builds
          // product_quantity by concatenating the operator then the value and
          // yields false without an operator, so posting a lone value would
          // silently filter nothing.
          const operator = subs[ctrl.operator_name]
          if (raw !== '' && operator) {
            params.set(ctrl.name, String(raw))
            params.set(ctrl.operator_name, String(operator))
          }
        } else if (typeof raw === 'string' && raw !== '') {
          params.set(ctrl.name, raw)
        }
      }
    }

    // Every export type reads its direction from `<type>_order` (e.g.
    // `order_order`, `product_order`) — the same key the legacy export form
    // posts. It is only sent alongside a chosen sort field, so "Default
    // order" keeps each type's own backend default direction.
    const sf = sortField.value[type]
    if (sf) {
      params.set(`${type}_orderby`, sf)
      params.set(`${type}_order`, sortDirection.value[type] ?? 'ASC')
    }

    // "Then by" levels break the primary sort's ties. A level only counts when
    // its column is actually exported — a column deselected after choosing it
    // is dropped rather than posted stale.
    const postedNames = new Set(names)
    const levelDefinitions = filtersCache.value[type]?.sort?.levels ?? []
    levelDefinitions.forEach((definition, i) => {
      const level = sortLevels.value[type]?.[i]
      if (!level?.field || !postedNames.has(level.field)) return
      params.set(definition.field_key, level.field)
      params.set(definition.order_key, level.direction || 'ASC')
    })

    return params.toString()
  }

  return {
    config, configLoading, configError,
    activeStep, expandedStep,
    selectedType,
    fieldsCache, fieldSelections, fieldOrder, showManageFields,
    filtersCache, filterValues, filterSubValues, sortField, sortDirection, sortLevels,
    exportFormat, exportTemplate, exportLimit, exportOffset, exportBatchSize,
    customMetaKeys, fieldLabels, extraOptionValues, customMetaValues,
    showExportProgress, exportState, exportProgress, exportTotal,
    exportProcessed, exportError, exportDiagnostics, exportDownloadUrl,
    postExportPrompt, upgradeUrl,
    currentTypeConfig, currentFields, currentFilters, hasFiltersStep,
    enabledFieldNames, exportFieldCount, sortLevelFieldOptions, isExporting, canRunExport,
    loadConfig, refreshConfig, loadFields, loadFilters,
    selectType, preselectType, setExpandedStep,
    getFieldEnabled, toggleFieldSelection, removeField, setFieldLabel, getFieldLabel, setAllFields, resetFieldsToDefault, saveFields,
    confirmFilters, confirmOptions,
    setFilterValue, setFilterSubValue, getFilterSubValue, filterSubSelectionCount,
    filterHasMultiselectSelection,
    setSortField, setSortDirection, setSortLevelField, setSortLevelDirection,
    startExport, closeExportProgress, dismissPostExportPrompt, cancelExport, reset, applyTemplate, setMetaFieldNames,
    metaColumnKey, selectedCustomMetaColumns
  }
})
