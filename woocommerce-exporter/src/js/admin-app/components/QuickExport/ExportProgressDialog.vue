<script setup>
import { computed } from 'vue'
import { X, Download } from 'lucide-vue-next'
import { BaseButton } from '../ui/index'
import { storeToRefs } from 'pinia'
import { useQuickExportStore } from '../../stores/quickExport'

const store = useQuickExportStore()
const {
  exportState, exportProgress, exportTotal, exportProcessed,
  exportError, exportDownloadUrl, currentTypeConfig
} = storeToRefs(store)

const RADIUS = 40
const CIRCUMFERENCE = 2 * Math.PI * RADIUS

const strokeDashoffset = computed(() => {
  const pct = Math.min(Math.max(exportProgress.value, 0), 100)
  return CIRCUMFERENCE - (pct / 100) * CIRCUMFERENCE
})

const progressColor = computed(() => {
  if (exportState.value === 'done') return '#22C55E'
  if (exportState.value === 'error') return '#EF4444'
  return '#6C7BFF'
})

// Steps mirroring the Figma design
const steps = computed(() => {
  const state = exportState.value
  const processed = exportProcessed.value
  const total = exportTotal.value

  return [
    {
      label: 'Initializing Export',
      status: 'idle' === state ? 'pending' : 'done'
    },
    {
      label: processed > 0 && total > 0
        ? `Fetching Data (${processed.toLocaleString()} / ${total.toLocaleString()} rows)`
        : 'Fetching Data',
      status: 'preparing' === state
        ? 'active'
        : ('idle' === state ? 'pending' : 'done')
    },
    {
      label: 'Preparing File',
      status: ['idle', 'preparing'].includes(state)
        ? 'pending'
        : ('exporting' === state ? 'active' : 'done')
    },
    {
      label: 'Generating File',
      status: ['idle', 'preparing', 'exporting'].includes(state)
        ? 'pending'
        : ('done' === state || 'error' === state ? 'done' : 'pending')
    },
    {
      label: 'Finalizing Export',
      status: 'done' === state ? 'done' : 'pending'
    }
  ]
})
</script>

