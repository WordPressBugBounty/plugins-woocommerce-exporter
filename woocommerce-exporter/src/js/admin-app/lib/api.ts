import { __, sprintf } from '@wordpress/i18n'
import type { WseAdminData } from './types'

function wpData (): Partial<WseAdminData> {
  return window.wseAdminData ?? {}
}

/** An Error carrying the rejected response's WP_Error code and HTTP status. */
export interface RestError extends Error {
  code: string
  status: number
}

export interface RequestOptions {
  /**
   * Send `_locale=user` so server-provided labels follow the logged-in user's
   * language. Defaults to true; pass false to get site-language labels.
   */
  userLocale?: boolean
  /**
   * Query parameters for the request.
   *
   * This is the ONLY way to put a query parameter on a request. `path` is a REST
   * route and nothing else — see `buildUrl()`.
   *
   * A key that is reserved, or whose name would reach PHP as a reserved one,
   * throws: it would re-route or re-verb a request carrying the administrator
   * nonce. See `SAFE_PARAM_KEY` and `RESERVED_PARAMS`.
   */
  params?: Record<string, string | number>
}

/**
 * Query keys a request may never carry.
 *
 * Each one re-points or re-verbs a request that goes out with the administrator's `X-WP-Nonce`.
 * On plain permalinks the REST URL already contains `rest_route=`, and WordPress resolves the
 * last one, so a second `rest_route=` runs the request against a route the app never named;
 * `_method` turns a GET into a POST; `_jsonp` and `_envelope` change how the answer comes back;
 * `_wpnonce` would override the header nonce with a value the caller chose. That is the chain
 * issue #121 / Patchstack 40396 used, and `params` is now the one sanctioned query channel — so
 * this is where the invariant is enforced, not left to each caller.
 *
 * No caller supplies a dynamic key today (every `params` object in the app is a literal), so this
 * guards the next one: a caller that spreads route- or query-derived data, e.g.
 * `api.get('x', { params: { ...route.query } })`. Throwing rather than dropping is deliberate — a
 * silent drop would let such a caller ship and only show up as a request that quietly asks for
 * something else.
 *
 * `_fields` and `_embed` are deliberately absent: they shape the response body and cannot change
 * which route runs or with what method, so they are safe for a caller to pass.
 */
const RESERVED_PARAMS = new Set(['rest_route', '_method', '_envelope', '_jsonp', '_wpnonce'])

/**
 * The shape a query key is allowed to have.
 *
 * Matching the reserved names alone is not enough, because the name JavaScript sends is not
 * always the name PHP reads. `parse_str()` — which is what fills `$_GET` — rewrites `.` and ` `
 * in a key to `_`, and treats `[` as the start of array syntax. Measured on this project's PHP:
 *
 *     parse_str('rest.route=a&rest route=b&rest_route[]=c&.method=POST&_method[]=POST', $o);
 *     // array_keys($o) === ['rest_route', '_method']
 *
 * `URLSearchParams` is no help either: `.` is unreserved so it is not encoded at all, ` ` becomes
 * `+` and `[` becomes `%5B`, and PHP decodes both before it mangles. So `rest.route`, `.method`
 * and `_wpnonce[]` would all walk straight past a name-only check and still arrive server-side as
 * the reserved key. Validating the shape closes the whole family at once, and every key the app
 * actually sends (`type`, `context`, `refresh`, `_locale`) passes unchanged.
 */
const SAFE_PARAM_KEY = /^[A-Za-z0-9_-]+$/

/**
 * Build a final URL by merging `path` onto wpData.restUrl.
 *
 * Handles both pretty permalinks (/wp-json/wse/v1/) and plain permalinks
 * (?rest_route=/wse/v1/), where naive concatenation would produce two `?`
 * separators and break query parsing in WordPress.
 *
 * `path` is a REST route and nothing else. Anything from the first `?` or `#`
 * onwards is dropped, and query parameters arrive only through `params`, which
 * percent-encodes them. This used to be a `path.split('?')`, with the remainder
 * appended to the REST URL verbatim — which is how a value that reached the
 * path could add query parameters to a request carrying the administrator's
 * nonce. On plain permalinks that meant a second `rest_route=`, which WordPress
 * resolves last-one-wins, so the request ran against a route the app never
 * named. See issue #121 / Patchstack 40396.
 *
 * Requests carry `_locale=user` by default, as WordPress's own apiFetch does,
 * so server-provided labels follow the logged-in user's language — the same
 * one the SPA's own strings are translated into — not the site language.
 * Requests whose labels get saved (field labels become the export header row)
 * opt out with `userLocale: false`: headers must follow the site language, and
 * what the user sees in the label input must be what gets exported (WYSIWYG).
 * With "Reset language to English" on, no request asks for the user locale:
 * REST then runs in the site locale, as the plugin did before the SPA was
 * translated, so server labels and dates do not mix into the English screen.
 * Neither opt-out drops `params`: the locale decides one parameter, not whether
 * the request has a query string.
 */
