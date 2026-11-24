<script setup>
import { computed } from 'vue'
import { Check } from 'lucide-vue-next'
import { __ } from '@wordpress/i18n'
import { upsellBenefits } from '../../lib/upsellBenefits'

const props = defineProps({
  url: { type: String, required: true }
})

// Only follow http(s) links, so a bad value can never become a javascript: URL.
const safeUrl = computed(() => (/^https?:\/\//i.test(props.url) ? props.url : '#'))

defineEmits(['dismiss'])

const BENEFITS = upsellBenefits()
</script>

<template>
  <section
    class="wse-qe-upgrade-prompt w-full bg-amber-50 border border-amber-200 rounded-xl px-5 pt-4 pb-4 text-left"
    aria-labelledby="wse-qe-upgrade-prompt-title"
  >
    <h3 id="wse-qe-upgrade-prompt-title" class="m-0! text-[15px] font-extrabold tracking-[-0.01em] text-gray-900">
      {{ __('Want more from your exports?', 'woocommerce-exporter') }}
    </h3>
    <p class="text-[11px] font-bold uppercase tracking-[0.06em] text-amber-700 mt-2 mb-2.5">{{ __('Store Exporter Deluxe unlocks', 'woocommerce-exporter') }}</p>
    <ul class="flex flex-col gap-2 list-none p-0 m-0">
      <li v-for="(b, i) in BENEFITS" :key="i" class="flex items-start gap-2.5 text-[12.5px] text-gray-700 m-0">
        <Check :size="15" :stroke-width="2.5" class="flex-shrink-0 mt-[2px] text-amber-600" />
        <span>{{ b }}</span>
      </li>
    </ul>
    <div class="flex gap-2 flex-wrap justify-end mt-4">
      <button
        type="button"
        class="bg-transparent hover:bg-amber-100 text-gray-600 text-[13px] font-semibold rounded-lg px-4 py-2 border-0 cursor-pointer"
        @click="$emit('dismiss')"
      >{{ __('Maybe later', 'woocommerce-exporter') }}</button>
      <a
        :href="safeUrl"
        target="_blank"
        rel="noopener"
        class="inline-block bg-amber-600 hover:bg-amber-700 !text-white text-[13px] font-semibold rounded-lg px-4 py-2 no-underline transition-colors"
      >{{ __('Upgrade to Deluxe', 'woocommerce-exporter') }} <span class="sr-only">{{ __('(opens in a new tab)', 'woocommerce-exporter') }}</span></a>
    </div>
  </section>
</template>
