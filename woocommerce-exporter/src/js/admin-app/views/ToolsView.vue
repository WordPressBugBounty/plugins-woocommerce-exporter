<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { storeToRefs } from 'pinia'
import {
  AlertCircle,
  RefreshCw,
  ChevronRight,
  ChevronLeft,
  Package,
  ArrowRight,
  Download,
  UploadCloud,
  Briefcase,
  Info,
  ShieldCheck,
  CheckCircle2,
} from 'lucide-vue-next'
import { FilterTabs, FilterSearchInput, BaseModal, BaseSpinner } from '../components/ui'
import { useToolsStore } from '../stores/tools'
import PageActions from '../components/PageActions.vue'

const store = useToolsStore()
const {
  data,
  isLoading,
  isRefreshing,
  error,
  installStatus,
  installError,
  statusFilter,
  currentPage,
  filteredModules,
  paginatedModules,
  totalPages,
  showingFrom,
  showingTo,
} = storeToRefs(store)

const localSearch = ref('')
let searchTimer = null

function handleSearchInput (val) {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => store.setSearch(val), 250)
}

watch(localSearch, handleSearchInput)

onMounted(store.fetchData)

// ── Module icon helpers ───────────────────────────────────────────────────────

const AVATAR_BG = [
  '#EFF1FF', '#ECFDF5', '#FEF3C7', '#FFF7ED',
  '#F3E8FF', '#CFFAFE', '#FEE2E2', '#F0FDF4',
]
const AVATAR_TEXT = [
  '#6C7BFF', '#059669', '#D97706', '#EA580C',
  '#7C3AED', '#0891B2', '#DC2626', '#16A34A',
]

function avatarStyle (name) {
  const idx = (name.charCodeAt(0) ?? 0) % AVATAR_BG.length
  return { backgroundColor: AVATAR_BG[idx], color: AVATAR_TEXT[idx] }
}

// WordPress.org CDN supports png, jpg, svg, and gif at 128px or 256px — try each in order before falling back to avatar.
const ICON_FORMATS = [
  (slug) => `https://ps.w.org/${slug}/assets/icon-128x128.png`,
  (slug) => `https://ps.w.org/${slug}/assets/icon-256x256.png`,
  (slug) => `https://ps.w.org/${slug}/assets/icon-128x128.jpg`,
  (slug) => `https://ps.w.org/${slug}/assets/icon.svg`,
  (slug) => `https://ps.w.org/${slug}/assets/icon-128x128.gif`,
]

const iconFormatIndex = ref({})
const iconErrors = ref(new Set())

function moduleIconUrl (module) {
  if (!module.slug) return null
  const idx = iconFormatIndex.value[module.name] ?? 0
  return ICON_FORMATS[idx]?.(module.slug) ?? null
}

function onIconError (module) {
  const current = iconFormatIndex.value[module.name] ?? 0
  const next = current + 1
  if (next < ICON_FORMATS.length) {
    iconFormatIndex.value = { ...iconFormatIndex.value, [module.name]: next }
  } else {
    iconErrors.value = new Set([...iconErrors.value, module.name])
  }
}

function showIcon (module) {
  return Boolean(module.slug) && !iconErrors.value.has(module.name)
}

// ── WooCommerce Tools icon mapping ───────────────────────────────────────────

function toolIcon (title) {
  const t = (title ?? '').toLowerCase()
  if (t.includes('import')) return UploadCloud
  if (t.includes('toolkit')) return Briefcase
  return Package
}

// ── Status / action helpers ───────────────────────────────────────────────────

function countFor (key) {
  const counts = data.value?.counts ?? {}
  return counts[key] ?? 0
}

const STATUS_TABS = computed(() => [
  { key: 'all', label: 'All', count: countFor('all') },
  { key: 'active', label: 'Active', count: countFor('active') },
  { key: 'inactive', label: 'Inactive', count: countFor('inactive') },
])

