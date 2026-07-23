<script setup>
import { storeToRefs } from 'pinia'
import { Lock, Check } from 'lucide-vue-next'
import BaseModal from './ui/BaseModal.vue'
import { useUpsellStore } from '../stores/upsell'

const store = useUpsellStore()
const { isOpen, feature, description, url } = storeToRefs(store)

// Generic Deluxe value props shown alongside the specific export type.
const BENEFITS = [
  'Unlock every export type — customers, coupons, subscriptions, bookings & more',
  'Export to Excel (XLS / XLSX) and XML, not just CSV',
  'Schedule automatic exports and save reusable export templates',
  'Access hundreds more fields with advanced filtering',
]
</script>

<template>
  <BaseModal v-model="isOpen" size="sm">
    <div class="text-center">
      <div class="w-14 h-14 rounded-2xl bg-amber-50 border border-amber-200 text-amber-600 grid place-items-center mx-auto mb-4">
        <Lock :size="26" :stroke-width="2" />
      </div>
      <h2 class="m-0! text-[18px] font-extrabold tracking-[-0.01em] text-gray-900">
        Exporting {{ feature }} is a Deluxe feature
      </h2>
      <p v-if="description" class="text-[13px] text-gray-600 mt-2 mx-auto max-w-[42ch]">{{ description }}</p>

      <p class="text-[11px] font-bold uppercase tracking-[0.06em] text-amber-700 mt-5 mb-2.5">Store Exporter Deluxe unlocks</p>
      <ul class="flex flex-col gap-2.5 text-left mx-auto max-w-[360px] list-none p-0 m-0">
        <li v-for="(b, i) in BENEFITS" :key="i" class="flex items-start gap-2.5 text-[13px] text-gray-700 m-0">
          <Check :size="16" :stroke-width="2.5" class="flex-shrink-0 mt-[2px] text-amber-600" />
          <span>{{ b }}</span>
        </li>
      </ul>

      <p class="text-[11.5px] text-gray-500 mt-5">30-day money-back guarantee · Priority support</p>
    </div>

    <template #footer>
      <button
        type="button"
        class="bg-transparent hover:bg-gray-100 text-gray-600 text-[13.5px] font-semibold rounded-lg px-4 py-2.5 border-0 cursor-pointer"
        @click="store.close()"
      >Maybe later</button>
      <a
        :href="url"
        target="_blank"
        rel="noopener"
        class="inline-block bg-amber-600 hover:bg-amber-700 !text-white text-[13.5px] font-semibold rounded-lg px-5 py-2.5 no-underline transition-colors"
        @click="store.close()"
      >Upgrade to Deluxe</a>
    </template>
  </BaseModal>
</template>
