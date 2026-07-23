<script setup>
import { computed, ref, watch } from 'vue'
import { X, Pencil, Check, ChevronDown, ChevronRight, RotateCcw, ArrowRight, ArrowLeft, ArrowUpDown, Info } from 'lucide-vue-next'
import BaseCheckbox from './BaseCheckbox.vue'
import FilterSearchInput from './FilterSearchInput.vue'
import BaseSelect from './BaseSelect.vue'

const props = defineProps({
  title:              { type: String,  required: true },
  subtitle:           { type: String,  default: '' },
  footerHint:         { type: String,  default: '' },
  confirmLabel:       { type: String,  default: 'Apply' },
  showResetDefault:   { type: Boolean, default: false },
  extraSectionLabel:  { type: String,  default: '' },
  groups:             { type: Array,   default: () => [] },
  selectedNames:      { type: Array,   default: () => [] },
  selectedFields:     { type: Array,   default: () => [] },
  loading:            { type: Boolean, default: false },
  error:              { type: String,  default: '' },
})

const emit = defineEmits([
  'toggle', 'remove', 'reorder', 'confirm', 'close',
  'reset', 'clearAll', 'selectAllVisible', 'setLabel',
])

// ── Search + filter ───────────────────────────────────────────────────────────
const search      = ref('')
const activeGroup = ref('all')

// ── Accordion ─────────────────────────────────────────────────────────────────
// Reset accordion and filters when the groups dataset changes (e.g. export type switch).
const expandedGroup = ref(null)

watch(
  () => props.groups.map(g => g.label).join('\x00'),
  () => {
    expandedGroup.value = props.groups.length ? props.groups[0].label : null
    search.value      = ''
    activeGroup.value = 'all'
  },
  { immediate: true },
)

function toggleGroup (label) {
  expandedGroup.value = expandedGroup.value === label ? null : label
}
function isGroupCollapsed (label) {
  if (search.value.trim()) return false
  return expandedGroup.value !== label
}

// ── Derived lists ─────────────────────────────────────────────────────────────
const groupOptions = computed(() => [
  { value: 'all', label: 'All Groups' },
  ...props.groups.map(g => ({ value: g.label, label: g.label })),
  ...(props.extraSectionLabel ? [{ value: '__extra__', label: props.extraSectionLabel }] : []),
])

// True when the "Custom Meta Fields" (or custom extra-section) group is active.
const isExtraGroupActive = computed(() => !!props.extraSectionLabel && activeGroup.value === '__extra__')

// Show the extra-sections slot when "All Groups" or the extra group is active.
// When a specific normal group is selected, hide it to keep the list focused.
const showExtraSection = computed(() =>
  !props.extraSectionLabel ||
  activeGroup.value === 'all' ||
  activeGroup.value === '__extra__'
)

const filteredGroups = computed(() => {
  const q = search.value.trim().toLowerCase()
  return props.groups
    .filter(g => activeGroup.value === 'all' || g.label === activeGroup.value)
    .map(g => ({
      ...g,
      fields: q
        ? g.fields.filter(f => f.label.toLowerCase().includes(q) || f.name.toLowerCase().includes(q))
        : g.fields,
    }))
    .filter(g => g.fields.length > 0)
})

const allFieldsCount   = computed(() => props.groups.reduce((n, g) => n + g.fields.length, 0))
const allSelectedCount = computed(() => props.selectedNames.length)

function isSelected (name) {
  return props.selectedNames.includes(name)
}

// ── Bulk transfer ─────────────────────────────────────────────────────────────
function handleSelectAllVisible () {
  const toAdd = []
  for (const g of filteredGroups.value) {
    for (const f of g.fields) {
      if (!isSelected(f.name)) toAdd.push(f)
    }
  }
  emit('selectAllVisible', toAdd)
}

// ── Inline rename ─────────────────────────────────────────────────────────────
const editingName  = ref(null)
const editingDraft = ref('')

