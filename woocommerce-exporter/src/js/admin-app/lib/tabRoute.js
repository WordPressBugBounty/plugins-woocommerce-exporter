/**
 * The ?tab= URL parameter, translated into the SPA's own routes.
 *
 * This is the only place a value taken off the address bar becomes a Vue Router path, so it is
 * also the place that decides what a link is allowed to ask the app to do. A tab value reaches
 * here exactly as `URLSearchParams.get('tab')` hands it over — decoded once, and not yet decoded
 * by Vue Router. Both guards below test THAT string and build their result from THAT string, so
 * there is no window between what was checked and what is used.
 *
 * @since 2.8.4
 */

/**
 * Tab base → route path.
 *
 * Null-prototype so a lookup can only ever find a key written here. A plain object literal
 * resolves inherited keys, which would make `tabToRoute('constructor')` return a function and
 * `tabToRoute('__proto__')` return `Object.prototype` — neither of them a route.
 *
 * This is belt as well as braces: the `typeof baseRoute !== 'string'` test below catches every
 * inherited key on its own, because nothing on `Object.prototype` is a string. Either mechanism
 * alone is enough, and neither is independently covered by a test for that reason. Both are kept
 * because the pair costs nothing and the failure they prevent is a non-string reaching
 * `router.replace()`. None of it is reachable from a link today — PHP only enqueues the bundle
 * for a base in `woo_ce_spa_tabs()` — but the two allow-lists are maintained apart.
 *
 * Deluxe-only tabs (scheduled_export, export_template, archive, audit_log) resolve to their
 * in-app upsell route in the free plugin.
 */
const BASE_ROUTES = Object.assign(Object.create(null), {
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
})

/**
 * What a tab sub-path is allowed to contain.
 *
 * An export type slug and a settings category are both lower-case slugs. Everything the
 * route-injection payload needs — `?`, `&`, `=`, `%`, `/`, `#` — is outside this set, and so is
 * every character `String.replace()` would read as a replacement pattern when the router syncs
 * the value back into the address bar.
 */
const SUB_PATH = /^[a-z0-9_-]+$/

/**
 * Split a tab value into its base and its sub-path.
 *
 * @param {string} tab - e.g. 'custom_meta_fields/product'
 * @returns {{base: string, sub: string}} The sub-path is '' when the tab has none.
 */
function splitTab (tab) {
  const slash = tab.indexOf('/')

  return slash === -1
    ? { base: tab, sub: '' }
    : { base: tab.slice(0, slash), sub: tab.slice(slash + 1) }
}

/**
 * Convert a ?tab= value back to a Vue Router path.
 *
 * Always returns a string, and never one the caller did not write: an unknown base and a
 * sub-path that is not a slug both fall back rather than being passed through.
 *
 * @param {string} tab - e.g. 'custom_meta_fields/product'
 * @returns {string} Vue route path, e.g. '/custom-meta-fields/product'
 */
export function tabToRoute (tab) {
  const { base, sub } = splitTab(String(tab ?? ''))
  const baseRoute = BASE_ROUTES[base]
  if (typeof baseRoute !== 'string') return '/'
  if (sub === '') return baseRoute

  // settings/{category} encodes the active section, not a distinct route — route to /settings
  // only. Deluxe-only bases have no sub-routes in the free plugin — land on the upsell.
  // Custom Meta Fields is the only free route with a real sub-path (/:type).
  if (base !== 'custom_meta_fields') return baseRoute

  return SUB_PATH.test(sub) ? `${baseRoute}/${sub}` : baseRoute
}

/**
 * The Settings category a ?tab=settings/{category} value asks for.
 *
 * Read separately from `tabToRoute()` because the category is not part of the route — Settings
 * is one route with a section selected inside it — but it comes off the same attacker-reachable
 * string, so it gets the same guard. The Settings screen clamps an unknown category to its
 * default anyway; this keeps the rejected value from reaching the store in the first place.
 *
 * @param {string} tab - e.g. 'settings/formats'
 * @returns {string} The category slug, or '' when the tab names none or names an unusable one.
 */
export function settingsCategoryFromTab (tab) {
  const { base, sub } = splitTab(String(tab ?? ''))
  if (base !== 'settings' || sub === '') return ''

  return SUB_PATH.test(sub) ? sub : ''
}

/**
 * The view a Store Export page load should start on, from its query string.
 *
 * The whole hand-off from the address bar to the router, in one place so it can be tested
 * without a browser: `main.js` mounts an app on import, so nothing can import it to check that
 * the guards are actually wired up. Previously that wiring was the one part of this fix with no
 * unit coverage at all.
 *
 * @param {string} search - `window.location.search`.
 * @returns {{tab: string, path: string, settingsCategory: string}} The raw tab value, the route
 *          to start on, and the Settings section to restore ('' when the tab names none).
 */
export function initialViewFromSearch (search) {
  const tab = new URLSearchParams(search).get('tab') || ''

  return {
    tab,
    path: tabToRoute(tab),
    settingsCategory: settingsCategoryFromTab(tab),
  }
}
