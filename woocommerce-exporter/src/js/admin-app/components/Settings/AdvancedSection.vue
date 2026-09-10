<script setup>
import { ref } from 'vue'
import { storeToRefs } from 'pinia'
import { Bug, FileSearch, RotateCcw, BellOff, Trash2 } from 'lucide-vue-next'
import { useSettingsStore } from '../../stores/settings'
import { useToastsStore } from '../../stores/toasts'

const settingsStore = useSettingsStore()
const toastsStore = useToastsStore()
const { settings } = storeToRefs(settingsStore)

const actionLoading = ref(null)

async function runAction (action) {
  actionLoading.value = action
  try {
    const result = await settingsStore.runAction(action)
    toastsStore.push(result.message ?? 'Done.', 'success')
  } catch (err) {
    toastsStore.push(err.message ?? 'Action failed.', 'error')
  } finally {
    actionLoading.value = null
  }
}
</script>

<template>
  <div class="bg-white border border-gray-200 rounded-[10px] overflow-hidden flex-shrink-0">
    <div class="py-5 px-6 border-b border-gray-100">
      <h2 class="text-[20px] font-bold text-gray-900 m-0 leading-tight">Advanced</h2>
      <p class="text-[13px] text-gray-500 m-0 mt-1">System and interface options for advanced configurations.</p>
    </div>

    <div class="divide-y divide-gray-100">

      <!-- Enable Debugging Mode -->
      <div class="py-5 px-6">
        <div class="flex items-center gap-4">
          <div class="flex items-start gap-3 flex-1 min-w-0">
            <span class="flex-shrink-0 w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center">
              <Bug :size="15" :stroke-width="1.75" color="#6C7BFF" />
            </span>
            <div class="min-w-0">
              <p class="text-[13px] font-semibold text-gray-900 m-0 mb-0.5">Enable Debugging Mode</p>
              <p class="text-[12px] text-gray-400 m-0">Activate verbose debug output. Visible in WooCommerce &rsaquo; Status.</p>
            </div>
          </div>
          <div class="flex items-center gap-2.5 flex-shrink-0">
            <button
              type="button"
              class="relative w-11 h-6 rounded-full border-none p-0 cursor-pointer flex-shrink-0 transition-colors"
              :class="settings.debug_mode ? 'bg-indigo-600' : 'bg-gray-300'"
              :aria-pressed="settings.debug_mode"
              @click="settings.debug_mode = !settings.debug_mode"
            >
              <span
                class="absolute top-[3px] left-[3px] w-[18px] h-[18px] rounded-full bg-white shadow-sm transition-transform"
                :class="settings.debug_mode && 'translate-x-[20px]'"
              />
            </button>
            <span class="text-[13px] font-semibold" :class="settings.debug_mode ? 'text-indigo-600' : 'text-gray-400'">
              {{ settings.debug_mode ? 'On' : 'Off' }}
            </span>
          </div>
        </div>
      </div>

      <!-- Enable Logging Mode -->
      <div class="py-5 px-6">
        <div class="flex items-center gap-4">
          <div class="flex items-start gap-3 flex-1 min-w-0">
            <span class="flex-shrink-0 w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center">
              <FileSearch :size="15" :stroke-width="1.75" color="#6C7BFF" />
            </span>
            <div class="min-w-0">
              <p class="text-[13px] font-semibold text-gray-900 m-0 mb-0.5">Enable Logging Mode</p>
              <p class="text-[12px] text-gray-400 m-0">Write export activity to the WooCommerce log. Visible in WooCommerce &rsaquo; Status.</p>
            </div>
          </div>
          <div class="flex items-center gap-2.5 flex-shrink-0">
            <button
              type="button"
              class="relative w-11 h-6 rounded-full border-none p-0 cursor-pointer flex-shrink-0 transition-colors"
              :class="settings.logging_mode ? 'bg-indigo-600' : 'bg-gray-300'"
              :aria-pressed="settings.logging_mode"
              @click="settings.logging_mode = !settings.logging_mode"
            >
              <span
                class="absolute top-[3px] left-[3px] w-[18px] h-[18px] rounded-full bg-white shadow-sm transition-transform"
                :class="settings.logging_mode && 'translate-x-[20px]'"
              />
            </button>
            <span class="text-[13px] font-semibold" :class="settings.logging_mode ? 'text-indigo-600' : 'text-gray-400'">
              {{ settings.logging_mode ? 'On' : 'Off' }}
            </span>
          </div>
        </div>
      </div>

      <!-- Maintenance Actions -->
      <div class="py-5 px-6">
        <div class="flex items-center gap-3 mb-3">
          <span class="flex-shrink-0 w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center">
            <RotateCcw :size="15" :stroke-width="1.75" color="#6C7BFF" />
          </span>
          <div>
            <p class="text-[13px] font-semibold text-gray-900 m-0">Maintenance</p>
            <p class="text-[12px] text-gray-400 m-0">One-time reset and cleanup operations.</p>
          </div>
        </div>

        <div class="rounded-xl border border-gray-100 bg-gray-50 overflow-hidden divide-y divide-gray-100">

          <!-- Reset Hidden Export Types -->
          <div class="flex items-center gap-3 px-4 py-3">
            <span class="flex-shrink-0 w-7 h-7 bg-white border border-gray-200 rounded-lg flex items-center justify-center shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
              <RotateCcw :size="13" :stroke-width="1.75" color="#7986FF" />
            </span>
            <div class="flex-1 min-w-0">
              <p class="text-[13px] font-semibold text-gray-800 m-0 leading-tight">Reset Hidden Export Types</p>
              <p class="text-[11px] text-gray-400 m-0 mt-0.5 leading-tight">Restore export types hidden on the Quick Export screen.</p>
            </div>
            <button
              type="button"
              class="flex-shrink-0 inline-flex items-center gap-1.5 h-7 px-3 text-[12px] font-semibold rounded-lg border border-indigo-200 bg-white text-indigo-600 cursor-pointer hover:bg-indigo-50 transition-colors disabled:opacity-40"
              :disabled="actionLoading === 'reset_hidden_export_types'"
              @click="runAction('reset_hidden_export_types')"
            >
              <RotateCcw v-if="actionLoading !== 'reset_hidden_export_types'" :size="11" :stroke-width="2.25" />
              {{ actionLoading === 'reset_hidden_export_types' ? 'Resetting…' : 'Reset' }}
            </button>
          </div>

          <!-- Reset Dismissed Notices -->
          <div class="flex items-center gap-3 px-4 py-3">
            <span class="flex-shrink-0 w-7 h-7 bg-white border border-gray-200 rounded-lg flex items-center justify-center shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
              <BellOff :size="13" :stroke-width="1.75" color="#7986FF" />
            </span>
            <div class="flex-1 min-w-0">
              <p class="text-[13px] font-semibold text-gray-800 m-0 leading-tight">Reset Dismissed Notices</p>
              <p class="text-[11px] text-gray-400 m-0 mt-0.5 leading-tight">Restore all notices that were previously dismissed.</p>
            </div>
            <button
              type="button"
              class="flex-shrink-0 inline-flex items-center gap-1.5 h-7 px-3 text-[12px] font-semibold rounded-lg border border-indigo-200 bg-white text-indigo-600 cursor-pointer hover:bg-indigo-50 transition-colors disabled:opacity-40"
              :disabled="actionLoading === 'reset_notices'"
              @click="runAction('reset_notices')"
            >
              <RotateCcw v-if="actionLoading !== 'reset_notices'" :size="11" :stroke-width="2.25" />
              {{ actionLoading === 'reset_notices' ? 'Resetting…' : 'Reset' }}
            </button>
          </div>

          <!-- Clear Export Transient -->
          <div class="flex items-center gap-3 px-4 py-3">
            <span class="flex-shrink-0 w-7 h-7 bg-white border border-gray-200 rounded-lg flex items-center justify-center shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
              <Trash2 :size="13" :stroke-width="1.75" color="#7986FF" />
            </span>
            <div class="flex-1 min-w-0">
              <p class="text-[13px] font-semibold text-gray-800 m-0 leading-tight">Clear Export Transient</p>
              <p class="text-[11px] text-gray-400 m-0 mt-0.5 leading-tight">Clear the transient cache. Use if an export appears stuck.</p>
            </div>
            <button
              type="button"
              class="flex-shrink-0 inline-flex items-center gap-1.5 h-7 px-3 text-[12px] font-semibold rounded-lg border border-indigo-200 bg-white text-indigo-600 cursor-pointer hover:bg-indigo-50 transition-colors disabled:opacity-40"
              :disabled="actionLoading === 'clear_export_transient'"
              @click="runAction('clear_export_transient')"
            >
              <Trash2 v-if="actionLoading !== 'clear_export_transient'" :size="11" :stroke-width="2.25" />
              {{ actionLoading === 'clear_export_transient' ? 'Clearing…' : 'Clear' }}
            </button>
          </div>

        </div>
      </div>

    </div>
  </div>
</template>
