<script setup>
import { ArrowRight, Lock } from 'lucide-vue-next'
import { useRouter } from 'vue-router'
import { useQuickExportStore } from '../stores/quickExport'
import { useUpsellStore } from '../stores/upsell'
import { TYPE_VISUALS, DEFAULT_VISUAL } from '../exportTypeVisuals'
import { computed, ref } from 'vue'

const router = useRouter()
const quickExport = useQuickExportStore()
const upsell = useUpsellStore()

async function onRowAction (item) {
  if (item.premium) {
    upsell.open({ label: item.label, description: item.description, url: item.upsell_url })
    return
  }
  await quickExport.loadConfig()
  quickExport.selectType(item.slug)
  router.push('/quick-export')
}

const props = defineProps({
  items: { type: Array, default: () => [] },
  perPage: { type: Number, default: 10 }
})

const currentPage = ref(1)

const totalPages = computed(() => Math.max(1, Math.ceil(props.items.length / props.perPage)))

const pagedItems = computed(() => {
  const start = (currentPage.value - 1) * props.perPage
  return props.items.slice(start, start + props.perPage)
})

const startIndex = computed(() => (currentPage.value - 1) * props.perPage + 1)
const endIndex = computed(() => Math.min(currentPage.value * props.perPage, props.items.length))

const pages = computed(() => {
  const result = []
  for (let i = 1; i <= totalPages.value; i++) result.push(i)
  return result
})

const visual = (slug) => TYPE_VISUALS[slug] ?? DEFAULT_VISUAL

const formatRecords = (n) => (n > 0 ? n.toLocaleString() : '—')
</script>

<template>
  <div class="flex flex-col gap-3">
    <table class="w-full border-collapse bg-white border border-gray-200 rounded-[10px] overflow-hidden text-[13px]">
      <thead>
        <tr>
          <th class="px-3.5 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-[0.05em] bg-gray-50 border-b border-gray-200 whitespace-nowrap">Name</th>
          <th class="px-3.5 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-[0.05em] bg-gray-50 border-b border-gray-200 whitespace-nowrap">Category</th>
          <th class="px-3.5 py-2.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-[0.05em] bg-gray-50 border-b border-gray-200 whitespace-nowrap w-[35%]">Description</th>
          <th class="px-3.5 py-2.5 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-[0.05em] bg-gray-50 border-b border-gray-200 whitespace-nowrap">Records</th>
          <th class="px-3.5 py-2.5 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-[0.05em] bg-gray-50 border-b border-gray-200 whitespace-nowrap">Last Export</th>
          <th class="px-3.5 py-2.5 bg-gray-50 border-b border-gray-200 w-[80px]" />
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="item in pagedItems"
          :key="item.slug"
          :data-testid="`export-type-row-${item.slug}`"
          class="border-b border-gray-100 last:border-b-0 transition-colors hover:bg-[#FAFAFA]"
        >
          <td class="px-3.5 py-2.5 align-middle text-gray-700">
            <span class="flex items-center gap-2.5 whitespace-nowrap">
              <span
                class="w-7 h-7 rounded-[7px] flex items-center justify-center flex-shrink-0"
                :style="{ background: visual(item.slug).bgColor }"
              >
                <component
                  :is="visual(item.slug).icon"
                  :size="14"
                  :color="visual(item.slug).iconColor"
                  :stroke-width="1.75"
                />
              </span>
              <span class="font-semibold text-gray-900">{{ item.label }}</span>
            </span>
          </td>
          <td class="px-3.5 py-2.5 align-middle text-gray-700">
            <span class="inline-block py-[2px] px-2 rounded-[20px] bg-gray-100 text-gray-600 text-[11.5px] font-medium whitespace-nowrap">{{ item.category }}</span>
          </td>
          <td class="px-3.5 py-2.5 align-middle text-gray-500 text-[12.5px] max-w-0 overflow-hidden text-ellipsis whitespace-nowrap">{{ item.description }}</td>
          <td class="px-3.5 py-2.5 align-middle text-right text-gray-500 text-[12.5px]">{{ item.premium ? '—' : formatRecords(item.records) }}</td>
          <td class="px-3.5 py-2.5 align-middle text-right text-gray-500 text-[12.5px]">{{ item.premium ? '—' : (item.last_export ?? '—') }}</td>
          <td class="px-3.5 py-2.5 align-middle text-gray-700">
            <a
              v-if="item.premium"
              :href="item.upsell_url"
              target="_blank"
              rel="noopener"
              class="inline-flex items-center gap-[4px] text-xs font-semibold text-amber-700 no-underline hover:underline"
              @click.prevent="onRowAction(item)"
            >
              <Lock :size="11" :stroke-width="2.5" /> Deluxe
            </a>
            <a
              v-else
              href="#/quick-export"
              class="inline-flex items-center gap-[3px] text-xs font-medium text-indigo-600 no-underline hover:underline hover:text-indigo-700"
              @click.prevent="onRowAction(item)"
            >
              Export <ArrowRight :size="11" :stroke-width="2" />
            </a>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Pagination -->
    <div v-if="items.length > 0" class="flex items-center justify-between py-[2px] text-[12.5px] text-gray-500">
      <span>Showing {{ startIndex }} to {{ endIndex }} of {{ items.length }} items</span>

      <div class="flex items-center gap-1.5">
        <span class="text-xs text-gray-400 mr-1.5">{{ perPage }} per page</span>
        <button
          v-for="page in pages"
          :key="page"
          class="w-7 h-7 border-[1.5px] rounded-[6px] bg-white text-xs font-medium cursor-pointer transition-[border-color,background] duration-[120ms]"
          :class="page === currentPage
            ? 'bg-indigo-600 border-indigo-600 text-white hover:bg-indigo-700 hover:border-indigo-700'
            : 'border-gray-200 text-gray-700 hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-600'"
          @click="currentPage = page"
        >{{ page }}</button>
      </div>
    </div>
  </div>
</template>
