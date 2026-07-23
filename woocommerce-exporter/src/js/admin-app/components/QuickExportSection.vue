<script setup>
import { useRouter } from 'vue-router'
import {
  Zap,
  Package,
  ShoppingBag,
  Users,
  LayoutGrid,
  Tag,
  RefreshCw,
  MoreHorizontal
} from 'lucide-vue-next'
import { useQuickExportStore } from '../stores/quickExport'
import ViewAllButton from './ui/ViewAllButton.vue'

defineProps({
  exportUrl: { type: String, default: '' }
})

const router = useRouter()
const store = useQuickExportStore()

const exportTypes = [
  { slug: 'product',      label: 'Products',      icon: Package,    iconColor: '#6C7BFF', bgColor: '#EFF1FF', selected: true },
  { slug: 'order',        label: 'Orders',         icon: ShoppingBag,iconColor: '#F97316', bgColor: '#FFF7ED', selected: false },
  { slug: 'customer',     label: 'Customers',      icon: Users,      iconColor: '#0D9488', bgColor: '#F0FDFA', selected: false },
  { slug: 'category',     label: 'Categories',     icon: LayoutGrid, iconColor: '#16A34A', bgColor: '#F0FDF4', selected: false },
  { slug: 'coupon',       label: 'Coupons',        icon: Tag,        iconColor: '#DC2626', bgColor: '#FFF1F2', selected: false },
  { slug: 'subscription', label: 'Subscriptions',  icon: RefreshCw,  iconColor: '#0891B2', bgColor: '#ECFEFF', selected: false }
]

function goToQuickExport (slug) {
  store.reset()
  store.selectType(slug)
  router.push('/quick-export')
}
</script>

<template>
  <section class="wse-card p-5">
    <div class="flex items-center justify-between gap-3 mb-[18px]">
      <div>
        <div class="flex items-center gap-1.5 mb-[3px]">
          <Zap :size="15" color="#F59E0B" fill="#F59E0B" class="flex-shrink-0" />
          <h2 class="text-[14px] font-semibold text-gray-900 m-0 leading-none">Quick Export</h2>
        </div>
        <p class="text-[12.5px] text-gray-500 m-0 leading-relaxed">Export any data type from your store in just a few clicks.</p>
      </div>
      <ViewAllButton to="/export-items" label="View All Export Items" />
    </div>

    <div class="grid grid-cols-7 gap-2">
      <a
        v-for="type in exportTypes"
        :key="type.slug"
        href="#"
        class="flex flex-col items-center justify-center gap-2 py-3.5 px-1.5 pb-3 border-[1.5px] border-gray-200 rounded-[10px] bg-white no-underline transition-all cursor-pointer hover:border-indigo-300 hover:shadow-[0_0_0_3px_#EFF1FF]"
        :class="type.selected && 'border-2 border-indigo-600 hover:shadow-[0_0_0_3px_#E1E5FF]'"
        @click.prevent="goToQuickExport(type.slug)"
      >
        <span
          class="w-[52px] h-[52px] rounded-[10px] flex items-center justify-center"
          :style="{ background: type.bgColor }"
        >
          <component
            :is="type.icon"
            :size="26"
            :color="type.iconColor"
            :stroke-width="1.75"
          />
        </span>
        <span
          class="text-xs font-semibold text-center leading-tight"
          :style="type.selected ? { color: type.iconColor } : { color: '#374151' }"
        >{{ type.label }}</span>
      </a>

      <!-- More tile -->
      <a
        class="flex flex-col items-center gap-2 py-3.5 px-1.5 pb-3 border-[1.5px] border-gray-200 rounded-[10px] bg-white no-underline transition-all cursor-pointer hover:border-indigo-300 hover:shadow-[0_0_0_3px_#EFF1FF]"
        href="#"
        @click.prevent="router.push('/export-items')"
      >
        <span class="w-[52px] h-[52px] rounded-[10px] bg-gray-50 flex items-center justify-center">
          <MoreHorizontal :size="22" color="#9CA3AF" :stroke-width="1.75" />
        </span>
        <span class="text-xs font-semibold text-gray-500 text-center leading-tight">More</span>
        <span class="text-[11px] text-gray-400 font-medium -mt-1">+8</span>
      </a>
    </div>
  </section>
</template>
