import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import { router } from './router'
import { useSettingsStore } from './stores/settings'
import '../../css/app.css'

const app = createApp(App)
app.use(createPinia())
app.use(router)

// With memory history the router never reads from the URL automatically.
// Derive the initial route from ?tab= so that page refreshes land on the right
// view. The tab value may include a sub-path (e.g. custom_meta_fields/product).
const tabParam = new URLSearchParams(window.location.search).get('tab') || ''
const initialRoute = tabToRoute(tabParam) || window.wseAdminData?.initialRoute || '/'

// Restore the active settings category from ?tab=settings/{category} before mount.
const settingsCategoryMatch = tabParam.match(/^settings\/(.+)$/)
if (settingsCategoryMatch) {
  useSettingsStore().activeCategory = settingsCategoryMatch[1]
}

router.replace(initialRoute).finally(() => {
  app.mount('#wse-admin-app')
})

/**
 * Convert a ?tab= value back to a Vue Router path.
 *
 * Deluxe-only tabs (scheduled_export, export_template, archive, audit_log)
 * resolve to their in-app upsell route in the free plugin.
 *
 * @param {string} tab - e.g. 'custom_meta_fields/product'
 * @returns {string} Vue route path, e.g. '/custom-meta-fields/product'
 */
function tabToRoute (tab) {
  const baseRoutes = {
    overview: '/',
    export_items: '/export-items',
    quick_export: '/quick-export',
    scheduled_export: '/scheduled-exports',
    export_template: '/export-templates',
    archive: '/archives',
    settings: '/settings',
    tools: '/tools',
    custom_meta_fields: '/custom-meta-fields',
    audit_log: '/audit-log',
    ai_mcp: '/ai-mcp',
  }
  const slash = tab.indexOf('/')
  if (slash === -1) return baseRoutes[tab] ?? '/'
  const base = tab.slice(0, slash)
  const sub = tab.slice(slash) // leading slash included
  const baseRoute = baseRoutes[base]
  if (!baseRoute) return '/'
  // settings/{category} encodes the active section, not a distinct route — route to /settings only.
  if (base === 'settings') return baseRoute
  // Custom Meta Fields is the only free route with a real sub-path (/:type).
  if (base === 'custom_meta_fields') return baseRoute + sub
  // Deluxe-only bases have no sub-routes in the free plugin — land on the upsell.
  return baseRoute
}
