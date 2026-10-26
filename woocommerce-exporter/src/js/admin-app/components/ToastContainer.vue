<script setup>
import { storeToRefs } from 'pinia'
import { CheckCircle2, AlertCircle, AlertTriangle, Info, X } from 'lucide-vue-next'
import { useToastsStore } from '../stores/toasts'

const store = useToastsStore()
const { toasts } = storeToRefs(store)

const ICONS = {
  success: CheckCircle2,
  error: AlertCircle,
  warning: AlertTriangle,
  info: Info
}

const TYPE_CLASSES = {
  success: { border: 'border-l-[3px] border-l-emerald-500', icon: 'text-emerald-500' },
  error: { border: 'border-l-[3px] border-l-red-600', icon: 'text-red-600' },
  warning: { border: 'border-l-[3px] border-l-amber-500', icon: 'text-amber-600' },
  info: { border: 'border-l-[3px] border-l-indigo-600', icon: 'text-indigo-600' },
}
</script>

<template>
  <div class="fixed bottom-6 right-6 flex flex-col gap-2 z-[100000] pointer-events-none" aria-live="polite">
    <transition-group name="wse-toast">
      <div
        v-for="t in toasts"
        :key="t.id"
        class="flex items-center gap-[9px] py-2.5 pr-3 pl-3.5 bg-white border border-gray-200 rounded-[10px] shadow-[0_8px_24px_rgba(0,0,0,0.12)] min-w-[240px] max-w-[380px] text-[13px] text-gray-900 pointer-events-auto"
        :class="TYPE_CLASSES[t.type]?.border"
        role="status"
      >
        <component
          :is="ICONS[t.type] ?? Info"
          :size="16"
          :stroke-width="2"
          :class="TYPE_CLASSES[t.type]?.icon"
        />
        <span class="flex-1 min-w-0">{{ t.message }}</span>
        <button
          class="bg-transparent border-0 cursor-pointer text-gray-400 p-0.5 rounded flex items-center hover:text-gray-700 hover:bg-gray-100"
          @click="store.dismiss(t.id)"
          aria-label="Dismiss"
        >
          <X :size="13" :stroke-width="2" />
        </button>
      </div>
    </transition-group>
  </div>
</template>

<style lang="scss" scoped src="./ToastContainer.scss"></style>
