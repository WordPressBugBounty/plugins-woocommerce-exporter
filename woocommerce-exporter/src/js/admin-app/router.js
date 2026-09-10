import { createRouter, createMemoryHistory } from 'vue-router'

// All routes use dynamic import() so each view is its own chunk.
// With ES module output (vite.config.js format: 'es') Rollup emits separate
// files that are fetched on demand. The initial JS payload only includes the
// entry point, vendor-vue, and the view the user lands on first.
//
// createMemoryHistory keeps Vue Router state in memory only — it never writes
// to the URL. The ?tab= query param (synced in afterEach below) is the sole
// URL persistence mechanism. PHP reads the base segment to enqueue the right
// asset bundle; main.js reads the full value to restore the initial route.
//
// Free-plugin scope: Scheduled Exports, Export Templates, Archives and the
// Audit Log are Deluxe-only. They keep their nav slot but resolve to a shared
// UpsellView instead of the feature — no store, no controller, no data. The
// route's meta.upsell object is the sole source of that view's copy and CTA.
const HELP_KB = 'https://visser.com.au/kb-categories/woocommerce-store-exporter-deluxe/'

const routes = [
  { path: '/', name: 'overview', meta: { helpUrl: HELP_KB }, component: () => import('./views/OverviewView.vue') },
  { path: '/export-items', name: 'export-items', meta: { helpUrl: HELP_KB }, component: () => import('./views/ExportItemsView.vue') },
  { path: '/quick-export', name: 'quick-export', meta: { helpUrl: HELP_KB }, component: () => import('./views/QuickExportView.vue') },
  { path: '/settings', name: 'settings', meta: { helpUrl: HELP_KB }, component: () => import('./views/SettingsView.vue') },
  { path: '/tools', name: 'tools', meta: { helpUrl: HELP_KB }, component: () => import('./views/ToolsView.vue') },
  { path: '/custom-meta-fields', name: 'custom-meta-fields', meta: { helpUrl: HELP_KB }, component: () => import('./views/CustomMetaFieldsView.vue') },
  { path: '/custom-meta-fields/:type', name: 'custom-meta-fields-form', meta: { helpUrl: HELP_KB }, component: () => import('./views/CustomMetaFieldsFormView.vue') },

  // Deluxe-only — rendered as upsells.
  {
    path: '/scheduled-exports',
    name: 'scheduled-exports',
    component: () => import('./views/UpsellView.vue'),
    meta: {
      helpUrl: HELP_KB,
      upsell: {
        feature: 'Scheduled Exports',
        lead: 'Set exports to run automatically and deliver themselves — no manual runs, no timeouts.',
        bullets: [
          'DST-safe daily, weekly, monthly & yearly schedules',
          'Deliver by email, SFTP/FTP, or webhook',
          'Powered by Action Scheduler — reliable at scale',
        ],
        utmMedium: 'scheduled_exports',
        preview: 'scheduled_exports',
      },
    },
  },
  {
    path: '/export-templates',
    name: 'export-templates',
    component: () => import('./views/UpsellView.vue'),
    meta: {
      helpUrl: HELP_KB,
      upsell: {
        feature: 'Export Templates',
        lead: 'Stop rebuilding the same field list every time. Save it as a template and pick it in one click.',
        bullets: [
          'Reusable field & filter presets per export type',
          'Shareable across manual and scheduled exports',
          'Ready-made feeds (Google Merchant, supplier)',
        ],
        utmMedium: 'export_templates',
        preview: 'export_templates',
      },
    },
  },
  {
    path: '/archives',
    name: 'archives',
    component: () => import('./views/UpsellView.vue'),
    meta: {
      helpUrl: HELP_KB,
      upsell: {
        feature: 'Archives',
        lead: 'Deluxe keeps a history of every export file so you can re-download past runs anytime.',
        bullets: [
          'Automatic retention of every generated file',
          'Re-download without re-running the export',
        ],
        utmMedium: 'archives',
        preview: 'archives',
      },
    },
  },
  {
    path: '/audit-log',
    name: 'audit-log',
    component: () => import('./views/UpsellView.vue'),
    meta: {
      helpUrl: HELP_KB,
      upsell: {
        feature: 'Audit Log',
        lead: 'Track every export and download for compliance and team accountability.',
        bullets: [
          'Per-user export & download history',
          'Filterable, exportable activity trail',
        ],
        utmMedium: 'audit_log',
        preview: 'audit_log',
      },
    },
  },
]

export const router = createRouter({
  history: createMemoryHistory(),
  routes,
})

// Keep ?tab= in sync so PHP knows which bundle to enqueue on page refresh.
router.afterEach((to) => {
  let tab

  switch (to.name) {
    case 'export-items':
      tab = 'export_items'
      break
    case 'quick-export':
      tab = 'quick_export'
      break
    case 'scheduled-exports':
      tab = 'scheduled_export'
      break
    case 'export-templates':
      tab = 'export_template'
      break
    case 'archives':
      tab = 'archive'
      break
    case 'settings':
      tab = 'settings'
      break
    case 'tools':
      tab = 'tools'
      break
    case 'custom-meta-fields':
      tab = 'custom_meta_fields'
      break
    case 'custom-meta-fields-form':
      tab = `custom_meta_fields/${to.params.type}`
      break
    case 'audit-log':
      tab = 'audit_log'
      break
    default:
      tab = 'overview'
  }

  // Use decodeURIComponent when reading so %2F and / both match the plain tab value.
  const url = new URL(window.location.href)
  if (decodeURIComponent(url.searchParams.get('tab') ?? '') !== tab) {
    // Write the tab value as a literal string to preserve slashes — URLSearchParams
    // would percent-encode them to %2F.
    let qs
    if (!url.search) {
      qs = `?tab=${tab}`
    } else if (/[?&]tab=/.test(url.search)) {
      qs = url.search.replace(/([?&]tab=)[^&]*/, `$1${tab}`)
    } else {
      qs = url.search + `&tab=${tab}`
    }
    const next = url.origin + url.pathname + qs
    history.replaceState(null, '', next)
  }
})
