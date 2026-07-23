import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import { api } from '../lib/api'
import { prepareExport, processBatch, getExportNonce } from '../lib/exportApi'

// Steps: type → fields → filters → options
export const STEPS = ['type', 'fields', 'filters', 'options']

export const useQuickExportStore = defineStore('quickExport', () => {
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
  const exportProgress = ref(0)
  const exportTotal = ref(0)
  const exportProcessed = ref(0)
  const exportError = ref(null)
  const exportDownloadUrl = ref(null)

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

  async function loadConfig () {
    if (config.value) return
    configLoading.value = true
    configError.value = null
    try {
      config.value = await api.get('quick-export/config')
      if (config.value?.formats?.length) {
        exportFormat.value = config.value.formats[0].value
      }
    } catch (err) {
      configError.value = err instanceof Error ? err.message : 'Failed to load export types.'
    } finally {
      configLoading.value = false
    }
  }

  // Force-refetch the config (record counts) without a page reload.
  // The `refresh=1` flag tells the REST endpoint to bust the cached
  // record-count transient before recomputing.
  async function refreshConfig () {
    const fresh = await api.get('quick-export/config?refresh=1')
    config.value = fresh
  }

  async function loadFields (type) {
    if (fieldsCache.value[type]) return
    fieldsCache.value[type] = { groups: [], loading: true, error: null }
    try {
      const data = await api.get(`quick-export/fields?type=${encodeURIComponent(type)}`)
      fieldsCache.value[type] = { groups: data.groups, loading: false, error: null }
    } catch (err) {
      fieldsCache.value[type] = { groups: [], loading: false, error: err instanceof Error ? err.message : 'Failed to load fields.' }
    }
  }

  async function loadFilters (type) {
    if (filtersCache.value[type]) return
    filtersCache.value[type] = { filters: [], sort: null, customMeta: [], extraOptions: [], typeLabel: '', loading: true, error: null }
    try {
      const data = await api.get(`quick-export/filters?type=${encodeURIComponent(type)}`)
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
      filtersCache.value[type] = { filters: [], sort: null, customMeta: [], extraOptions: [], typeLabel: type, loading: false, error: err instanceof Error ? err.message : 'Failed to load filters.' }
    }
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

  // Count of items selected for the sub-controls of a single filter — used
  // for the "X selected" chip in the Filters list.
  function filterSubSelectionCount (type, filter) {
    if (!filter?.sub_options?.length) return 0
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

  // Export
  async function startExport () {
    if (!selectedType.value) return

    showExportProgress.value = true
    exportState.value = 'preparing'
    exportProgress.value = 0
    exportTotal.value = 0
    exportProcessed.value = 0
    exportError.value = null
    exportDownloadUrl.value = null

    const nonce = getExportNonce()
    const type = selectedType.value
    const formData = buildFormData(type)

    try {
      const { total, batches } = await prepareExport({ type, nonce, formData })
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
        exportProcessed.value = batchData.exported ?? exportProcessed.value
        exportProgress.value = Math.round(((i + 1) / batchKeys.length) * 100)
        if (batchData.done) {
          exportDownloadUrl.value = batchData.file_url ?? null
          break
        }
      }

      exportState.value = 'done'
      exportProgress.value = 100
    } catch (err) {
      exportState.value = 'error'
      exportError.value = err instanceof Error ? err.message : 'An unexpected error occurred.'
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
      }
    }
  }

  function cancelExport () {
    showExportProgress.value = false
    exportState.value = 'idle'
    exportError.value = null
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
    exportFormat.value = config.value?.formats?.[0]?.value ?? 'csv'
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
  }

  // ─── Internal ────────────────────────────────────────────────────────────

  function buildFormData (type) {
    const params = new URLSearchParams()
    params.set('dataset', type)
    params.set('export_format', exportFormat.value)

    if (exportTemplate.value) params.set('export_template', exportTemplate.value)
    if (exportLimit.value) params.set('limit_volume', exportLimit.value)
    if (exportOffset.value) params.set('offset', exportOffset.value)
    if (exportBatchSize.value) params.set('batch_size', exportBatchSize.value)

    const names = enabledFieldNames.value
    const labels = fieldLabels.value[type] ?? {}
    names.forEach((name, i) => {
      params.set(`${type}_fields[${name}]`, 'on')
      params.set(`${type}_fields_order[${name}]`, String(i + 1))
      if (labels[name]) {
        params.set(`${type}_labels[${name}]`, labels[name])
      }
    })

    // Legacy "${type}_custom_fields[]" — only set when the user typed keys in
    // the generic textarea (kept for backwards compatibility).
    const metaKeys = customMetaKeys.value
      .split(/[\n,]+/)
      .map(k => k.trim())
      .filter(Boolean)
    metaKeys.forEach((key, i) => {
      params.set(`${type}_custom_fields[${i}]`, key)
    })

    // Type-specific custom meta textareas (custom_products, custom_orders, …)
    const metaValues = customMetaValues.value[type] ?? {}
    Object.entries(metaValues).forEach(([name, raw]) => {
      const keys = String(raw ?? '')
        .split(/[\n,]+/)
        .map(k => k.trim())
        .filter(Boolean)
      keys.forEach((key, i) => {
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
          if (raw.from) params.set(`${ctrl.name}_from`, String(raw.from))
          if (raw.to)   params.set(`${ctrl.name}_to`,   String(raw.to))
        } else if (typeof raw === 'string' && raw !== '') {
          params.set(ctrl.name, raw)
        }
      }
    }

    const sf = sortField.value[type]
    if (sf) {
      params.set(`${type}_orderby`, sf)
      params.set(`${type}_orderby_direction`, sortDirection.value[type] ?? 'ASC')
    }

    return params.toString()
  }

  return {
    config, configLoading, configError,
    activeStep, expandedStep,
    selectedType,
    fieldsCache, fieldSelections, fieldOrder, showManageFields,
    filtersCache, filterValues, filterSubValues, sortField, sortDirection,
    exportFormat, exportTemplate, exportLimit, exportOffset, exportBatchSize,
    customMetaKeys, fieldLabels, extraOptionValues, customMetaValues,
    showExportProgress, exportState, exportProgress, exportTotal,
    exportProcessed, exportError, exportDownloadUrl,
    currentTypeConfig, currentFields, currentFilters, hasFiltersStep,
    enabledFieldNames, isExporting, canRunExport,
    loadConfig, refreshConfig, loadFields, loadFilters,
    selectType, setExpandedStep,
    getFieldEnabled, toggleFieldSelection, removeField, setFieldLabel, getFieldLabel, setAllFields, resetFieldsToDefault, saveFields,
    confirmFilters, confirmOptions,
    setFilterValue, setFilterSubValue, getFilterSubValue, filterSubSelectionCount,
    setSortField, setSortDirection,
    startExport, closeExportProgress, cancelExport, reset, applyTemplate
  }
})
