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
      Change one, change all three. The SPA is hardcoded English throughout, so
      this copy is deliberately not translated.
    -->
    <span class="m-0 text-xs text-amber-800 leading-normal">
      Store Exporter is currently in debugging mode (<code>WOO_CE_DEBUG</code>). Exports are
      refused and no export files are generated until debugging mode is de-activated.
    </span>
  </div>
</template>