function statusBadgeClass (status) {
  return status === 'active'
    ? 'inline-flex items-center px-2.5 py-1 rounded-full text-[12px] font-medium bg-emerald-50 text-emerald-700'
    : 'inline-flex items-center px-2.5 py-1 rounded-full text-[12px] font-medium bg-[#F3F4F6] text-[#6B7280]'
}

function statusLabel (status) {
  return status === 'active' ? 'Active' : 'Inactive'
}

function actionLabel (status) {
  return status === 'active' ? 'Deactivate' : 'Install'
}

function actionUrl (module) {
  if (module.status === 'active') {
    return data.value?.urls?.plugins ?? '#'
  }
  return module.url ?? '#'
}

function actionClass (status) {
  const base = 'inline-flex items-center justify-center gap-1.5 px-3 py-[6px] rounded-md text-[12px] font-medium border cursor-pointer no-underline transition-colors flex-shrink-0'
  return status === 'active'
    ? `${base} bg-white text-gray-700 border-gray-300 hover:bg-gray-50`
    : `${base} bg-white text-[#6C7BFF] border-[#6C7BFF] hover:bg-indigo-50`
}

// ── Install confirmation ──────────────────────────────────────────────────────

const pendingInstall = ref(null)

const isInstalling = computed(() =>
  installStatus.value === 'installing' || installStatus.value === 'activating'
)

function isWpOrg (module) {
  return Boolean(module.slug)
}

function handleInstallClick (module) {
  if (module.status !== 'active' && isWpOrg(module)) {
    pendingInstall.value = module
    return
  }
  window.open(actionUrl(module), '_blank', 'noopener,noreferrer')
}

function confirmInstall () {
  if (!pendingInstall.value) return
  store.installPlugin(pendingInstall.value.slug)
}

function closeInstallModal () {
  if (isInstalling.value) return
  pendingInstall.value = null
  store.resetInstall()
}

function installStepState (step) {
  const order = ['installing', 'activating', 'success']
  const current = installStatus.value === 'error' ? 'error' : (installStatus.value ?? 'idle')
  const stepIdx = order.indexOf(step)
  const curIdx = order.indexOf(current)

  if (current === 'success') return 'done'
  if (stepIdx < curIdx) return 'done'
  if (stepIdx === curIdx) return 'active'
  return 'pending'
}

// ── Pagination helpers ────────────────────────────────────────────────────────

function prevPage () {
  if (currentPage.value > 1) currentPage.value--
}

function nextPage () {
  if (currentPage.value < totalPages.value) currentPage.value++
}

function goToPage (n) {
  currentPage.value = n
}

function visiblePages () {
  const total = totalPages.value
  const cur = currentPage.value
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)

  const pages = new Set([1, total, cur])
  if (cur > 2) pages.add(cur - 1)
  if (cur < total - 1) pages.add(cur + 1)

  return [...pages].sort((a, b) => a - b)
}
</script>

