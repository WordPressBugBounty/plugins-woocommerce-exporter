<script setup>
import { ref, computed } from 'vue'
import { storeToRefs } from 'pinia'
import { __, sprintf } from '@wordpress/i18n'
import { Check, ChevronRight, Settings2, Plus, Move, RotateCcw, Pencil, X as XIcon } from 'lucide-vue-next'
import { useQuickExportStore } from '../../../stores/quickExport'
import { useToastsStore } from '../../../stores/toasts'
import { BaseInfoNotice } from '../../ui'

const store = useQuickExportStore()
const toasts = useToastsStore()
const { selectedType, currentFields, customMetaValues } = storeToRefs(store)

function formatMetaKeyLabel (key) {
  const s = key.replace(/^_+/, '').replace(/_/g, ' ').trim()
  return s ? s.charAt(0).toUpperCase() + s.slice(1) : key
}

// ── Field chips (enabled + ordered) ─────────────────────────────────────────
const fieldsChips = computed(() => {
  if (!selectedType.value || !currentFields.value?.groups) return []
  const all = []
  currentFields.value.groups.forEach(g => g.fields.forEach(f => all.push(f)))
  const enabled = all.filter(f => store.getFieldEnabled(selectedType.value, f.name))
  const order = store.fieldOrder[selectedType.value]
  const sorted = !order?.length ? enabled : [...enabled].sort((a, b) => {
    const ai = order.indexOf(a.name)
    const bi = order.indexOf(b.name)
    if (ai === -1 && bi === -1) return 0
    if (ai === -1) return 1
    if (bi === -1) return -1
    return ai - bi
  })

  // The meta chips are the meta columns the export posts: an unresolved or
  // duplicate key is left out, so the chips and "Selected Fields (N)" match
  // the export. A chip is named by its column key, the name its saved
  // position and label use, so a prefixed column that shares a key with a
  // plain one keeps its own position and label.
  const meta = store.selectedCustomMetaColumns().map(({ metaKey, definition, columnKey }) => ({
    name: columnKey,
    label: formatMetaKeyLabel(metaKey),
    _isMeta: true,
    _metaKey: metaKey,
    _defName: definition
  }))

  if (!order?.length) return [...sorted, ...meta]

  return [...sorted, ...meta].sort((a, b) => {
    const ai = order.indexOf(a.name)
    const bi = order.indexOf(b.name)
    if (ai === -1 && bi === -1) return 0
    if (ai === -1) return 1
    if (bi === -1) return -1
    return ai - bi
  })
})

// ── Inline rename ────────────────────────────────────────────────────────────
const editingField = ref(null)
const editingDraft = ref('')

function startRename (fieldName, currentLabel) {
  editingField.value = fieldName
  editingDraft.value = currentLabel
}
function commitRename () {
  if (editingField.value && selectedType.value) {
    const label = editingDraft.value.trim()
    store.setFieldLabel(selectedType.value, editingField.value, label)
    if (label) {
      /* translators: %s: new field label */
      toasts.push(sprintf(__('Field renamed to "%s"', 'woocommerce-exporter'), label))
    }
  }
  editingField.value = null
  editingDraft.value = ''
}
function cancelRename () {
  editingField.value = null
  editingDraft.value = ''
}

function removeMetaKey (defName, key) {
  const type = selectedType.value
  if (!type || !customMetaValues.value[type]?.[defName]) return
  const remaining = String(customMetaValues.value[type][defName])
    .split(/[\n,]+/)
    .map(k => k.trim())
    .filter(k => k && k !== key)
  customMetaValues.value[type][defName] = remaining.join('\n')
}
function selectedFieldsLabel () {
  /* translators: %s: number of selected fields */
  return sprintf(__('Selected Fields (%s)', 'woocommerce-exporter'), fieldsChips.value.length)
}
function renameAriaLabel (field) {
  /* translators: %s: field label */
  return sprintf(__('Rename %s', 'woocommerce-exporter'), field.label)
}
function removeAriaLabel (field) {
  /* translators: %s: name of the item to remove */
  return sprintf(__('Remove %s', 'woocommerce-exporter'), field.label)
}
function displayLabel (field) {
  return store.getFieldLabel(selectedType.value, field.name, field.label)
}