<template>
  <div
    class="fixed inset-0 bg-gray-900/50 z-[99999] flex items-center justify-center p-6"
    @click.self="exportState === 'done' || exportState === 'error' ? store.closeExportProgress() : null"
  >
    <div
      class="wse-qe-progress bg-white rounded-2xl w-full max-w-[420px] px-7 pt-7 pb-6 shadow-[0_24px_60px_rgba(0,0,0,0.2)] flex flex-col items-center gap-[14px]"
      :class="`wse-qe-progress--${exportState}`"
      role="dialog"
      aria-modal="true"
      aria-label="Export in Progress"
    >
      <!-- Header -->
      <div class="w-full flex items-center justify-between">
        <h2 class="text-base font-bold text-gray-900 m-0">Export in Progress</h2>
        <button
          v-if="exportState === 'done' || exportState === 'error'"
          class="bg-none border-none cursor-pointer p-1 text-gray-400 rounded-md flex items-center hover:bg-gray-100 hover:text-gray-700"
          aria-label="Close"
          @click="store.closeExportProgress()"
        >
          <X :size="18" :stroke-width="1.75" />
        </button>
      </div>

      <!-- Circular progress -->
      <div class="relative w-24 h-24 flex-shrink-0">
        <svg width="96" height="96" viewBox="0 0 96 96" aria-hidden="true">
          <circle cx="48" cy="48" :r="RADIUS" fill="none" stroke="#E5E7EB" stroke-width="6" />
          <circle
            cx="48" cy="48" :r="RADIUS" fill="none"
            :stroke="progressColor"
            stroke-width="6"
            stroke-linecap="round"
            :stroke-dasharray="CIRCUMFERENCE"
            :stroke-dashoffset="strokeDashoffset"
            transform="rotate(-90 48 48)"
            style="transition: stroke-dashoffset 0.35s ease, stroke 0.2s;"
          />
        </svg>
        <div class="absolute inset-0 flex items-center justify-center text-xl font-bold text-gray-900">
          {{ exportProgress }}%
        </div>
      </div>

      <!-- Status text -->
      <p class="text-[13px] text-gray-500 text-center m-0 leading-[1.5] max-w-[280px]">
        <template v-if="exportState === 'done'">
          Your export is complete and ready to download.
        </template>
        <template v-else-if="exportState === 'error'">
          An error occurred during the export.
        </template>
        <template v-else>
          Please don't close this window, your export is being prepared.
        </template>
      </p>

      <!-- Type label -->
      <p v-if="currentTypeConfig" class="text-xs text-gray-400 -mt-2 m-0">
        {{ currentTypeConfig.label }}
      </p>

      <!-- Step list -->
      <ul class="list-none m-0 p-0 w-full flex flex-col gap-2">
        <li
          v-for="(step, i) in steps"
          :key="i"
          class="flex items-center gap-[10px] text-[13px]"
          :class="{
            'text-gray-400': step.status === 'pending',
            'text-gray-700': step.status === 'done',
            'text-gray-900 font-medium': step.status === 'active'
          }"
        >
          <span class="w-4 h-4 flex-shrink-0 flex items-center justify-center">
            <svg v-if="step.status === 'done'" width="16" height="16" viewBox="0 0 16 16" fill="none">
              <circle cx="8" cy="8" r="8" fill="#22C55E"/>
              <path d="M4.5 8L7 10.5L11.5 5.5" stroke="white" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span v-else-if="step.status === 'active'" class="flex items-center">
              <svg width="16" height="16" viewBox="0 0 16 16" fill="none" class="animate-spin">
                <circle cx="8" cy="8" r="6.5" stroke="#6C7BFF" stroke-width="2" stroke-dasharray="20 20" stroke-linecap="round"/>
              </svg>
            </span>
            <span v-else class="w-2 h-2 rounded-full bg-gray-300 block" />
          </span>
          <span class="flex-1">{{ step.label }}</span>
          <span
            v-if="step.status === 'active'"
            class="text-[10px] font-bold tracking-[0.04em] text-indigo-600 bg-indigo-50 rounded px-[6px] py-[2px] flex-shrink-0"
          >IN PROGRESS</span>
          <span v-else-if="step.status === 'done'" class="text-[11px] text-green-500 flex-shrink-0">Completed</span>
          <span v-else class="text-[11px] text-gray-400 flex-shrink-0">Pending</span>
        </li>
      </ul>

      <!-- Error message -->
      <div v-if="exportError" class="text-[12.5px] text-red-500 text-center max-w-[300px] leading-[1.5]">{{ exportError }}</div>

      <!-- Actions -->
      <div class="flex gap-2 flex-wrap justify-center w-full">
        <template v-if="exportState === 'done'">
          <BaseButton v-if="exportDownloadUrl" class="wse-qe-download" tag="a" :href="exportDownloadUrl" download>
            <template #icon><Download :size="14" :stroke-width="2" /></template>
            Download File
          </BaseButton>
          <button
            class="wse-qe-progress-done inline-flex items-center gap-[6px] rounded-lg text-[13px] font-semibold py-2 px-[18px] cursor-pointer bg-transparent border-[1.5px] border-gray-200 text-gray-700 hover:bg-gray-100"
            @click="store.closeExportProgress()"
          >Done</button>
        </template>
        <template v-else-if="exportState !== 'error'">
          <button
            class="inline-flex items-center gap-[6px] rounded-lg text-[13px] font-semibold py-2 px-[18px] cursor-pointer bg-transparent border-[1.5px] border-gray-200 text-gray-700 hover:bg-gray-100"
            @click="store.cancelExport()"
          >Cancel Export</button>
        </template>
        <template v-else>
          <BaseButton @click="store.startExport()">Retry</BaseButton>
          <button
            class="inline-flex items-center gap-[6px] rounded-lg text-[13px] font-semibold py-2 px-[18px] cursor-pointer bg-transparent border-[1.5px] border-gray-200 text-gray-700 hover:bg-gray-100"
            @click="store.cancelExport()"
          >Cancel</button>
        </template>
      </div>
    </div>
  </div>
</template>
