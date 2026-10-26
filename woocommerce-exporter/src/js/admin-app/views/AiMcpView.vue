<script setup>
import { ref, computed, onMounted } from 'vue'
import { storeToRefs } from 'pinia'
import {
  AlertCircle,
  ArrowRight,
  ListChecks,
  Calculator,
  Receipt,
  Package,
  Activity,
  Search,
  Lock,
  UserCheck,
  Eye,
  Sparkles,
} from 'lucide-vue-next'
import { BaseButton, BaseSpinner } from '../components/ui'
import PageActions from '../components/PageActions.vue'
import { useAiMcpStore } from '../stores/aiMcp'
import { useToastsStore } from '../stores/toasts'
import { KB_ARTICLES } from '../docLinks'
import claudeLogo from '@/assets/ai-clients/claude.svg'
import chatgptLogo from '@/assets/ai-clients/openai-chatgpt.svg'
import cursorLogo from '@/assets/ai-clients/cursor.svg'

const store = useAiMcpStore()
const toasts = useToastsStore()
const { data, isLoading, error, isSavingWrite, installStatus } = storeToRefs(store)

onMounted(store.fetchData)

// Wait this long after an install so the success toast can be read before the reload.
const RELOAD_DELAY_MS = 1000

const CLIENTS = [
  { name: 'Claude', logo: claudeLogo },
  { name: 'ChatGPT', logo: chatgptLogo },
  // The Cursor mark is light, so it sits on a dark tile, like in its brand kit.
  { name: 'Cursor', logo: cursorLogo, dark: true },
]

const RECIPE_ICONS = {
  'list-checks': ListChecks,
  calculator: Calculator,
  receipt: Receipt,
  package: Package,
  activity: Activity,
  search: Search,
}

const WHO_ICONS = [UserCheck, Eye, Sparkles]

const labels = computed(() => data.value?.labels ?? {})
const cta = computed(() => data.value?.cta ?? {})
const writeAccess = computed(() => data.value?.write_access ?? {})
const endpoint = computed(() => data.value?.endpoint ?? {})

/**
 * Split the intro text into plain parts and one StoreAgent link part.
 *
 * The translated text marks the link with <link></link>. When a translation
 * breaks the tags, the text shows without a link and without the tags.
 */
const introParts = computed(() => {
  const text = labels.value.intro ?? ''
  const match = text.match(/^([\s\S]*)<link>([\s\S]*)<\/link>([\s\S]*)$/)
  if (!match) return [{ text: text.replace(/<\/?link>/g, '') }]
  return [
    { text: match[1] },
    { text: match[2], url: labels.value.wporg_url },
    { text: match[3] },
  ]
})

// ── Allow AI to make changes ─────────────────────────────────────────────────

const writePartial = computed(() => {
  const { available, enabled, allowed, total } = writeAccess.value
  if (!available || enabled || !allowed) return ''
  return (labels.value.write_partial ?? '').replace('%1$d', allowed).replace('%2$d', total)
})

async function toggleWriteAccess () {
  if (!writeAccess.value.available || isSavingWrite.value) return
  const result = await store.setWriteAccess(!writeAccess.value.enabled)
  toasts.push(result.message || labels.value.write_failed, result.status, 4000)
}

// ── Install / activate StoreAgent ────────────────────────────────────────────

const isActivation = computed(() => data.value?.state === 'inactive')

async function installStoreAgent () {
  const message = await store.installStoreAgent()
  if (installStatus.value === 'success') {
    toasts.push(labels.value.install_done, 'success')
    // The ?tab=ai_mcp query keeps the reload on this screen.
    setTimeout(() => window.location.reload(), RELOAD_DELAY_MS)
    return
  }
  toasts.push(message || (isActivation.value ? labels.value.activate_failed : labels.value.install_failed), 'error', 5000)
}

// ── Prompt cards ─────────────────────────────────────────────────────────────

const activeCategory = ref('all')

const visibleRecipes = computed(() => {
  const recipes = data.value?.recipes ?? []
  if (activeCategory.value === 'all') return recipes
  return recipes.filter(recipe => recipe.category === activeCategory.value)
})

