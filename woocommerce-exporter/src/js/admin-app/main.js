import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import { router } from './router'
import { useSettingsStore } from './stores/settings'
import { initialViewFromSearch } from './lib/tabRoute'
import '../../css/app.css'

const app = createApp(App)
app.use(createPinia())
app.use(router)

// With memory history the router never reads from the URL automatically.
// Derive the initial route from ?tab= so that page refreshes land on the right
// view. The tab value may include a sub-path (e.g. custom_meta_fields/product);
// tabToRoute is what decides whether that sub-path is one the app will accept.
const urlParams = new URLSearchParams(window.location.search)
// initialViewFromSearch always resolves a route, so the wseAdminData.initialRoute fallback that
// used to sit here could never fire. PHP still sends that value, and it does NOT apply this
// module's slug rule to the sub-path — do not revive it as a fallback without one.
const { path: initialPath, settingsCategory } = initialViewFromSearch(window.location.search)
// ?type= preselects an export type in Quick Export. It is a one-shot hand-off:
// drop it from the address bar so a later refresh or in-app navigation never
// forces that type again.
const typeParam = initialPath === '/quick-export' ? urlParams.get('type') : null
if (typeParam) {
  const url = new URL(window.location.href)
  url.searchParams.delete('type')
  history.replaceState(null, '', url.pathname + url.search + url.hash)
}
const initialRoute = typeParam
  ? { path: initialPath, query: { type: typeParam } }
  : initialPath

// Restore the active settings category from ?tab=settings/{category} before mount.
// An empty result means the tab named no usable category, so the store keeps its default.
if (settingsCategory) {
  useSettingsStore().activeCategory = settingsCategory
}

router.replace(initialRoute).finally(() => {
  app.mount('#wse-admin-app')
})