function buildUrl (
  path: string,
  userLocale = true,
  params: Record<string, string | number> = {}
): string {
  const base = wpData().restUrl ?? ''
  const pathOnly = path.split(/[?#]/)[0]

  // Discarding is the safe default, but a silent discard turns a caller that still builds its
  // query string in the path into a request that quietly asks for something else — no throw, no
  // failing test, just a parameter that stopped arriving. Say so out loud.
  if (pathOnly !== path) {
    // eslint-disable-next-line no-console
    console.warn(
      `[wse] api path "${path}" carries a query string or fragment; it was discarded. ` +
      'Pass query parameters through the `params` request option instead.'
    )
  }

  const search = new URLSearchParams()
  for (const [key, value] of Object.entries(params)) {
    if (!SAFE_PARAM_KEY.test(key) || RESERVED_PARAMS.has(key)) {
      // The detail names the offending key, for whoever has to find the call site. It goes to the
      // console rather than into the thrown message because callers surface `err.message` on
      // screen (quickExport.js assigns it to `configError` and to each type's `fields` error), and
      // an untranslated internal key name does not belong in a translated admin screen.
      // eslint-disable-next-line no-console
      console.error(
        `[wse] query parameter "${key}" is not allowed in \`params\`: it is reserved, or its ` +
        'name would reach PHP as a reserved one. It would re-route or re-verb a request carrying ' +
        'the administrator nonce. See issue #121.'
      )

      throw new Error(__('The request could not be built: it named a reserved query parameter.', 'woocommerce-exporter'))
    }
    search.append(key, String(value))
  }
  // A caller that sets its own _locale wins: appending ours too would send the parameter twice.
  if (userLocale && !wpData().resetLanguage && !search.has('_locale')) search.append('_locale', 'user')

  const query = search.toString()
  const separator = base.includes('?') ? '&' : '?'

  return base + pathOnly + (query ? separator + query : '')
}

async function wpFetch<T = unknown> (
  path: string,
  options: RequestInit = {},
  { userLocale = true, params = {} }: RequestOptions = {}
): Promise<T> {
  const response = await fetch(buildUrl(path, userLocale, params), {
    ...options,
    headers: {
      'X-WP-Nonce': wpData().nonce ?? '',
      'Content-Type': 'application/json',
      ...options.headers,
    },
  })

  if (!response.ok) {
    const body = await response.json().catch(() => ({})) as { message?: string, code?: string }
    const error = new Error(body.message ?? sprintf(
      /* translators: %d: HTTP status code */
      __('HTTP %d', 'woocommerce-exporter'),
      response.status
    )) as RestError
    // The WP_Error code travels with the message so a caller can tell WHICH
    // field a rejection is about and show it beside that field.
    error.code = body.code ?? ''
    error.status = response.status
    throw error
  }

  return response.json() as Promise<T>
}

export const api = {
  get:    <T = unknown>(path: string, requestOptions: RequestOptions = {}) =>
    wpFetch<T>(path, {}, requestOptions),
  post:   <T = unknown>(path: string, data: unknown, requestOptions: RequestOptions = {}) =>
    wpFetch<T>(path, { method: 'POST', body: JSON.stringify(data) }, requestOptions),
  put:    <T = unknown>(path: string, data: unknown, requestOptions: RequestOptions = {}) =>
    wpFetch<T>(path, { method: 'PUT',  body: JSON.stringify(data) }, requestOptions),
  delete: <T = unknown>(path: string, requestOptions: RequestOptions = {}) =>
    wpFetch<T>(path, { method: 'DELETE' }, requestOptions),
}