function isLocked (recipe) {
  return recipe.needs_write && !writeAccess.value.enabled
}

// ── Endpoint copy ────────────────────────────────────────────────────────────

const endpointEl = ref(null)
const copyMessage = ref('')

/**
 * Select the endpoint URL text so the user can copy it with the keyboard.
 */
function selectEndpoint () {
  const range = document.createRange()
  range.selectNodeContents(endpointEl.value)
  const selection = window.getSelection()
  selection.removeAllRanges()
  selection.addRange(range)
}

/**
 * Copy the endpoint URL.
 *
 * navigator.clipboard exists only in a secure context, so a plain-HTTP admin
 * falls back to execCommand. When that also fails, the URL stays selected and
 * the help text tells the user to copy it with the keyboard.
 */
async function copyEndpoint () {
  copyMessage.value = ''
  try {
    if (window.isSecureContext && navigator.clipboard) {
      await navigator.clipboard.writeText(endpoint.value.url)
      toasts.push(labels.value.copied, 'success')
      return
    }
  } catch {
    // Fall through to the selection fallback.
  }

  selectEndpoint()
  if (document.execCommand('copy')) {
    toasts.push(labels.value.copied, 'success')
    return
  }
  copyMessage.value = labels.value.copy_manual
}
</script>

<template>
  <main class="wse-main overflow-y-auto" data-testid="app-main">

    <div class="wse-header">
      <div>
        <h1 class="wse-page-title">{{ labels.title ?? 'AI / MCP' }}</h1>
        <p v-if="labels.subtitle" class="wse-page-sub">{{ labels.subtitle }}</p>
      </div>
      <PageActions />
    </div>

    <div v-if="isLoading" class="flex justify-center py-16">
      <BaseSpinner />
    </div>

    <div
      v-else-if="error"
      class="flex items-center gap-2 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-[13px] text-red-700"
      role="alert"
    >
      <AlertCircle :size="16" :stroke-width="1.75" aria-hidden="true" />
      {{ error }}
    </div>

    <div v-else-if="data" class="flex flex-col gap-5" data-testid="ai-mcp-screen">

      <!-- ── Hero ── -->
      <section
        class="flex flex-col lg:flex-row gap-8 p-6 lg:p-8 rounded-2xl border border-indigo-100 bg-gradient-to-br from-indigo-50 via-white to-violet-50"
        data-testid="ai-mcp-hero"
      >
        <div class="flex-1 min-w-0">
          <p class="text-[11px] font-bold uppercase tracking-[0.12em] text-indigo-600 m-0 mb-2">{{ labels.eyebrow }}</p>
          <h2 class="text-[26px] font-bold text-gray-900 leading-tight m-0 mb-3">{{ labels.heading }}</h2>
          <p class="text-[14px] leading-relaxed text-gray-600 m-0 mb-6 max-w-[620px]">
            <template v-for="(part, index) in introParts" :key="index">
              <a
                v-if="part.url"
                :href="part.url"
                target="_blank"
                rel="noopener noreferrer"
                class="font-semibold text-indigo-600 underline"
              >{{ part.text }}</a>
              <template v-else>{{ part.text }}</template>
            </template>
          </p>

          <div class="flex flex-wrap items-center gap-x-6 gap-y-3">
            <BaseButton
              v-if="cta.action === 'install'"
              :loading="installStatus === 'installing' || installStatus === 'success'"
              data-testid="ai-mcp-cta"
              @click="installStoreAgent"
            >
              {{ installStatus === 'installing' ? (isActivation ? labels.activating : labels.installing) : cta.label }}
              <ArrowRight v-if="installStatus !== 'installing'" :size="15" :stroke-width="2" aria-hidden="true" />
            </BaseButton>
            <BaseButton
              v-else-if="cta.action === 'link'"
              tag="a"
              :href="cta.url"
              :target="cta.external ? '_blank' : undefined"
              :rel="cta.external ? 'noopener noreferrer' : undefined"
              class="no-underline !text-white"
              data-testid="ai-mcp-cta"
            >
              {{ cta.label }}
              <ArrowRight :size="15" :stroke-width="2" aria-hidden="true" />
            </BaseButton>

            <div class="flex items-center gap-2.5">
              <button
                type="button"
                class="relative w-11 h-6 rounded-full border-none p-0 cursor-pointer flex-shrink-0 transition-colors focus-visible:outline-none focus-visible:ring-[3px] focus-visible:ring-indigo-500/25 disabled:cursor-not-allowed disabled:opacity-50"
                :class="writeAccess.enabled ? 'bg-indigo-600' : 'bg-gray-300'"
                role="switch"
                :aria-checked="writeAccess.enabled ? 'true' : 'false'"
                :aria-label="labels.write_toggle"
                aria-describedby="wse-ai-mcp-cta-note wse-ai-mcp-write-note"
                :aria-busy="isSavingWrite || undefined"
                :disabled="!writeAccess.available || isSavingWrite"
                data-testid="ai-mcp-write-toggle"
                @click="toggleWriteAccess"
              >
                <span
                  class="absolute top-[3px] left-[3px] w-[18px] h-[18px] rounded-full bg-white shadow-sm transition-transform"
                  :class="writeAccess.enabled && 'translate-x-[20px]'"
                />
              </button>
              <span
                class="text-[12px] font-bold uppercase tracking-[0.06em]"
                :class="writeAccess.available ? 'text-gray-800' : 'text-gray-500'"
                aria-hidden="true"
              >{{ labels.write_toggle }}</span>
              <span
                v-if="writePartial"
                class="text-[11px] font-semibold px-2 py-[3px] rounded-full bg-amber-50 text-amber-700"
                data-testid="ai-mcp-write-partial"
              >{{ writePartial }}</span>
            </div>
          </div>

          <p
            id="wse-ai-mcp-cta-note"
            class="text-[12px] text-gray-500 m-0 mt-3"
            aria-live="polite"
            data-testid="ai-mcp-cta-note"
          >{{ cta.note }}</p>
          <p
            id="wse-ai-mcp-write-note"
            class="text-[12px] text-amber-700 m-0 mt-1"
            :hidden="!writeAccess.note"
            data-testid="ai-mcp-write-note"
          >{{ writeAccess.note }}</p>
        </div>

        <div class="flex flex-col gap-2.5 lg:w-[150px] flex-shrink-0">
          <div
            v-for="client in CLIENTS"
            :key="client.name"
            class="flex items-center gap-3 px-3 py-2.5 bg-white rounded-xl border border-gray-200 shadow-sm"
          >
            <span
              class="w-7 h-7 flex items-center justify-center rounded-md flex-shrink-0"
              :class="client.dark && 'bg-gray-900'"
            >
              <img :src="client.logo" alt="" :class="client.dark ? 'w-4 h-4' : 'w-6 h-6'" />
            </span>
            <span class="text-[13px] font-semibold text-gray-900">{{ client.name }}</span>
          </div>
          <span class="text-[11px] text-gray-500 text-right">{{ labels.any_client }}</span>
        </div>
      </section>

      <!-- ── Try It ── -->
      <section class="bg-white border border-gray-200 rounded-2xl p-6" data-testid="ai-mcp-recipes">
        <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
          <div>
            <h2 class="text-[16px] font-bold text-gray-900 m-0 mb-1">{{ labels.try_heading }}</h2>
            <p class="text-[13px] text-gray-500 m-0" data-testid="ai-mcp-recipes-help">
              {{ writeAccess.enabled ? labels.try_help_on : labels.try_help_off }}
            </p>
          </div>
          <a
            :href="KB_ARTICLES.aiMcp"
            target="_blank"
            rel="noopener noreferrer"
            class="text-[13px] font-semibold text-indigo-600 no-underline hover:underline whitespace-nowrap"
          >{{ labels.docs_link }} <span aria-hidden="true">→</span></a>
        </div>

        <div class="flex flex-wrap gap-2 mb-4" role="group" :aria-label="labels.filter_label">
          <button
            v-for="category in data.categories"
            :key="category.id"
            type="button"
            class="px-3 py-1.5 rounded-full border text-[12px] font-medium cursor-pointer transition-colors"
            :class="activeCategory === category.id
              ? 'bg-indigo-600 border-indigo-600 text-white'
              : 'bg-white border-gray-300 text-gray-600 hover:border-gray-400'"
            :aria-pressed="activeCategory === category.id ? 'true' : 'false'"
            :data-testid="`ai-mcp-chip-${category.id}`"
            @click="activeCategory = category.id"
          >{{ category.label }}</button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
          <article
            v-for="recipe in visibleRecipes"
            :key="recipe.id"
            class="flex flex-col gap-3 p-4 rounded-xl border border-gray-200 transition-opacity"
            :class="isLocked(recipe) && 'opacity-60'"
            :data-testid="`ai-mcp-recipe-${recipe.id}`"
          >
            <div class="flex items-center gap-2.5">
              <span class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center flex-shrink-0">
                <component :is="RECIPE_ICONS[recipe.icon]" :size="15" :stroke-width="1.75" aria-hidden="true" />
              </span>
              <h3 class="flex-1 text-[14px] font-semibold text-gray-900 m-0">{{ recipe.title }}</h3>
              <span
                v-if="isLocked(recipe)"
                class="inline-flex items-center gap-1 text-[11px] font-medium px-2 py-[3px] rounded-full bg-orange-50 text-orange-700 whitespace-nowrap"
                data-testid="ai-mcp-recipe-lock"
              >
                <Lock :size="11" :stroke-width="2" aria-hidden="true" />
                {{ labels.needs_write }}
              </span>
            </div>
            <p class="text-[13px] leading-relaxed text-gray-700 bg-gray-50 rounded-lg px-3 py-2.5 m-0">“{{ recipe.prompt }}”</p>
            <div class="flex flex-wrap gap-1.5 mt-auto">
              <code
                v-for="ability in recipe.abilities"
                :key="ability"
                class="text-[11px] text-indigo-700 bg-white border border-indigo-100 rounded px-1.5 py-0.5"
              >{{ ability }}</code>
            </div>
          </article>
        </div>
      </section>

      <!-- ── MCP endpoint ── -->
      <section class="flex flex-col lg:flex-row gap-6 bg-white border border-gray-200 rounded-2xl p-6" data-testid="ai-mcp-endpoint">
        <div class="flex-1 min-w-0">
          <h2 class="text-[16px] font-bold text-gray-900 m-0 mb-1">{{ labels.endpoint_heading }}</h2>
          <p class="text-[13px] text-gray-500 m-0 mb-4" aria-live="polite" data-testid="ai-mcp-endpoint-help">{{ copyMessage || endpoint.help }}</p>
          <div class="flex items-center gap-2">
            <code
              ref="endpointEl"
              class="flex-1 min-w-0 truncate text-[12px] px-3 py-2 rounded-lg border"
              :class="endpoint.available ? 'text-gray-900 bg-gray-50 border-gray-200' : 'text-gray-600 bg-gray-50 border-gray-200'"
              :aria-label="labels.endpoint_label"
              data-testid="ai-mcp-endpoint-url"
            >{{ endpoint.url }}</code>
            <BaseButton
              variant="secondary"
              size="sm"
              class="!py-2"
              :disabled="!endpoint.available"
              data-testid="ai-mcp-endpoint-copy"
              @click="copyEndpoint"
            >{{ labels.copy }}</BaseButton>
          </div>
        </div>

        <div class="lg:w-[300px] flex-shrink-0 bg-gray-50 border border-gray-200 rounded-xl p-4" data-testid="ai-mcp-who">
          <h3 class="text-[11px] font-bold uppercase tracking-[0.1em] text-gray-500 m-0 mb-3">{{ labels.who_heading }}</h3>
          <ul class="flex flex-col gap-2.5 m-0 p-0 list-none">
            <li
              v-for="(line, index) in data.who_can_use"
              :key="index"
              class="flex items-start gap-2 text-[12px] leading-snug text-gray-700 m-0"
            >
              <component :is="WHO_ICONS[index] ?? UserCheck" :size="14" :stroke-width="1.75" class="flex-shrink-0 mt-[1px] text-indigo-500" aria-hidden="true" />
              {{ line }}
            </li>
          </ul>
        </div>
      </section>

    </div>
  </main>
</template>
