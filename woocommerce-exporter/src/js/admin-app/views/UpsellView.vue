<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { Lock, Check } from 'lucide-vue-next'
import PageShell from '../components/ui/PageShell.vue'
import PageHeader from '../components/ui/PageHeader.vue'
import UpsellPreview from '../components/UpsellPreview.vue'

const route = useRoute()

// All copy, CTA targeting and the preview facsimile come from the route's
// meta.upsell block (see router.js). This single view backs every Deluxe-only
// nav slot.
const upsell = computed(() => route.meta.upsell ?? {})

const UPGRADE_BASE = 'https://visser.com.au/plugins/woocommerce-export/'

// Mirrors trunk's woo_ce_upsell_link() tagging: utm_source=wse, a per-feature
// utm_medium, and the campaign that identifies which button was clicked.
function upgradeUrl (campaign) {
  const medium = upsell.value.utmMedium ?? 'spa'
  return `${UPGRADE_BASE}?utm_source=wse&utm_medium=${medium}&utm_campaign=${campaign}`
}

const upgradeNowUrl = computed(() => upgradeUrl('upsellupgradenowbutton'))
const tellMeMoreUrl = computed(() => upgradeUrl('upselltellmemorebutton'))
</script>

<template>
  <PageShell>
    <PageHeader :title="upsell.feature" :subtitle="upsell.lead">
      <template #actions>
        <span class="inline-flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-[0.06em] text-amber-700 bg-amber-50 border border-amber-200 rounded-full px-2.5 py-1">
          <Lock :size="12" :stroke-width="2.5" /> Deluxe
        </span>
      </template>
    </PageHeader>

    <div class="relative rounded-2xl border border-gray-200 overflow-hidden min-h-[560px] bg-gray-50">
      <!-- Facsimile of the Deluxe screen (decorative only). Shown fully clear
           on load, then blurs in sync with the overlay fade-in below. -->
      <div class="wse-upsell-preview-layer absolute inset-0 pointer-events-none select-none opacity-95" aria-hidden="true">
        <UpsellPreview v-if="upsell.preview" :feature="upsell.preview" />
      </div>

      <!-- Scrim + lock card. Kept light so the preview stays clearly visible;
           the lock card is opaque, so it remains crisp regardless. The overlay
           fades in after a short delay so the Deluxe preview is briefly shown
           before the upsell settles over it. -->
      <div class="wse-upsell-overlay absolute inset-0 grid place-items-center p-6 bg-gradient-to-b from-white/40 via-white/25 to-white/40">
        <div class="max-w-[480px] w-full text-center bg-white border border-gray-200 rounded-2xl shadow-lg px-8 py-10">
          <div class="w-14 h-14 rounded-2xl bg-amber-50 border border-amber-200 text-amber-600 grid place-items-center mx-auto mb-4">
            <Lock :size="26" :stroke-width="2" />
          </div>

          <h2 class="text-[19px] font-extrabold tracking-[-0.01em] text-gray-900">
            {{ upsell.feature }} is a Deluxe feature
          </h2>
          <p class="text-[13.5px] text-gray-600 mt-2 mx-auto max-w-[44ch]">{{ upsell.lead }}</p>

          <ul class="flex flex-col gap-2.5 text-left mx-auto mt-5 mb-6 max-w-[340px] list-none p-0">
            <li v-for="(b, i) in upsell.bullets" :key="i" class="flex items-center gap-2.5 text-[13px] text-gray-700 m-0">
              <Check :size="16" :stroke-width="2.5" class="flex-shrink-0 text-amber-600" />
              <span>{{ b }}</span>
            </li>
          </ul>

          <div class="flex items-center justify-center gap-2.5 flex-wrap">
            <a
              :href="upgradeNowUrl"
              target="_blank"
              rel="noopener"
              class="inline-block bg-amber-600 hover:bg-amber-700 !text-white text-[13.5px] font-semibold rounded-lg px-5 py-2.5 no-underline transition-colors"
            >Upgrade Now</a>
            <a
              :href="tellMeMoreUrl"
              target="_blank"
              rel="noopener"
              class="inline-block bg-transparent hover:bg-amber-50 text-amber-700 border border-amber-200 text-[13.5px] font-semibold rounded-lg px-5 py-2.5 no-underline transition-colors"
            >Tell me more</a>
          </div>

          <p class="text-[11.5px] text-gray-500 mt-3.5">30-day money-back guarantee · Priority support</p>
        </div>
      </div>
    </div>
  </PageShell>
</template>

<style scoped>
/* On load the Deluxe preview is fully clear and visible. After a 1s beat the
   overlay fades in and the preview blurs — together — so free users first see
   the feature, then watch it "lock" behind the upsell. */
.wse-upsell-preview-layer {
  animation: wse-upsell-preview-blur 0.5s ease both;
  animation-delay: 1s;
}

.wse-upsell-overlay {
  animation: wse-upsell-fade-in 0.5s ease both;
  animation-delay: 1s;
}

@keyframes wse-upsell-preview-blur {
  from {
    filter: blur(0);
  }
  to {
    filter: blur(2.5px);
  }
}

@keyframes wse-upsell-fade-in {
  from {
    opacity: 0;
    transform: translateY(8px);
  }
  to {
    opacity: 1;
    transform: none;
  }
}

@media (prefers-reduced-motion: reduce) {
  .wse-upsell-preview-layer {
    animation: none;
    filter: blur(2.5px);
  }
  .wse-upsell-overlay {
    animation: none;
  }
}
</style>
