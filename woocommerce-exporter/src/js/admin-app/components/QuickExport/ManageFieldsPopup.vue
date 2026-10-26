<script setup>
import { ref, computed, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { ChevronDown, ChevronRight } from 'lucide-vue-next'
import { useQuickExportStore } from '../../stores/quickExport'
import { useToastsStore } from '../../stores/toasts'
import FieldSelectPopup from '../ui/FieldSelectPopup.vue'
import { api } from '../../lib/api'

const store  = useQuickExportStore()
const toasts = useToastsStore()
const { currentFields, selectedType, currentFilters, customMetaValues, fieldSelections, fieldOrder } = storeToRefs(store)

// ── Custom Meta Fields ────────────────────────────────────────────────────────
const savedDefinitions   = ref([])
const expandedDefs       = ref({})
const loadingSavedMeta   = ref(false)

async function fetchSavedMeta (type) {
  if (!type) { savedDefinitions.value = []; return }
  loadingSavedMeta.value = true
  try {
    const data = await api.get(`custom-meta-fields/${type}`)
    const defs = (data.field_definitions ?? []).filter(
      d => d.availability?.quick_export !== false
    )
    savedDefinitions.value = defs
    defs.forEach(d => {
      if (expandedDefs.value[d.name] === undefined) expandedDefs.value[d.name] = false
    })
  } catch {
    savedDefinitions.value = []
  } finally {
    loadingSavedMeta.value = false
  }
}

watch(selectedType, fetchSavedMeta, { immediate: true })

function formatMetaKeyLabel (key) {
  const s = key.replace(/^_+/, '').replace(/_/g, ' ').trim()
  return s ? s.charAt(0).toUpperCase() + s.slice(1) : key
}

// ── Local working copy — mirrors the AddFieldsPopup pattern exactly ───────────
// Regular enabled fields + currently-selected meta keys, seeded at mount.
// All selection/reorder/remove operations work on this local array.
// Only onConfirm flushes back to the store.
function buildInitialSelected () {
  const result = []

  if (currentFields.value?.groups) {
    const all = []
    currentFields.value.groups.forEach(g => g.fields.forEach(f => all.push(f)))
    const enabled = all.filter(f => store.getFieldEnabled(selectedType.value, f.name))
    const order   = fieldOrder.value[selectedType.value]
    const sorted  = !order?.length ? enabled : [...enabled].sort((a, b) => {
      const ai = order.indexOf(a.name)
      const bi = order.indexOf(b.name)
      if (ai === -1 && bi === -1) return 0
      if (ai === -1) return 1
      if (bi === -1) return -1
      return ai - bi
    })
    sorted.forEach(f => result.push({
      ...f,
      label: store.getFieldLabel(selectedType.value, f.name, f.label),
    }))
  }

  // Append already-selected custom meta keys for every known definition.
  const typeMetaValues = customMetaValues.value[selectedType.value] ?? {}
  Object.entries(typeMetaValues).forEach(([defName, raw]) => {
    String(raw ?? '').split(/[\n,]+/).map(k => k.trim()).filter(Boolean).forEach(key => {
      result.push({ name: key, label: formatMetaKeyLabel(key), _isMeta: true, _defName: defName })
    })
  })

  return result
}

const localSelected = ref(buildInitialSelected())

// ── Groups (left-panel accordion) ─────────────────────────────────────────────
const groups = computed(() => {
  if (!currentFields.value?.groups) return []
  return currentFields.value.groups.filter(g => g.fields.length > 0)
})

// selectedNames drives FieldSelectPopup's "X selected" badge and left-panel
// checkbox state for regular fields.
const selectedNames = computed(() => localSelected.value.map(f => f.name))

// ── Custom meta helpers ────────────────────────────────────────────────────────
function isCustomKeySelected (key, defName) {
  return localSelected.value.some(f => f.name === key && f._isMeta && f._defName === defName)
}

function toggleCustomKey (key, defName) {
  const idx = localSelected.value.findIndex(f => f.name === key && f._isMeta && f._defName === defName)
  if (idx >= 0) {
    localSelected.value.splice(idx, 1)
  } else {
    localSelected.value.push({ name: key, label: formatMetaKeyLabel(key), _isMeta: true, _defName: defName })
  }
}

function selectedCountForDef (def) {
  return def.meta_keys.filter(k => isCustomKeySelected(k, def.name)).length
}

// ── Event handlers (same shape as AddFieldsPopup) ─────────────────────────────

function onToggle (name) {
  const idx = localSelected.value.findIndex(f => f.name === name && !f._isMeta)
  if (idx >= 0) {
    localSelected.value.splice(idx, 1)
  } else {
    let field = null
    for (const g of (currentFields.value?.groups ?? [])) {
      field = g.fields.find(f => f.name === name)
      if (field) break
    }
    if (field) {
      localSelected.value.push({
        ...field,
        label: store.getFieldLabel(selectedType.value, name, field.label),
      })
    }
  }
}

function onRemove (name) {
  const idx = localSelected.value.findIndex(f => f.name === name)
  if (idx >= 0) localSelected.value.splice(idx, 1)
}

function onReorder (names) {
  const map = new Map(localSelected.value.map(f => [f.name, f]))
  localSelected.value = names.map(n => map.get(n)).filter(Boolean)
}

// The label a field was registered with, so a cleared rename can be told apart
// from a real one. Falls back to the slug only when the definition is not
// loaded, which is also what onConfirm() assumed before this was shared.
function defaultLabelFor (name) {
  for (const g of (currentFields.value?.groups ?? [])) {
    const def = g.fields.find(gf => gf.name === name)
    if (def) return def.label
  }
  return name
}

function onSetLabel (name, label) {
  const f = localSelected.value.find(f => f.name === name && !f._isMeta)
  if (!f) return

  // An empty label means "undo the rename", so put the field default back rather
  // than keeping the row's current text. onConfirm() then sees label ===
  // default and clears the stored override, matching the inline chip. Keeping
  // the current text would re-save the override the user just cleared.
  f.label = label || defaultLabelFor(name)
  if (label) toasts.push(`Field renamed to "${label}"`)
}

function onSelectAllVisible (fields) {
  for (const f of fields) {
    if (localSelected.value.some(s => s.name === f.name && !s._isMeta)) continue
    let field = null
    for (const g of (currentFields.value?.groups ?? [])) {
      field = g.fields.find(gf => gf.name === f.name)
      if (field) break
    }
    if (field) {
      localSelected.value.push({
        ...field,
        label: store.getFieldLabel(selectedType.value, f.name, field.label),
      })
    }
  }
}

function onClearAll () {
  localSelected.value = []
  toasts.push('All fields cleared', 'info')
}

function onReset () {
  // Restore server-provided defaults; drop any meta selections.
  const result = []
  if (currentFields.value?.groups) {
    const all = []
    currentFields.value.groups.forEach(g => g.fields.forEach(f => all.push(f)))
    all.filter(f => f.selected).forEach(f => result.push({ ...f }))
  }
  localSelected.value = result
  toasts.push('Reset to default fields')
}

function onConfirm () {
  const type = selectedType.value
  if (!type) return

  const metaEntries    = localSelected.value.filter(f => f._isMeta)
  const regularEntries = localSelected.value.filter(f => !f._isMeta)
  const regularNameSet = new Set(regularEntries.map(f => f.name))

  // Apply regular field selections: enable selected, disable the rest.
  if (currentFields.value?.groups) {
    const overrides = {}
    currentFields.value.groups.forEach(g => g.fields.forEach(f => {
      overrides[f.name] = regularNameSet.has(f.name)
    }))
    if (!fieldSelections.value[type]) fieldSelections.value[type] = {}
    fieldSelections.value[type] = overrides
  }

  // Apply regular field drag order.
  fieldOrder.value[type] = regularEntries.map(f => f.name)

  // Persist inline label renames: only when label differs from the field default.
  // A label back at the default clears any override the store still holds, so
  // clearing a rename here removes it instead of leaving the old one in place.
  regularEntries.forEach(f => {
    const defaultLabel = defaultLabelFor(f.name)
    store.setFieldLabel(type, f.name, f.label && f.label !== defaultLabel ? f.label : '')
  })

  // Apply meta key selections — save each definition's keys to its own storage key.
  if (savedDefinitions.value.length) {
    if (!customMetaValues.value[type]) customMetaValues.value[type] = {}
    const updated = { ...customMetaValues.value[type] }
    savedDefinitions.value.forEach(def => {
      updated[def.name] = metaEntries
        .filter(f => f._defName === def.name)
        .map(f => f.name)
        .join('\n')
    })
    customMetaValues.value[type] = updated
  }

  store.saveFields()
  toasts.push('Fields saved')
  store.showManageFields = false
}

function onClose () {
  store.showManageFields = false
}
</script>

<template>
  <FieldSelectPopup
    title="Select Export Fields"
    subtitle="Choose the fields you want to include in your export and reorder them."
    footer-hint="The fields will be available to export in the exported file."
    confirm-label="Save Fields"
    :show-reset-default="true"
    :groups="groups"
    :selected-names="selectedNames"
    :selected-fields="localSelected"
    :loading="currentFields?.loading ?? false"
    :error="currentFields?.error ?? ''"
    extra-section-label="Custom Meta Fields"
    @toggle="onToggle"
    @remove="onRemove"
    @reorder="onReorder"
    @confirm="onConfirm"
    @close="onClose"
    @reset="onReset"
    @clear-all="onClearAll"
    @select-all-visible="onSelectAllVisible"
    @set-label="onSetLabel"
  >
    <template v-if="savedDefinitions.length || loadingSavedMeta" #extra-sections>
      <div v-if="loadingSavedMeta" class="flex items-center gap-2 px-4 py-3 text-[12.5px] text-gray-500">
        <span class="wse-spinner" /> Loading&hellip;
      </div>
      <template v-else>
        <div
          v-for="def in savedDefinitions.filter(d => d.meta_keys.length > 0)"
          :key="def.name"
        >
          <button
            type="button"
            class="flex items-center gap-2 w-full px-4 py-[10px] bg-white border-none border-b border-gray-100 cursor-pointer text-left transition-colors hover:bg-gray-50"
            :aria-expanded="String(!!expandedDefs[def.name])"
            :data-qe-meta-def="def.name"
            @click="expandedDefs[def.name] = !expandedDefs[def.name]"
          >
            <component
              :is="expandedDefs[def.name] ? ChevronDown : ChevronRight"
              :size="14"
              color="#9CA3AF"
              :stroke-width="2"
              class="flex-shrink-0"
            />
            <span class="flex-1 text-[13px] font-semibold text-gray-900">{{ def.label }}</span>
            <span class="text-[11px] font-medium text-indigo-600 bg-indigo-50 rounded-[5px] py-[3px] px-2 flex-shrink-0">
              {{ selectedCountForDef(def) }}/{{ def.meta_keys.length }}
            </span>
          </button>

          <div v-show="expandedDefs[def.name]">
            <ul class="list-none m-0 py-1 p-0">
              <li
                v-for="key in def.meta_keys"
                :key="key"
                class="flex items-center gap-[10px] py-[7px] pr-4 pl-[38px] cursor-pointer transition-colors hover:bg-gray-50"
                :data-qe-meta-key="key"
                :data-qe-meta-selected="String(isCustomKeySelected(key, def.name))"
                @click="toggleCustomKey(key, def.name)"
              >
                <span
                  class="w-4 h-4 rounded border flex-shrink-0 flex items-center justify-center transition-colors"
                  :class="isCustomKeySelected(key, def.name)
                    ? 'bg-indigo-600 border-indigo-600'
                    : 'bg-white border-gray-300'"
                >
                  <svg v-if="isCustomKeySelected(key, def.name)" width="10" height="8" viewBox="0 0 10 8" fill="none">
                    <path d="M1 4l3 3 5-6" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                </span>
                <span
                  class="text-[12.5px] font-mono"
                  :class="isCustomKeySelected(key, def.name) ? 'text-gray-900 font-medium' : 'text-gray-500'"
                >{{ formatMetaKeyLabel(key) }}</span>
              </li>
            </ul>
          </div>
        </div>
      </template>
    </template>
  </FieldSelectPopup>
</template>