function startEdit (field) {
  editingName.value  = field.name
  editingDraft.value = field.label
}
function commitEdit () {
  if (!editingName.value) return
  emit('setLabel', editingName.value, editingDraft.value.trim() || editingName.value)
  editingName.value  = null
  editingDraft.value = ''
}
function cancelEdit () {
  editingName.value  = null
  editingDraft.value = ''
}

// ── Drag reorder ──────────────────────────────────────────────────────────────
const dragIndex  = ref(null)
const hoverIndex = ref(null)
const dragListEl = ref(null)

function onDragStart (idx, e) {
  dragIndex.value = idx
  if (e?.dataTransfer) {
    e.dataTransfer.effectAllowed = 'move'
    try { e.dataTransfer.setData('text/plain', String(idx)) } catch (_) {}
  }
}
function computeDropIndex (container, clientY) {
  if (!container) return null
  const rows = [...container.querySelectorAll('[data-row-index]')]
  for (let i = 0; i < rows.length; i++) {
    const r = rows[i].getBoundingClientRect()
    if (clientY < r.top + r.height / 2) return i
  }
  return rows.length
}
function onListDragOver (e) {
  if (dragIndex.value === null) return
  e.preventDefault()
  if (e.dataTransfer) e.dataTransfer.dropEffect = 'move'
  hoverIndex.value = computeDropIndex(e.currentTarget, e.clientY)
}
function onListDrop (e) {
  e?.preventDefault()
  const from = dragIndex.value
  let to = computeDropIndex(e.currentTarget, e.clientY)
  if (to === null) to = hoverIndex.value
  dragIndex.value  = null
  hoverIndex.value = null
  if (from === null || to === null) return
  if (to > from) to -= 1
  if (from === to) return
  const ordered = props.selectedFields.map(f => f.name)
  const [moved] = ordered.splice(from, 1)
  ordered.splice(to, 0, moved)
  emit('reorder', ordered)
}
function onDragEnd () { dragIndex.value = null; hoverIndex.value = null }
function onListDragLeave (e) {
  const to = e.relatedTarget
  if (!to || (e.currentTarget && !e.currentTarget.contains(to))) {
    hoverIndex.value = null
  }
}
</script>

