<script setup>
import { computed, onMounted } from 'vue'
import { storeToRefs } from 'pinia'
import { useRouter } from 'vue-router'
import {
  AlertCircle,
  ChevronRight,
  Info,
  Layers,
  Plus
} from 'lucide-vue-next'
import { BaseButton } from '../components/ui/index'
import { useCustomMetaFieldsStore } from '../stores/customMetaFields'
import { TYPE_VISUALS, DEFAULT_VISUAL } from '../exportTypeVisuals'
import PageActions from '../components/PageActions.vue'

const store = useCustomMetaFieldsStore()
const { types, isLoadingList, listError } = storeToRefs(store)
const router = useRouter()

onMounted(store.fetchTypes)

const configuredTypes = computed(() => types.value.filter(t => t.field_count > 0))

function visualFor (typeSlug) {
  return TYPE_VISUALS[typeSlug] ?? DEFAULT_VISUAL
}

// Per-type badge colour tokens — mirrors TYPE_VISUALS icon colours.
const BADGE_STYLES = {
  product:           { bg: '#EFF1FF', text: '#6C7BFF' },
  category:          { bg: '#F0FDF4', text: '#16A34A' },
  tag:               { bg: '#F5F3FF', text: '#7C3AED' },
  brand:             { bg: '#FFFBEB', text: '#B45309' },
  order:             { bg: '#FFF7ED', text: '#EA580C' },
  customer:          { bg: '#F0FDFA', text: '#0D9488' },
  user:              { bg: '#EFF1FF', text: '#7986FF' },
  review:            { bg: '#FFFBEB', text: '#F59E0B' },
  coupon:            { bg: '#FFF1F2', text: '#DC2626' },
  subscription:      { bg: '#ECFEFF', text: '#0891B2' },
  yith_subscription: { bg: '#F0F9FF', text: '#0284C7' },
  product_vendor:    { bg: '#ECFDF5', text: '#059669' },
  wcv_product:       { bg: '#D1FAE5', text: '#10B981' },
  commission:        { bg: '#EFF1FF', text: '#7986FF' },
  shipping_class:    { bg: '#FFF7ED', text: '#F97316' },
  ticket:            { bg: '#FDF2F8', text: '#EC4899' },
  booking:           { bg: '#F5F3FF', text: '#8B5CF6' },
  attribute:         { bg: '#F0F9FF', text: '#0EA5E9' },
  downloadable_file: { bg: '#F0FDFA', text: '#0D9488' }
}

function badgeStyle (typeSlug) {
  return BADGE_STYLES[typeSlug] ?? { bg: '#F3F4F6', text: '#6B7280' }
}

function goToType (typeSlug) {
  router.push(`/custom-meta-fields/${typeSlug}`)
}

const firstTypeSlug = computed(() => {
  const unconfigured = types.value.find(t => t.field_count === 0)
  return unconfigured?.type ?? types.value[0]?.type ?? 'product'
})

function addCustomField () {
  router.push(`/custom-meta-fields/${firstTypeSlug.value}`)
}
</script>