<template>
  <main class="wse-main" data-testid="app-main">
    <!-- Page header -->
    <div class="wse-header">
      <div>
        <h1 class="wse-page-title">Export Modules</h1>
        <p class="wse-page-sub">Enable or disable export types and third-party integrations.</p>
      </div>
      <PageActions />
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="wse-loading">
      <span class="wse-spinner" />
      Loading tools&hellip;
    </div>

    <!-- Error -->
    <div v-else-if="error" class="wse-error">
      <AlertCircle :size="16" color="#DC2626" :stroke-width="1.75" />
      {{ error }}
    </div>

    <template v-else-if="data">

      <!-- ── WooCommerce Tools promo ──────────────────────────────────── -->
      <section class="wse-tools-promo">

        <!-- Header row -->
        <div class="relative">
          <h2 class="m-0 text-[18px] font-bold text-[#1E1B4B] leading-snug">WooCommerce Tools</h2>
          <p class="m-0 mt-1 text-[14px] text-gray-500">
            Extend your store with powerful WooCommerce extensions.
          </p>

          <!-- Decorative cube -->
          <span class="wse-tools-promo__cube" aria-hidden="true">
            <svg width="110" height="90" viewBox="0 0 110 90" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M55 8L95 30V72L55 94L15 72V30L55 8Z" stroke="#7C3AED" stroke-width="1.5" stroke-linejoin="round" fill="rgba(124,58,237,0.06)" />
              <path d="M55 8V94" stroke="#7C3AED" stroke-width="1" stroke-dasharray="3 3" opacity="0.4" />
              <path d="M15 30L55 52L95 30" stroke="#7C3AED" stroke-width="1.5" stroke-linejoin="round" opacity="0.5" />
              <path d="M55 52V94" stroke="#7C3AED" stroke-width="1" opacity="0.3" />
              <path d="M35 19L75 41" stroke="#A78BFA" stroke-width="1" opacity="0.35" />
              <path d="M75 19L35 41" stroke="#A78BFA" stroke-width="1" opacity="0.35" />
            </svg>
          </span>
        </div>

        <!-- Tool cards grid -->
        <div class="grid grid-cols-2 gap-4 mt-5">
          <a
            v-for="tool in data.woo_tools"
            :key="tool.title"
            :href="tool.url"
            :target="tool.is_internal ? undefined : '_blank'"
            :rel="tool.is_internal ? undefined : 'noopener noreferrer'"
            class="wse-tools-promo__card group"
          >
            <!-- Icon -->
            <div class="flex-shrink-0 w-10 h-10 rounded-[8px] bg-indigo-50 flex items-center justify-center">
              <component :is="toolIcon(tool.title)" :size="20" color="#7986FF" :stroke-width="1.75" />
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
              <div class="text-[14px] font-semibold text-gray-900 leading-snug">{{ tool.title }}</div>
              <p class="m-0 mt-1 text-[13px] text-gray-500 leading-relaxed">{{ tool.description }}</p>
            </div>

            <!-- Arrow -->
            <ArrowRight :size="18" color="#6C7BFF" :stroke-width="1.75" class="flex-shrink-0 self-center transition-transform group-hover:translate-x-0.5" />
          </a>
        </div>

        <!-- Trust banner -->
        <div class="wse-tools-promo__banner">
          <!-- Left: info + copy -->
          <div class="flex items-center gap-3">
            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-[#EDE9FE] flex items-center justify-center">
              <Info :size="15" color="#7C3AED" :stroke-width="1.75" />
            </div>
            <div>
              <div class="text-[14px] font-semibold text-gray-900 leading-snug">Built for Store Owners &amp; Developers</div>
              <p class="m-0 mt-0.5 text-[13px] text-gray-500">Our tools are designed to save time, reduce manual work, and help you manage your WooCommerce store more efficiently.</p>
            </div>
          </div>

          <!-- Right: trust indicator -->
          <div class="flex items-center gap-1.5 flex-shrink-0 text-[13px] font-semibold text-emerald-600">
            <ShieldCheck :size="16" color="#059669" :stroke-width="1.75" />
            Trusted by 10,000+ stores
          </div>
        </div>

      </section>

      <!-- ── Export Modules card ──────────────────────────────────────── -->
      <section class="bg-white border border-gray-200 rounded-[10px] overflow-hidden">

        <!-- Card header -->
        <div class="flex items-start justify-between gap-4 px-5 py-4 border-b border-gray-100">
          <div>
            <h2 class="m-0 text-[14px] font-semibold text-gray-900">Export Modules</h2>
            <p class="m-0 mt-1 text-[13px] text-gray-500">
              Export store details from other WooCommerce and WordPress plugins.
              Install and activate one of the below plugins to enable additional export options.
            </p>
          </div>
          <button
            type="button"
            class="inline-flex items-center gap-1.5 px-3 py-[7px] border border-gray-300 rounded-md bg-white text-gray-700 text-[12.5px] font-medium cursor-pointer transition-colors hover:bg-gray-50 hover:border-gray-400 flex-shrink-0 disabled:opacity-55 disabled:cursor-not-allowed"
            :disabled="isRefreshing"
            @click="store.refresh()"
          >
            <RefreshCw
              :size="13"
              :stroke-width="1.75"
              :class="isRefreshing ? 'animate-spin' : ''"
            />
            {{ isRefreshing ? 'Refreshing…' : 'Refresh Status' }}
          </button>
        </div>

        <!-- Filter + Search bar -->
        <div class="flex items-center justify-between gap-4 px-5 py-3 border-b border-gray-100 flex-wrap">
          <FilterTabs
            :options="STATUS_TABS"
            :model-value="statusFilter"
            @update:model-value="store.setStatusFilter($event)"
          />
          <FilterSearchInput
            v-model="localSearch"
            placeholder="Search modules..."
            class="min-w-[220px]"
          />
        </div>

        <!-- Table header -->
        <div class="grid grid-cols-[280px_1fr_120px_130px] gap-x-4 px-5 py-2.5 border-b border-gray-100 bg-gray-50/50">
          <span class="text-[11px] font-medium text-[#9CA3AF] uppercase tracking-wide">Module</span>
          <span class="text-[11px] font-medium text-[#9CA3AF] uppercase tracking-wide">Description</span>
          <span class="text-[11px] font-medium text-[#9CA3AF] uppercase tracking-wide">Status</span>
          <span class="text-[11px] font-medium text-[#9CA3AF] uppercase tracking-wide">Action</span>
        </div>

        <!-- Module list -->
        <div v-if="paginatedModules.length">
          <div
            v-for="module in paginatedModules"
            :key="module.name"
            class="grid grid-cols-[280px_1fr_120px_130px] gap-x-4 items-center px-5 py-3 border-b border-[#F3F4F6] last:border-0 hover:bg-gray-50/50 transition-colors"
          >
            <!-- Column 1: Plugin icon + name -->
            <div class="flex items-center gap-3 min-w-0">
              <div
                class="w-10 h-10 rounded-lg flex-shrink-0 overflow-hidden flex items-center justify-center border border-gray-100"
                :style="showIcon(module) ? {} : avatarStyle(module.title)"
              >
                <img
                  v-if="showIcon(module)"
                  :src="moduleIconUrl(module)"
                  :alt="module.title"
                  class="w-full h-full object-cover"
                  @error="onIconError(module)"
                />
                <span v-else class="text-[15px] font-bold leading-none">
                  {{ module.title.charAt(0).toUpperCase() }}
                </span>
              </div>
              <a
                :href="module.wp_org_url ?? module.url ?? '#'"
                target="_blank"
                rel="noopener noreferrer"
                class="text-[13px] font-semibold text-[#111827] no-underline hover:text-indigo-600 hover:underline leading-snug truncate"
              >
                {{ module.title }}
              </a>
            </div>

            <!-- Column 2: Description -->
            <p v-if="module.description" class="m-0 text-[13px] text-[#6B7280] leading-relaxed line-clamp-2">
              {{ module.description }}
            </p>
            <span v-else />

            <!-- Column 3: Status badge -->
            <div>
              <span :class="statusBadgeClass(module.status)">
                {{ statusLabel(module.status) }}
              </span>
            </div>

            <!-- Column 4: Action button -->
            <div>
              <button
                type="button"
                :class="actionClass(module.status)"
                @click="handleInstallClick(module)"
              >
                <Download v-if="module.status !== 'active'" :size="13" :stroke-width="1.75" />
                {{ actionLabel(module.status) }}
              </button>
            </div>
          </div>
        </div>

        <!-- Empty state -->
        <div v-else class="py-10 text-center text-[13px] text-gray-400">
          No modules match your current filter.
        </div>

        <!-- Pagination -->
        <div
          v-if="filteredModules.length > 0"
          class="flex items-center justify-between gap-4 px-5 py-3 border-t border-gray-100"
        >
          <span class="text-[12.5px] text-gray-500">
            Showing {{ showingFrom }} to {{ showingTo }} of {{ filteredModules.length }} modules
          </span>

          <div class="flex items-center gap-1">
            <!-- Prev -->
            <button
              type="button"
              class="inline-flex items-center justify-center w-7 h-7 rounded-md border border-gray-200 bg-white text-gray-600 text-xs cursor-pointer transition-colors hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed"
              :disabled="currentPage === 1"
              @click="prevPage"
            >
              <ChevronLeft :size="14" :stroke-width="1.75" />
            </button>

            <!-- Page numbers -->
            <template v-for="(page, i) in visiblePages()" :key="page">
              <span
                v-if="i > 0 && visiblePages()[i - 1] !== page - 1"
                class="inline-flex items-center justify-center w-7 h-7 text-gray-400 text-xs select-none"
              >…</span>
              <button
                type="button"
                class="inline-flex items-center justify-center w-7 h-7 rounded-md border text-xs font-medium cursor-pointer transition-colors"
                :class="currentPage === page
                  ? 'bg-indigo-600 text-white border-indigo-600'
                  : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'"
                @click="goToPage(page)"
              >
                {{ page }}
              </button>
            </template>

            <!-- Next -->
            <button
              type="button"
              class="inline-flex items-center justify-center w-7 h-7 rounded-md border border-gray-200 bg-white text-gray-600 text-xs cursor-pointer transition-colors hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed"
              :disabled="currentPage === totalPages"
              @click="nextPage"
            >
              <ChevronRight :size="14" :stroke-width="1.75" />
            </button>
          </div>
        </div>

      </section>
    </template>
  </main>

  <!-- ── Install modal ───────────────────────────────────────────────── -->
  <BaseModal
    :model-value="Boolean(pendingInstall)"
    title="Install Plugin"
    size="sm"
    :close-on-backdrop="!isInstalling"
    @update:model-value="closeInstallModal"
  >
    <div class="flex flex-col gap-5">

      <!-- Plugin identity row -->
      <div class="flex items-start gap-3">
        <div
          class="w-10 h-10 rounded-lg flex-shrink-0 overflow-hidden flex items-center justify-center border border-gray-100"
          :style="pendingInstall && showIcon(pendingInstall) ? {} : (pendingInstall ? avatarStyle(pendingInstall.title) : {})"
        >
          <img
            v-if="pendingInstall && showIcon(pendingInstall)"
            :src="moduleIconUrl(pendingInstall)"
            :alt="pendingInstall.title"
            class="w-full h-full object-cover"
            @error="onIconError(pendingInstall)"
          />
          <span v-else-if="pendingInstall" class="text-[15px] font-bold leading-none">
            {{ pendingInstall.title.charAt(0).toUpperCase() }}
          </span>
        </div>
        <div class="flex-1 min-w-0">
          <div class="text-[14px] font-semibold text-gray-900 leading-snug">{{ pendingInstall?.title }}</div>
          <p v-if="pendingInstall?.description" class="m-0 mt-1 text-[13px] text-gray-500 leading-relaxed">
            {{ pendingInstall.description }}
          </p>
        </div>
      </div>

      <!-- Idle state: intro copy -->
      <p v-if="!installStatus" class="m-0 text-[13px] text-gray-600 leading-relaxed">
        This plugin is available on <strong>WordPress.org</strong>. Click <em>Install</em> to download and activate it automatically — no need to leave this page.
      </p>

      <!-- Progress stepper (installing / activating / success) -->
      <div v-if="installStatus && installStatus !== 'error'" class="wse-install-steps">
        <div
          v-for="(step, idx) in [
            { key: 'installing', label: 'Downloading & Installing' },
            { key: 'activating', label: 'Activating' },
            { key: 'success',    label: 'Done' },
          ]"
          :key="step.key"
          class="wse-install-steps__step"
          :class="`wse-install-steps__step--${installStepState(step.key)}`"
        >
          <!-- Connector line before every step except the first -->
          <div v-if="idx > 0" class="wse-install-steps__connector" :class="installStepState(step.key) !== 'pending' ? 'wse-install-steps__connector--done' : ''" />

          <!-- Indicator -->
          <div class="wse-install-steps__indicator">
            <CheckCircle2 v-if="installStepState(step.key) === 'done'" :size="18" :stroke-width="2" />
            <BaseSpinner v-else-if="installStepState(step.key) === 'active'" size="sm" />
            <span v-else class="wse-install-steps__dot" />
          </div>

          <!-- Label -->
          <span class="wse-install-steps__label">{{ step.label }}</span>
        </div>
      </div>

      <!-- Success state -->
      <div v-if="installStatus === 'success'" class="wse-install-success">
        <div class="wse-install-success__icon">
          <CheckCircle2 :size="22" color="#16a34a" :stroke-width="2" />
        </div>
        <div>
          <div class="text-[13px] font-semibold text-gray-900">
            {{ pendingInstall?.title }} is now active!
          </div>
          <p class="m-0 mt-0.5 text-[12px] text-gray-500 leading-relaxed">
            The plugin has been installed and activated. Refresh the page to use its features.
          </p>
        </div>
      </div>

      <!-- Error state -->
      <div v-if="installStatus === 'error'" class="wse-install-error">
        <AlertCircle :size="16" color="#dc2626" :stroke-width="1.75" class="flex-shrink-0 mt-px" />
        <p class="m-0 text-[13px] text-[#dc2626] leading-relaxed">{{ installError }}</p>
      </div>

    </div>

    <template #footer>
      <!-- Idle: Cancel + Install -->
      <template v-if="!installStatus">
        <button
          type="button"
          class="inline-flex items-center justify-center px-4 py-[7px] border border-gray-300 rounded-md bg-white text-gray-700 text-[13px] font-medium cursor-pointer transition-colors hover:bg-gray-50"
          @click="closeInstallModal"
        >
          Cancel
        </button>
        <button
          type="button"
          class="inline-flex items-center justify-center gap-1.5 px-4 py-[7px] rounded-md bg-[#6C7BFF] text-white text-[13px] font-medium cursor-pointer transition-colors hover:bg-indigo-700 border-0"
          @click="confirmInstall"
        >
          <Download :size="13" :stroke-width="1.75" />
          Install
        </button>
      </template>

      <!-- In-progress: locked buttons -->
      <template v-else-if="isInstalling">
        <button
          type="button"
          disabled
          class="inline-flex items-center justify-center px-4 py-[7px] border border-gray-200 rounded-md bg-white text-gray-400 text-[13px] font-medium cursor-not-allowed"
        >
          Cancel
        </button>
        <button
          type="button"
          disabled
          class="inline-flex items-center justify-center gap-2 px-4 py-[7px] rounded-md bg-indigo-400 text-white text-[13px] font-medium cursor-not-allowed border-0"
        >
          <BaseSpinner size="sm" />
          {{ installStatus === 'activating' ? 'Activating…' : 'Installing…' }}
        </button>
      </template>

      <!-- Success: single Close button -->
      <template v-else-if="installStatus === 'success'">
        <button
          type="button"
          class="inline-flex items-center justify-center px-4 py-[7px] rounded-md bg-[#6C7BFF] text-white text-[13px] font-medium cursor-pointer transition-colors hover:bg-indigo-700 border-0"
          @click="closeInstallModal"
        >
          Close
        </button>
      </template>

      <!-- Error: Try Again + Cancel -->
      <template v-else-if="installStatus === 'error'">
        <button
          type="button"
          class="inline-flex items-center justify-center px-4 py-[7px] border border-gray-300 rounded-md bg-white text-gray-700 text-[13px] font-medium cursor-pointer transition-colors hover:bg-gray-50"
          @click="closeInstallModal"
        >
          Cancel
        </button>
        <button
          type="button"
          class="inline-flex items-center justify-center gap-1.5 px-4 py-[7px] rounded-md bg-[#6C7BFF] text-white text-[13px] font-medium cursor-pointer transition-colors hover:bg-indigo-700 border-0"
          @click="confirmInstall"
        >
          <Download :size="13" :stroke-width="1.75" />
          Try Again
        </button>
      </template>
    </template>
  </BaseModal>
</template>

<style lang="scss" scoped src="./ToolsView.scss"></style>
