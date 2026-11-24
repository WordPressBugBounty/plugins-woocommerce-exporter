<script setup>
/**
 * DebugModeBanner — persistent warning shown while debugging mode is on.
 *
 * Issue #89: the PHP notice is static, so it could not react to the Advanced
 * toggle without a page reload, and it duplicated on SPA tabs. The PHP notice
 * is now suppressed on SPA tabs and this banner replaces it. It reads
 * `debugModeActive` (seeded from the bootstrapped WOO_CE_DEBUG constant and
 * updated only after a settings request resolves — see
 * resolveDebugModeActive() in stores/settings.js), never `settings.debug_mode`,
 * which is the Advanced toggle's own value and can disagree with the constant.
 */
import { storeToRefs } from 'pinia'
import { AlertTriangle } from 'lucide-vue-next'
import I18nText from './ui/I18nText.vue'
import { __ } from '@wordpress/i18n'
import { useSettingsStore } from '../stores/settings'

const { debugModeActive } = storeToRefs(useSettingsStore())
</script>

<template>
  <div
    v-if="debugModeActive"
    class="wse-debug-banner flex items-start gap-3 py-3 px-5 bg-amber-50 border-b border-amber-200"
    role="status"
  >
    <AlertTriangle
      :size="18"
      :stroke-width="1.75"
      aria-hidden="true"
      class="flex-shrink-0 text-amber-700"
    />
    <!--
      COPY SYNC — this sentence also lives in the legacy admin notice
      (includes/functions.php, woo_ce_admin_fail_notices) and in the AJAX
      refusal payload (includes/functions.php, woo_ce_send_debug_mode_error).
      Change one, change all three. The msgid is the same in all three places.
    -->
    <!-- translators: %s: the PHP constant name, WOO_CE_DEBUG -->
    <span class="m-0 text-xs text-amber-800 leading-normal">
      <I18nText :text="__('Store Exporter is currently in debugging mode (%s). Exports are refused and no export files are generated until debugging mode is de-activated.', 'woocommerce-exporter')">
        <template #1><code>WOO_CE_DEBUG</code></template>
      </I18nText>
    </span>
  </div>
</template>
