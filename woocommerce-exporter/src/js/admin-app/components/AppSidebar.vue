<script setup>
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import {
  Home,
  Clock,
  FileText,
  Archive,
  Settings,
  Wrench,
  Zap,
  PanelLeftClose,
  PanelLeftOpen,
  LayoutGrid,
  Lock,
  ScrollText
} from 'lucide-vue-next'
import mascotUrl from '@/assets/wse-mascot.svg'

const route = useRoute()
const router = useRouter()

const collapsed = ref(false)

function toggleCollapsed () {
  collapsed.value = !collapsed.value
}

function isActive (...names) {
  return names.includes(route.name)
}
</script>

<template>
  <aside
    class="wse-sidebar flex flex-col bg-white border-r border-gray-200 pb-0 sticky top-[32px] h-[calc(100vh-32px)] overflow-y-auto self-start"
    :class="collapsed ? 'w-16 min-w-[64px]' : 'w-60 min-w-[240px]'"
  >

    <!-- Logo -->
    <div v-if="!collapsed" class="flex items-center gap-3 px-4 pt-5 pb-4">
      <img :src="mascotUrl" alt="" class="w-12 h-12 flex-shrink-0 rounded-xl ring-1 ring-gray-900/[0.06] shadow-sm" />
      <div class="flex flex-col gap-[6px]">
        <span class="text-[14px] font-bold uppercase text-gray-900 leading-none tracking-[0.01em]">Store Exporter</span>
        <span class="self-start text-[9px] font-bold uppercase tracking-[0.14em] text-white bg-gradient-to-r from-indigo-600 to-violet-600 px-2 py-[3px] rounded-full leading-none shadow-sm">Free</span>
      </div>
    </div>
    <div v-else class="flex justify-center pt-5 pb-4">
      <img :src="mascotUrl" alt="Store Exporter for WooCommerce" class="w-12 h-12 rounded-xl ring-1 ring-gray-900/[0.06] shadow-sm" />
    </div>

    <!-- Quick Export CTA -->
    <div :class="collapsed ? 'px-2 pb-3' : 'px-3 pb-3'">
      <a
        href="#/quick-export"
        class="flex items-center gap-2.5 w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 !text-white text-[13px] font-semibold rounded-xl no-underline shadow-sm transition-colors"
        :class="collapsed ? 'justify-center px-0' : 'justify-center'"
        @click.prevent="router.push('/quick-export')"
      >
        <Zap :size="15" :stroke-width="2" fill="currentColor" class="flex-shrink-0" />
        <span v-if="!collapsed">Quick Export</span>
      </a>
    </div>

    <!-- Separator below CTA -->
    <div class="h-px bg-gray-200 flex-shrink-0" />

    <!-- Navigation -->
    <nav :class="collapsed ? 'px-2 pt-3' : 'px-2.5 pt-3'" class="flex-1">

      <!-- EXPORTS group -->
      <div
        class="text-[11px] font-semibold text-gray-500 tracking-[0.09em] uppercase px-3 pt-0 pb-3"
        :class="collapsed && 'hidden'"
      >EXPORTS</div>

      <a
        class="wse-nav-link mb-[2px]"
        :class="[collapsed ? 'justify-center px-[9px]' : 'px-3', isActive('overview') ? 'wse-nav-link--active' : 'wse-nav-link--default']"
        href="#"
        @click.prevent="router.push('/')"
      >
        <Home :size="16" :stroke-width="1.75" />
        <span v-if="!collapsed" class="flex-1">Overview</span>
      </a>

      <a
        class="wse-nav-link mb-[2px]"
        :class="[collapsed ? 'justify-center px-[9px]' : 'px-3', isActive('scheduled-exports') ? 'wse-nav-link--active' : 'wse-nav-link--default']"
        href="#"
        @click.prevent="router.push('/scheduled-exports')"
      >
        <Clock :size="16" :stroke-width="1.75" />
        <span v-if="!collapsed" class="flex-1">Scheduled Exports</span>
        <Lock v-if="!collapsed" :size="12" :stroke-width="2" class="flex-shrink-0 text-amber-500" />
      </a>

      <a
        class="wse-nav-link mb-[2px]"
        :class="[collapsed ? 'justify-center px-[9px]' : 'px-3', isActive('export-templates') ? 'wse-nav-link--active' : 'wse-nav-link--default']"
        href="#"
        @click.prevent="router.push('/export-templates')"
      >
        <FileText :size="16" :stroke-width="1.75" />
        <span v-if="!collapsed" class="flex-1">Export Templates</span>
        <Lock v-if="!collapsed" :size="12" :stroke-width="2" class="flex-shrink-0 text-amber-500" />
      </a>

      <a
        class="wse-nav-link mb-[2px]"
        :class="[collapsed ? 'justify-center px-[9px]' : 'px-3', isActive('archives') ? 'wse-nav-link--active' : 'wse-nav-link--default']"
        href="#"
        @click.prevent="router.push('/archives')"
      >
        <Archive :size="16" :stroke-width="1.75" />
        <span v-if="!collapsed" class="flex-1">Archives</span>
        <Lock v-if="!collapsed" :size="12" :stroke-width="2" class="flex-shrink-0 text-amber-500" />
      </a>

      <!-- TOOLS group -->
      <div v-if="!collapsed" class="h-px bg-gray-100 mt-4 mb-0" />
      <div
        class="text-[11px] font-semibold text-gray-500 tracking-[0.09em] uppercase px-3 pt-3 pb-3"
        :class="collapsed && 'hidden'"
      >TOOLS</div>

      <a
        class="wse-nav-link mb-[2px]"
        :class="[collapsed ? 'justify-center px-[9px]' : 'px-3', isActive('custom-meta-fields', 'custom-meta-fields-form') ? 'wse-nav-link--active' : 'wse-nav-link--default']"
        href="#"
        @click.prevent="router.push('/custom-meta-fields')"
      >
        <Wrench :size="16" :stroke-width="1.75" />
        <span v-if="!collapsed" class="flex-1">Custom Meta Fields</span>
      </a>

      <a
        class="wse-nav-link mb-[2px]"
        :class="[collapsed ? 'justify-center px-[9px]' : 'px-3', isActive('tools') ? 'wse-nav-link--active' : 'wse-nav-link--default']"
        href="#"
        @click.prevent="router.push('/tools')"
      >
        <LayoutGrid :size="16" :stroke-width="1.75" />
        <span v-if="!collapsed" class="flex-1">Export Modules</span>
      </a>

      <!-- SYSTEM group -->
      <div v-if="!collapsed" class="h-px bg-gray-100 mt-4 mb-0" />
      <div
        class="text-[11px] font-semibold text-gray-500 tracking-[0.09em] uppercase px-3 pt-3 pb-3"
        :class="collapsed && 'hidden'"
      >SYSTEM</div>

      <a
        class="wse-nav-link mb-[2px]"
        :class="[collapsed ? 'justify-center px-[9px]' : 'px-3', isActive('audit-log') ? 'wse-nav-link--active' : 'wse-nav-link--default']"
        href="#"
        @click.prevent="router.push('/audit-log')"
      >
        <ScrollText :size="16" :stroke-width="1.75" />
        <span v-if="!collapsed" class="flex-1">Audit Log</span>
        <Lock v-if="!collapsed" :size="12" :stroke-width="2" class="flex-shrink-0 text-amber-500" />
      </a>

      <a
        class="wse-nav-link mb-[2px]"
        :class="[collapsed ? 'justify-center px-[9px]' : 'px-3', isActive('settings') ? 'wse-nav-link--active' : 'wse-nav-link--default']"
        href="#"
        @click.prevent="router.push('/settings')"
      >
        <Settings :size="16" :stroke-width="1.75" />
        <span v-if="!collapsed" class="flex-1">Settings</span>
      </a>

    </nav>

    <!-- Collapse toggle -->
    <button
      type="button"
      class="flex items-center gap-1.5 px-[18px] py-3.5 mt-auto border-0 border-t border-gray-200 bg-transparent text-gray-500 text-xs font-medium cursor-pointer text-left transition-all hover:text-gray-900 hover:bg-gray-50"
      :class="collapsed && 'justify-center px-0'"
      :aria-label="collapsed ? 'Expand sidebar' : 'Collapse sidebar'"
      @click="toggleCollapsed"
    >
      <component
        :is="collapsed ? PanelLeftOpen : PanelLeftClose"
        :size="14"
        :stroke-width="1.75"
      />
      <span v-if="!collapsed">Collapse</span>
    </button>

  </aside>
</template>

<style lang="scss" scoped src="./AppSidebar.scss"></style>