// ── Inline drag-to-reorder ───────────────────────────────────────────────────
const inlineDragIndex = ref(null)
const inlineHoverIndex = ref(null)

function onInlineDragStart (idx, e) {
  inlineDragIndex.value = idx
  if (e?.dataTransfer) {
    e.dataTransfer.effectAllowed = 'move'
    try { e.dataTransfer.setData('text/plain', String(idx)) } catch (_) { /* IE */ }
  }
}
function computeInlineDropIndex (container, clientX, clientY) {
  if (!container) return null
  const chips = [...container.querySelectorAll('[data-row-index]')]
  if (!chips.length) return 0

  const rows = new Map()
  for (const el of chips) {
    const r = el.getBoundingClientRect()
    const key = Math.round((r.top + r.height / 2) / 4) * 4
    if (!rows.has(key)) rows.set(key, [])
    rows.get(key).push({ el, idx: parseInt(el.dataset.rowIndex, 10), rect: r })
  }
  let bestKey = null
  let bestDist = Infinity
  for (const key of rows.keys()) {
    const d = Math.abs(clientY - key)
    if (d < bestDist) { bestDist = d; bestKey = key }
  }
  const row = rows.get(bestKey).sort((a, b) => a.rect.left - b.rect.left)
  for (const c of row) {
    if (clientX < c.rect.left + c.rect.width / 2) return c.idx
  }
  return row[row.length - 1].idx + 1
}
function onInlineListDragOver (e) {
  if (inlineDragIndex.value === null) return
  e.preventDefault()
  if (e.dataTransfer) e.dataTransfer.dropEffect = 'move'
  inlineHoverIndex.value = computeInlineDropIndex(e.currentTarget, e.clientX, e.clientY)
}
function onInlineListDrop (e, list) {
  if (e) e.preventDefault()
  const from = inlineDragIndex.value
  let to = computeInlineDropIndex(e.currentTarget, e.clientX, e.clientY)
  if (to === null) to = inlineHoverIndex.value
  inlineDragIndex.value = null
  inlineHoverIndex.value = null
  if (from === null || to === null) return
  if (to > from) to -= 1
  if (from === to) return
  if (!selectedType.value) return
  const ordered = list.map(f => f.name)
  const [moved] = ordered.splice(from, 1)
  ordered.splice(to, 0, moved)
  store.fieldOrder[selectedType.value] = ordered
}
function onInlineDragEnd () {
  inlineDragIndex.value = null
  inlineHoverIndex.value = null
}
function onInlineListDragLeave (e) {
  const to = e.relatedTarget
  if (!to || (e.currentTarget && !e.currentTarget.contains(to))) {
    inlineHoverIndex.value = null
  }
}
</script>