<template>
  <main class="wse-main" data-testid="app-main">

    <!-- Page header -->
    <div class="flex items-start justify-between gap-4 mb-6">
      <div>
        <h1 class="m-0 text-[24px] font-bold text-[#111827] leading-snug">Custom Meta Fields</h1>
        <p class="m-0 mt-1 text-[14px] text-[#6B7280]">
          Manage and reuse custom meta fields across different export types.
        </p>
      </div>
      <div class="flex items-center gap-2">
        <BaseButton @click="addCustomField">
          <template #icon><Plus :size="15" :stroke-width="2.25" /></template>
          Add Custom Field
        </BaseButton>
        <PageActions />
      </div>
    </div>

    <!-- Loading -->
    <div v-if="isLoadingList" class="flex items-center gap-2.5 text-[13px] text-gray-500 py-10 justify-center">
      <span class="wse-spinner" />
      Loading&hellip;
    </div>

    <!-- Error -->
    <div v-else-if="listError" class="flex items-center gap-2 text-[13px] text-red-600 py-8 justify-center">
      <AlertCircle :size="16" :stroke-width="1.75" />
      {{ listError }}
    </div>

    <!-- Export Types card -->
    <template v-else>
      <section class="bg-white border border-[#E5E7EB] rounded-[10px] overflow-hidden">

        <!-- Card header -->
        <div class="px-6 py-5 border-b border-[#F3F4F6]">
          <h2 class="m-0 text-[15px] font-semibold text-[#111827]">Export Types</h2>
          <p class="m-0 mt-1 text-[13px] text-[#6B7280]">
            Select an export type to view and manage its custom meta fields.
          </p>
        </div>

        <!-- Empty state body -->
        <div v-if="configuredTypes.length === 0" class="wse-cmf-empty-body">
          <!-- Illustration -->
          <svg class="wse-cmf-empty-illustration" viewBox="0 0 220 190" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <!-- Drop shadow ellipse -->
            <ellipse cx="110" cy="172" rx="58" ry="10" fill="rgba(99,102,241,0.10)" />

            <!-- Folder body -->
            <rect x="44" y="95" width="132" height="76" rx="8" fill="#DDD6FE" />
            <!-- Folder tab -->
            <path d="M44 103 C44 98.5817 47.5817 95 52 95 H86 L94 103 H44Z" fill="#C4B5FD" />

            <!-- Document 1 (left, slightly rotated) -->
            <g transform="rotate(-8, 82, 80)">
              <rect x="60" y="56" width="52" height="64" rx="5" fill="#EFF1FF" stroke="#C8CFFF" stroke-width="1.5" />
              <rect x="68" y="70" width="36" height="3" rx="1.5" fill="#C8CFFF" />
              <rect x="68" y="78" width="28" height="3" rx="1.5" fill="#C8CFFF" />
              <rect x="68" y="86" width="32" height="3" rx="1.5" fill="#C8CFFF" />
            </g>

            <!-- Document 2 (right, slightly rotated) -->
            <g transform="rotate(7, 138, 80)">
              <rect x="108" y="52" width="52" height="64" rx="5" fill="#E1E5FF" stroke="#A7B0FF" stroke-width="1.5" />
              <rect x="116" y="66" width="36" height="3" rx="1.5" fill="#A7B0FF" />
              <rect x="116" y="74" width="24" height="3" rx="1.5" fill="#A7B0FF" />
              <rect x="116" y="82" width="30" height="3" rx="1.5" fill="#A7B0FF" />
            </g>

            <!-- Badge circle on folder -->
            <circle cx="110" cy="133" r="20" fill="#6C7BFF" />
            <!-- Tag icon inside badge -->
            <path d="M103 129 L103 137 L108.5 142.5 C109.3 143.3 110.7 143.3 111.5 142.5 L116.5 137.5 C117.3 136.7 117.3 135.3 116.5 134.5 L111 129 L103 129Z" fill="white" stroke="white" stroke-width="0.5" stroke-linejoin="round" />
            <circle cx="106.5" cy="132.5" r="1.5" fill="#6C7BFF" />

            <!-- Sparkle top-left -->
            <path d="M52 52 L53.5 56 L57.5 57.5 L53.5 59 L52 63 L50.5 59 L46.5 57.5 L50.5 56 Z" fill="#C4B5FD" />
            <!-- Sparkle top-right -->
            <path d="M168 44 L169 47 L172 48 L169 49 L168 52 L167 49 L164 48 L167 47 Z" fill="#A7B0FF" />
            <!-- Small dot top-right -->
            <circle cx="158" cy="62" r="3" fill="#DDD6FE" />
            <!-- Small dot left -->
            <circle cx="48" cy="76" r="2.5" fill="#C8CFFF" />
            <!-- Tiny sparkle bottom-right -->
            <path d="M174 105 L175 108 L178 109 L175 110 L174 113 L173 110 L170 109 L173 108 Z" fill="#C4B5FD" />
          </svg>

          <h3 class="wse-cmf-empty-title">No custom meta fields yet</h3>
          <p class="wse-cmf-empty-desc">
            You haven't created any custom meta fields for any export type.<br>
            Add your first custom meta field to get started.
          </p>

          <BaseButton @click="addCustomField">
            <template #icon><Plus :size="16" :stroke-width="2.5" /></template>
            Add Custom Field
          </BaseButton>
        </div>

        <!-- Type rows (when fields exist) -->
        <div v-else>
          <button
            v-for="item in configuredTypes"
            :key="item.type"
            type="button"
            class="wse-cmf-row"
            @click="goToType(item.type)"
          >
            <!-- Icon -->
            <div
              class="w-10 h-10 rounded-[8px] flex items-center justify-center flex-shrink-0"
              :style="{ backgroundColor: visualFor(item.type).bgColor }"
            >
              <component
                :is="visualFor(item.type).icon"
                :size="18"
                :color="visualFor(item.type).iconColor"
                :stroke-width="1.75"
              />
            </div>

            <!-- Label + description -->
            <div class="flex-1 min-w-0 text-left">
              <div class="text-[14px] font-semibold text-[#111827]">{{ item.label }}</div>
              <p class="m-0 mt-0.5 text-[13px] text-[#6B7280]">{{ item.description }}</p>
            </div>

            <!-- Field count badge -->
            <div
              class="wse-cmf-badge flex-shrink-0"
              :style="{ backgroundColor: badgeStyle(item.type).bg, color: badgeStyle(item.type).text }"
            >
              {{ item.field_count }} {{ item.field_count === 1 ? 'field' : 'fields' }}
            </div>

            <!-- Chevron -->
            <ChevronRight :size="16" color="#9CA3AF" :stroke-width="1.75" class="flex-shrink-0" />
          </button>
        </div>

        <!-- Info footer -->
        <div class="flex items-center gap-2 px-6 py-4 border-t border-[#F3F4F6]">
          <Info :size="14" color="#9CA3AF" :stroke-width="1.75" class="flex-shrink-0" />
          <span class="text-[12px] text-[#6B7280]">
            Custom meta fields are key-value pairs that you can include in your exports.
          </span>
        </div>

      </section>
    </template>

  </main>
</template>

<style lang="scss" scoped src="./CustomMetaFieldsView.scss"></style>
