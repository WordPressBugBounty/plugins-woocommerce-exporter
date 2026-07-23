<script setup>
import { onMounted, ref, computed } from 'vue'
import { storeToRefs } from 'pinia'
import {
  AlertCircle,
  Search,
  LayoutGrid,
  List,
  ChevronRight
} from 'lucide-vue-next'
import PageActions from '../components/PageActions.vue'
import { useRouter } from 'vue-router'
import { useExportItemsStore } from '../stores/exportItems'
import ExportItemsGrid from '../components/ExportItemsGrid.vue'
import ExportItemsList from '../components/ExportItemsList.vue'
import PageFooter from '../components/PageFooter.vue'

const store = useExportItemsStore()
const { data, isLoading, error } = storeToRefs(store)
const router = useRouter()

onMounted(store.fetchData)

const viewMode = ref('grid')
const searchQuery = ref('')
const sortOrder = ref('name-asc')

const sortOptions = [
  { value: 'name-asc',      label: 'Name (A–Z)' },
  { value: 'name-desc',     label: 'Name (Z–A)' },
  { value: 'records-desc',  label: 'Records (high–low)' },
  { value: 'records-asc',   label: 'Records (low–high)' }
]

const filteredItems = computed(() => {
  if (!data.value?.items) return []

  let list = [...data.value.items]

  const q = searchQuery.value.trim().toLowerCase()
  if (q) {
    list = list.filter(
      (item) =>
        item.label.toLowerCase().includes(q) ||
        item.category.toLowerCase().includes(q) ||
        item.description.toLowerCase().includes(q)
    )
  }

  switch (sortOrder.value) {
    case 'name-asc':     list.sort((a, b) => a.label.localeCompare(b.label)); break
    case 'name-desc':    list.sort((a, b) => b.label.localeCompare(a.label)); break
    case 'records-desc': list.sort((a, b) => b.records - a.records); break
    case 'records-asc':  list.sort((a, b) => a.records - b.records); break
  }

  // Free export types always come first; premium (Deluxe-only) sink below,
  // regardless of the chosen sort. Stable sort preserves the order above.
  list.sort((a, b) => (a.premium === b.premium ? 0 : a.premium ? 1 : -1))

  return list
})
</script>

<template>
  <main class="wse-main">
    <!-- Page header -->
    <div class="wse-header">
      <div>
        <nav class="wse-breadcrumb" aria-label="breadcrumb">
          <a class="wse-breadcrumb__link" href="#" @click.prevent="router.push('/')">Overview</a>
          <ChevronRight :size="13" class="wse-breadcrumb__sep" />
          <span class="wse-breadcrumb__current">All Export Items</span>
        </nav>
        <h1 class="wse-page-title">All Export Items</h1>
        <p class="wse-page-sub">Browse and export any data type from your WooCommerce store.</p>
      </div>
      <PageActions />
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="wse-loading">
      <span class="wse-spinner" />
      Loading export items&hellip;
    </div>

    <!-- Error -->
    <div v-else-if="error" class="wse-error">
      <AlertCircle :size="16" color="#DC2626" :stroke-width="1.75" />
      {{ error }}
    </div>

    <!-- Content -->
    <template v-else-if="data">
      <!-- Toolbar -->
      <div class="flex items-center gap-2.5">
        <div class="relative flex-1 max-w-[340px]">
          <Search :size="14" color="#9CA3AF" :stroke-width="1.75" class="absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" />
          <input
            v-model="searchQuery"
            type="search"
            class="w-full py-[7px] pr-2.5 pl-8 border-[1.5px] border-gray-200 rounded-[7px] bg-white text-[13px] text-gray-700 outline-none transition-colors focus:border-indigo-300 focus:shadow-[0_0_0_3px_#EFF1FF] placeholder:text-gray-400"
            placeholder="Search export items…"
          />
        </div>

        <div class="flex items-center gap-2 ml-auto">
          <label class="text-[12.5px] text-gray-500 whitespace-nowrap" for="ei-sort">Sort by:</label>
          <select id="ei-sort" v-model="sortOrder" class="py-[6px] pr-7 pl-2.5 border-[1.5px] border-gray-200 rounded-[7px] bg-white text-[12.5px] text-gray-700 outline-none cursor-pointer">
            <option v-for="opt in sortOptions" :key="opt.value" :value="opt.value">
              {{ opt.label }}
            </option>
          </select>

          <div class="flex border-[1.5px] border-gray-200 rounded-[7px] overflow-hidden" role="group" aria-label="View mode">
            <button
              class="flex items-center justify-center w-8 h-8 bg-white border-0 cursor-pointer transition-all border-r border-r-gray-200 hover:bg-gray-100 hover:text-gray-700"
              :class="viewMode === 'grid' ? 'bg-indigo-50 text-indigo-600' : 'text-gray-400'"
              aria-label="Grid view"
              @click="viewMode = 'grid'"
            >
              <LayoutGrid :size="15" :stroke-width="1.75" />
            </button>
            <button
              class="flex items-center justify-center w-8 h-8 bg-white border-0 cursor-pointer transition-all hover:bg-gray-100 hover:text-gray-700"
              :class="viewMode === 'list' ? 'bg-indigo-50 text-indigo-600' : 'text-gray-400'"
              aria-label="List view"
              @click="viewMode = 'list'"
            >
              <List :size="15" :stroke-width="1.75" />
            </button>
          </div>
        </div>
      </div>

      <!-- No results -->
      <p v-if="filteredItems.length === 0" class="wse-empty">
        No export items match your search.
      </p>

      <ExportItemsGrid v-else-if="viewMode === 'grid'" :items="filteredItems" />
      <ExportItemsList v-else :items="filteredItems" />

      <PageFooter :plugin="data.plugin" />
    </template>
  </main>
</template>