<template>
  <div class="pt-1 px-[18px] pb-4 border-t border-gray-50">
    <div v-if="currentFields && currentFields.loading" class="flex items-center gap-2 text-[12.5px] text-gray-500 py-3">
      <span class="wse-spinner" /> {{ __('Loading fields…', 'woocommerce-exporter') }}
    </div>
    <template v-else-if="currentFields && !currentFields.error">
      <div class="flex items-center justify-between my-1.5 mb-2.5">
        <span class="text-[13.5px] font-semibold text-gray-900">{{ selectedFieldsLabel() }}</span>
        <span v-if="fieldsChips.length" class="inline-flex items-center gap-[5px] text-[11.5px] font-medium text-gray-500 bg-gray-100 rounded-[6px] py-1 px-2.5">
          <Move :size="12" :stroke-width="2" />
          {{ __('Drag to reorder', 'woocommerce-exporter') }}
        </span>
      </div>
      <div class="bg-white border-[1.5px] border-gray-200 rounded-[10px] p-3.5 min-h-[60px]">
        <div v-if="!fieldsChips.length" class="text-[12.5px] text-gray-400">{{ __('No fields selected.', 'woocommerce-exporter') }}</div>
        <ul
          v-else
          class="list-none m-0 p-0 flex flex-wrap items-center gap-2"
          @dragover="onInlineListDragOver"
          @drop="onInlineListDrop($event, fieldsChips)"
          @dragleave="onInlineListDragLeave"
        >
          <template v-for="(field, idx) in fieldsChips" :key="field.name">
            <li
              v-if="inlineDragIndex !== null && inlineHoverIndex === idx && inlineDragIndex !== idx && inlineDragIndex !== idx - 1"
              class="self-stretch w-[3px] bg-indigo-600 rounded-[2px] shadow-[0_0_0_3px_rgba(108,123,255,0.18)] pointer-events-none list-none"
              aria-hidden="true"
            />
            <li
              class="inline-flex items-center gap-2 py-1.5 pl-2 pr-2.5 bg-gray-100 rounded-[7px] text-xs font-medium text-gray-700 cursor-grab transition-colors hover:bg-gray-200 list-none"
              :class="inlineDragIndex === idx && 'opacity-40'"
              :data-row-index="idx"
              draggable="true"
              @dragstart="onInlineDragStart(idx, $event)"
              @dragend="onInlineDragEnd"
              @dblclick="!field._isMeta && startRename(field.name, displayLabel(field))"
            >
              <span class="flex items-center justify-center cursor-grab flex-shrink-0" aria-hidden="true">
                <svg width="8" height="13" viewBox="0 0 10 14" fill="none">
                  <circle cx="2.5" cy="2.5"  r="1.3" fill="#9CA3AF"/>
                  <circle cx="7.5" cy="2.5"  r="1.3" fill="#9CA3AF"/>
                  <circle cx="2.5" cy="7"    r="1.3" fill="#9CA3AF"/>
                  <circle cx="7.5" cy="7"    r="1.3" fill="#9CA3AF"/>
                  <circle cx="2.5" cy="11.5" r="1.3" fill="#9CA3AF"/>
                  <circle cx="7.5" cy="11.5" r="1.3" fill="#9CA3AF"/>
                </svg>
              </span>
              <template v-if="!field._isMeta && editingField === field.name">
                <input
                  v-model="editingDraft"
                  class="border border-indigo-600 rounded py-[2px] px-1.5 text-xs font-medium text-gray-900 outline-none bg-white w-[130px]"
                  autofocus
                  @keydown.enter.prevent="commitRename()"
                  @keydown.esc.prevent="cancelRename()"
                  @click.stop
                />
                <button class="bg-transparent border-none cursor-pointer p-0 flex items-center text-emerald-500 flex-shrink-0 hover:text-emerald-600" :title="__('Save', 'woocommerce-exporter')" @mousedown.prevent="commitRename()">
                  <Check :size="11" :stroke-width="2.5" />
                </button>
                <button class="bg-transparent border-none cursor-pointer p-0 flex items-center text-gray-400 flex-shrink-0 hover:text-red-600" :title="__('Cancel', 'woocommerce-exporter')" @mousedown.prevent="cancelRename()">
                  <XIcon :size="11" :stroke-width="2.5" />
                </button>
              </template>
              <template v-else>
                <span class="inline-block">{{ displayLabel(field) }}</span>
                <button
                  v-if="!field._isMeta"
                  class="bg-transparent border-none cursor-pointer p-0 flex items-center text-gray-400 flex-shrink-0 hover:text-indigo-600"
                  :aria-label="renameAriaLabel(field)"
                  @click.stop="startRename(field.name, displayLabel(field))"
                >
                  <Pencil :size="10" :stroke-width="2" />
                </button>
                <button
                  class="bg-transparent border-none cursor-pointer p-0 flex items-center text-gray-400 flex-shrink-0 hover:text-red-600"
                  :aria-label="removeAriaLabel(field)"
                  @click.stop="field._isMeta ? removeMetaKey(field._defName, field._metaKey) : store.removeField(selectedType, field.name)"
                >
                  <XIcon :size="10" :stroke-width="2.5" />
                </button>
              </template>
            </li>
          </template>
          <li
            v-if="inlineDragIndex !== null && inlineHoverIndex === fieldsChips.length && inlineDragIndex !== fieldsChips.length - 1"
            class="self-stretch w-[3px] bg-indigo-600 rounded-[2px] shadow-[0_0_0_3px_rgba(108,123,255,0.18)] pointer-events-none list-none"
            aria-hidden="true"
          />
        </ul>
      </div>

      <button
        type="button"
        class="wse-qe-add-fields w-full flex flex-col items-center gap-1 mt-3 py-[22px] px-4 bg-gray-50 border-[1.5px] border-dashed border-gray-300 rounded-[10px] cursor-pointer transition-[border-color,background] hover:border-indigo-400 hover:bg-[#F5F3FF]"
        @click="store.showManageFields = true"
      >
        <span class="inline-flex items-center justify-center text-gray-500">
          <Plus :size="18" :stroke-width="2" />
        </span>
        <span class="text-[13.5px] font-semibold text-gray-900">{{ __('Add more fields', 'woocommerce-exporter') }}</span>
        <span class="text-xs text-gray-500">{{ __('Click to add more fields from available fields', 'woocommerce-exporter') }}</span>
      </button>

      <div class="flex items-center justify-between gap-3 mt-[18px] pt-4 border-t border-gray-100">
        <div class="flex flex-col gap-[2px] min-w-0">
          <p class="text-[13px] font-semibold text-gray-900 m-0">{{ __('Quick Actions', 'woocommerce-exporter') }}</p>
          <p class="text-xs text-gray-500 m-0">{{ __('Save time by using presets or managing your field selections.', 'woocommerce-exporter') }}</p>
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
          <button
            class="wse-qe-reset-fields inline-flex items-center gap-[5px] rounded-lg text-[12.5px] font-semibold py-[7px] px-3.5 cursor-pointer bg-transparent border-[1.5px] border-gray-200 text-gray-700 hover:bg-gray-100"
            @click="store.resetFieldsToDefault(selectedType); toasts.push(__('Reset to default fields', 'woocommerce-exporter'))"
          >
            <RotateCcw :size="13" :stroke-width="1.75" />
            {{ __('Reset to Default', 'woocommerce-exporter') }}
          </button>
          <button
            class="wse-qe-manage-fields inline-flex items-center gap-[5px] rounded-lg text-[12.5px] font-semibold py-[7px] px-3.5 cursor-pointer bg-transparent border-[1.5px] border-gray-200 text-gray-700 hover:bg-gray-100"
            @click="store.showManageFields = true"
          >
            <Settings2 :size="13" :stroke-width="1.75" />
            {{ __('Manage Fields', 'woocommerce-exporter') }}
          </button>
          <button
            class="wse-qe-fields-continue inline-flex items-center gap-[5px] rounded-lg text-[12.5px] font-semibold py-[7px] px-3.5 cursor-pointer bg-indigo-600 text-white border-none hover:bg-indigo-700"
            @click="store.saveFields(); toasts.push(__('Fields saved', 'woocommerce-exporter'))"
          >
            {{ __('Continue', 'woocommerce-exporter') }} <ChevronRight :size="13" :stroke-width="2" />
          </button>
        </div>
      </div>

      <BaseInfoNotice class="mt-3.5">
        <strong>{{ __('Tip:', 'woocommerce-exporter') }}</strong> {{ __('You can reorder fields by dragging them. The order will be reflected in your exported file.', 'woocommerce-exporter') }}
      </BaseInfoNotice>
    </template>
    <div v-else-if="currentFields?.error" class="flex items-center gap-2 text-[12.5px] text-red-600 py-3">{{ currentFields.error }}</div>
    <div v-else class="flex items-center justify-end gap-2 pt-3.5">
      <button
        class="wse-qe-fields-continue inline-flex items-center gap-[5px] rounded-lg text-[12.5px] font-semibold py-[7px] px-3.5 cursor-pointer bg-indigo-600 text-white border-none hover:bg-indigo-700"
        @click="store.saveFields()"
      >
        {{ __('Continue', 'woocommerce-exporter') }} <ChevronRight :size="13" :stroke-width="2" />
      </button>
    </div>
  </div>
</template>
