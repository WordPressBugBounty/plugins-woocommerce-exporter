<script setup>
import { ArrowRight, Lock } from 'lucide-vue-next'
import { useRouter } from 'vue-router'
import { useQuickExportStore } from '../stores/quickExport'
import { useUpsellStore } from '../stores/upsell'
import { TYPE_VISUALS, DEFAULT_VISUAL } from '../exportTypeVisuals'

defineProps({
  items: { type: Array, default: () => [] }
})

const router = useRouter()
const quickExport = useQuickExportStore()
const upsell = useUpsellStore()
const visual = (slug) => TYPE_VISUALS[slug] ?? DEFAULT_VISUAL

async function onCardClick (item) {
  if (item.premium) {
    upsell.open({ label: item.label, description: item.description, url: item.upsell_url })
    return
  }
  await quickExport.loadConfig()
  quickExport.selectType(item.slug)
  router.push('/quick-export')
}
</script>

<template>
  <div class="grid grid-cols-4 gap-3">
    <a
      v-for="item in items"
      :key="item.slug"
      :href="item.premium ? item.upsell_url : '#/quick-export'"
      :target="item.premium ? '_blank' : undefined"
      :rel="item.premium ? 'noopener' : undefined"
      class="relative flex flex-col gap-[10px] p-4 bg-white border border-gray-200 rounded-[10px] no-underline transition-[border-color,box-shadow] duration-[120ms]"
      :class="item.premium ? 'hover:border-amber-300 hover:shadow-[0_0_0_3px_#FEF3C7]' : 'hover:border-indigo-300 hover:shadow-[0_0_0_3px_#EFF1FF]'"
      @click.prevent="onCardClick(item)"
    >
      <span v-if="item.premium" class="absolute top-3 right-3 w-5 h-5 rounded-full bg-amber-100 flex items-center justify-center">
        <Lock :size="11" :stroke-width="2.5" color="#B45309" />
      </span>
      <span
        class="w-11 h-11 rounded-[10px] flex items-center justify-center flex-shrink-0"
        :class="item.premium && 'opacity-70'"
        :style="{ background: visual(item.slug).bgColor }"
      >
        <component
          :is="visual(item.slug).icon"
          :size="24"
          :color="visual(item.slug).iconColor"
          :stroke-width="1.75"
        />
      </span>

      <h3 class="text-[13px] font-semibold text-gray-900 m-0 leading-[1.3]">{{ item.label }}</h3>
      <p class="text-xs text-gray-500 m-0 leading-[1.5] flex-1 line-clamp-3">{{ item.description }}</p>

      <span v-if="item.premium" class="inline-flex items-center gap-[4px] text-xs font-semibold text-amber-700 mt-auto">
        <Lock :size="11" :stroke-width="2.5" /> Available in Deluxe
      </span>
      <span v-else class="inline-flex items-center gap-[3px] text-xs font-medium text-indigo-600 mt-auto">
        Export <ArrowRight :size="12" :stroke-width="2" />
      </span>
    </a>
  </div>
</template>