<template>
  <div class="fixed inset-0 bg-gray-900/45 z-[99999] flex items-center justify-center p-6" @click.self="emit('close')">
    <div
      class="bg-white rounded-[14px] w-full max-w-[1180px] max-h-[88vh] flex flex-col shadow-[0_20px_60px_rgba(0,0,0,0.18)] overflow-hidden"
      role="dialog"
      aria-modal="true"
    >
      <!-- Header -->
      <div class="flex items-start justify-between gap-3 px-5 py-[18px] pb-[14px] border-b border-gray-100 flex-shrink-0">
        <div>
          <h2 class="text-[15px] font-semibold text-gray-900 m-0 mb-[3px]">{{ title }}</h2>
          <p v-if="subtitle" class="text-[12.5px] text-gray-500 m-0">{{ subtitle }}</p>
        </div>
        <button
          class="bg-none border-none cursor-pointer p-1 text-gray-400 rounded-md flex items-center hover:bg-gray-100 hover:text-gray-700"
          aria-label="Close"
          @click="emit('close')"
        >
          <X :size="18" :stroke-width="1.75" />
        </button>
      </div>

      <!-- Toolbar -->
      <div class="flex items-center gap-[10px] px-5 py-[14px] border-b border-gray-100 flex-shrink-0">
        <FilterSearchInput
          v-model="search"
          placeholder="Search fields..."
          class="flex-1"
        />
        <BaseSelect
          variant="filter"
          v-model="activeGroup"
          :options="groupOptions"
          class="flex-shrink-0 min-w-[170px]"
        />
        <button
          type="button"
          class="inline-flex items-center gap-[6px] h-[40px] px-4 bg-white border-[1.5px] border-gray-200 rounded-lg text-[12.5px] font-semibold text-gray-700 cursor-pointer whitespace-nowrap flex-shrink-0 hover:bg-gray-50 hover:border-gray-300"
          @click="emit('clearAll')"
        >
          Clear All
        </button>
        <button
          v-if="showResetDefault"
          type="button"
          class="inline-flex items-center gap-[6px] h-[40px] px-4 bg-white border-[1.5px] border-indigo-200 rounded-lg text-[12.5px] font-semibold text-indigo-600 cursor-pointer whitespace-nowrap flex-shrink-0 hover:bg-indigo-50 hover:border-indigo-400 hover:text-indigo-700"
          @click="emit('reset')"
        >
          <RotateCcw :size="13" :stroke-width="2" />
          Reset to Default
        </button>
      </div>

      <!-- Body: dual panel + transfer column -->
      <div
        class="grid gap-4 flex-1 overflow-hidden px-5 py-[18px] bg-[#FAFAFA]"
        style="grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr)"
      >

        <!-- Left: All Fields -->
        <div class="flex flex-col bg-white border-[1.5px] border-gray-200 rounded-[10px] min-w-0 overflow-hidden">
          <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 flex-shrink-0">
            <span class="text-[13.5px] font-semibold text-gray-900 inline-flex items-center gap-2">
              All Fields
              <span class="inline-flex items-center justify-center text-[11px] font-bold text-gray-500 bg-gray-100 rounded-md py-[2px] px-2">{{ allFieldsCount }}</span>
            </span>
            <span class="text-[11.5px] font-medium text-indigo-600 bg-indigo-50 rounded-[5px] py-1 px-[9px]">{{ allSelectedCount }} selected</span>
          </div>

          <div class="flex-1 overflow-y-auto py-[6px] min-h-[380px]">
            <div v-if="loading" class="flex items-center gap-2 px-4 py-5 text-[12.5px] text-gray-500">
              <span class="wse-spinner" /> Loading fields…
            </div>
            <div v-else-if="error" class="flex items-center gap-2 px-4 py-5 text-[12.5px] text-red-600">
              {{ error }}
            </div>
            <template v-else>
              <template v-if="!isExtraGroupActive">
                <div v-for="group in filteredGroups" :key="group.label">
                  <button
                    type="button"
                    class="flex items-center gap-2 w-full px-4 py-[10px] bg-white border-none border-b border-gray-100 cursor-pointer text-left transition-colors hover:bg-gray-50"
                    :aria-expanded="String(!isGroupCollapsed(group.label))"
                    @click="toggleGroup(group.label)"
                  >
                    <component
                      :is="isGroupCollapsed(group.label) ? ChevronRight : ChevronDown"
                      :size="14"
                      color="#9CA3AF"
                      :stroke-width="2"
                      class="flex-shrink-0"
                    />
                    <span class="flex-1 text-[13px] font-semibold text-gray-900">{{ group.label }}</span>
                    <span class="text-[11px] font-medium text-indigo-600 bg-indigo-50 rounded-[5px] py-[3px] px-2 flex-shrink-0">
                      {{ group.fields.filter(f => isSelected(f.name)).length }}/{{ group.fields.length }}
                    </span>
                  </button>
                  <ul v-show="!isGroupCollapsed(group.label)" class="list-none m-0 py-1 p-0">
                    <li
                      v-for="field in group.fields"
                      :key="field.name"
                      class="flex items-center gap-[10px] py-[7px] pr-4 pl-[38px] cursor-pointer transition-colors hover:bg-gray-50"
                      @click="emit('toggle', field.name)"
                    >
                      <BaseCheckbox
                        :model-value="isSelected(field.name)"
                        size="md"
                        @click.stop
                        @update:model-value="emit('toggle', field.name)"
                      />
                      <span
                        class="text-[12.5px]"
                        :class="isSelected(field.name) ? 'text-gray-900 font-medium' : 'text-gray-500'"
                      >
                        {{ field.label }}
                      </span>
                    </li>
                  </ul>
                </div>
                <div v-if="filteredGroups.length === 0" class="flex items-center gap-2 px-4 py-5 text-[12.5px] text-gray-500">
                  No fields match your search.
                </div>
              </template>
              <slot v-if="showExtraSection" name="extra-sections" />
            </template>
          </div>
        </div>

        <!-- Middle: transfer arrows -->
        <div class="flex flex-col gap-[10px] self-center flex-shrink-0">
          <button
            type="button"
            class="w-[38px] h-[38px] flex items-center justify-center bg-white border-[1.5px] border-gray-200 rounded-lg cursor-pointer text-gray-700 transition-[background,border-color,color] duration-[120ms] hover:bg-indigo-50 hover:border-indigo-400 hover:text-indigo-600"
            title="Add visible fields to selection"
            @click="handleSelectAllVisible"
          >
            <ArrowRight :size="14" :stroke-width="2" />
          </button>
          <button
            type="button"
            class="w-[38px] h-[38px] flex items-center justify-center bg-white border-[1.5px] border-gray-200 rounded-lg cursor-pointer text-gray-700 transition-[background,border-color,color] duration-[120ms] hover:bg-indigo-50 hover:border-indigo-400 hover:text-indigo-600"
            title="Remove all from selection"
            @click="emit('clearAll')"
          >
            <ArrowLeft :size="14" :stroke-width="2" />
          </button>
        </div>

        <!-- Right: Selected Fields -->
        <div class="flex flex-col bg-white border-[1.5px] border-gray-200 rounded-[10px] min-w-0 overflow-hidden">
          <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 flex-shrink-0">
            <span class="text-[13.5px] font-semibold text-gray-900 inline-flex items-center gap-2">
              Selected Fields
              <span class="inline-flex items-center justify-center text-[11px] font-bold text-gray-500 bg-gray-100 rounded-md py-[2px] px-2">{{ selectedFields.length }}</span>
            </span>
            <span class="inline-flex items-center gap-[5px] text-[11.5px] font-medium text-indigo-600 bg-indigo-50 rounded-md py-1 px-[9px]">
              <ArrowUpDown :size="11" :stroke-width="2" />
              Reorder
            </span>
          </div>

          <div class="flex-1 overflow-y-auto p-2 min-h-[380px]">
            <p v-if="selectedFields.length === 0" class="text-xs text-gray-400 text-center py-5 px-3 m-0">
              Enable fields on the left to add them here.
            </p>
            <ul
              v-else
              ref="dragListEl"
              class="list-none m-0 p-0 flex flex-col gap-[6px]"
              @dragover="onListDragOver"
              @drop="onListDrop"
              @dragleave="onListDragLeave"
            >
              <template v-for="(field, idx) in selectedFields" :key="field.name">
                <li
                  v-if="dragIndex !== null && hoverIndex === idx && dragIndex !== idx && dragIndex !== idx - 1"
                  class="h-[3px] bg-indigo-600 rounded-[2px] m-0 shadow-[0_0_0_3px_rgba(108,123,255,0.18)] pointer-events-none list-none"
                  aria-hidden="true"
                />
                <li
                  class="flex items-center gap-[10px] py-[9px] px-3 bg-white border-[1.5px] border-gray-200 rounded-lg cursor-grab transition-[border-color,background] duration-[120ms] hover:border-indigo-200 hover:bg-[#FAFAFE] list-none"
                  :class="dragIndex === idx && 'opacity-40'"
                  :data-row-index="idx"
                  draggable="true"
                  @dragstart="onDragStart(idx, $event)"
                  @dragend="onDragEnd"
                >
                  <svg class="cursor-grab flex-shrink-0" width="10" height="14" viewBox="0 0 10 14" fill="none">
                    <circle cx="2.5" cy="2.5"  r="1.5" fill="#9CA3AF"/>
                    <circle cx="7.5" cy="2.5"  r="1.5" fill="#9CA3AF"/>
                    <circle cx="2.5" cy="7"    r="1.5" fill="#9CA3AF"/>
                    <circle cx="7.5" cy="7"    r="1.5" fill="#9CA3AF"/>
                    <circle cx="2.5" cy="11.5" r="1.5" fill="#9CA3AF"/>
                    <circle cx="7.5" cy="11.5" r="1.5" fill="#9CA3AF"/>
                  </svg>

                  <template v-if="editingName === field.name">
                    <input
                      v-model="editingDraft"
                      class="flex-1 min-w-0 border border-indigo-600 rounded-[4px] py-[2px] px-[6px] text-[12.5px] text-gray-900 outline-none bg-white"
                      autofocus
                      @keydown.enter.prevent="commitEdit"
                      @keydown.esc.prevent="cancelEdit"
                      @click.stop
                    />
                    <button
                      class="bg-none border-none cursor-pointer p-[2px] rounded-[3px] flex items-center flex-shrink-0 text-emerald-500 hover:text-emerald-600 hover:bg-emerald-50"
                      title="Save"
                      @mousedown.prevent="commitEdit"
                    ><Check :size="13" :stroke-width="2.5" /></button>
                    <button
                      class="bg-none border-none cursor-pointer p-[2px] rounded-[3px] flex items-center flex-shrink-0 text-gray-400 hover:text-red-600 hover:bg-red-50"
                      title="Cancel"
                      @mousedown.prevent="cancelEdit"
                    ><X :size="13" :stroke-width="2.5" /></button>
                  </template>
                  <template v-else>
                    <span
                      class="text-[12.5px] font-medium text-gray-900 flex-1 min-w-0 overflow-hidden text-ellipsis whitespace-nowrap"
                      @dblclick.stop="startEdit(field)"
                    >{{ field.label }}</span>
                    <button
                      class="bg-none border-none cursor-pointer p-[2px] rounded-[3px] flex items-center flex-shrink-0 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50"
                      aria-label="Rename field"
                      @click.stop="startEdit(field)"
                    ><Pencil :size="12" :stroke-width="2" /></button>
                    <button
                      class="bg-none border-none cursor-pointer p-[2px] rounded-[3px] flex items-center flex-shrink-0 text-gray-400 hover:text-red-600 hover:bg-red-50"
                      aria-label="Remove field"
                      @click.stop="emit('remove', field.name)"
                    ><X :size="12" :stroke-width="2" /></button>
                  </template>
                </li>
              </template>
              <li
                v-if="dragIndex !== null && hoverIndex === selectedFields.length && dragIndex !== selectedFields.length - 1"
                class="h-[3px] bg-indigo-600 rounded-[2px] m-0 shadow-[0_0_0_3px_rgba(108,123,255,0.18)] pointer-events-none list-none"
                aria-hidden="true"
              />
            </ul>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="flex items-center justify-between gap-3 px-5 py-3 border-t border-gray-100 flex-shrink-0">
        <p v-if="footerHint" class="inline-flex items-center gap-2 text-[11.5px] text-gray-400 m-0">
          <Info :size="14" color="#6C7BFF" :stroke-width="1.75" />
          {{ footerHint }}
        </p>
        <div class="flex gap-2 flex-shrink-0 ml-auto">
          <button
            class="bg-transparent border-[1.5px] border-gray-200 rounded-lg text-[13px] font-medium text-gray-700 py-[7px] px-4 cursor-pointer hover:bg-gray-100"
            @click="emit('close')"
          >Cancel</button>
          <button
            class="bg-indigo-600 border-none rounded-lg text-[13px] font-semibold text-white py-[7px] px-[18px] cursor-pointer hover:bg-indigo-700"
            @click="emit('confirm')"
          >{{ confirmLabel }}</button>
        </div>
      </div>
    </div>
  </div>
</template>
