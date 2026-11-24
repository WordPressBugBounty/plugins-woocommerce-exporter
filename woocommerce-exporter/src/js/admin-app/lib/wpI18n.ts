/**
 * `@wordpress/i18n` for the admin SPA.
 *
 * Vite and Vitest alias `@wordpress/i18n` to this module, so components import
 * the standard WordPress API (`import { __ } from '@wordpress/i18n'`) while the
 * calls run through WordPress's own `wp.i18n` instance — the one that
 * `wp_set_script_translations()` loads the plugin's translations into. Bundling
 * the npm package instead would give the SPA a separate, always-empty locale.
 *
 * When `wp.i18n` is absent (unit tests), the functions fall back to returning
 * the untranslated English text.
 *
 * @since 2.8.3
 */

interface WpI18n {
  __: (text: string, domain?: string) => string
  _x: (text: string, context: string, domain?: string) => string
  _n: (single: string, plural: string, number: number, domain?: string) => string
  _nx: (single: string, plural: string, number: number, context: string, domain?: string) => string
  sprintf: (format: string, ...args: unknown[]) => string
}

declare global {
  interface Window {
    wp?: { i18n?: WpI18n }
  }
}

// Read lazily: wp.i18n is a classic script, and tests may install it late.
function wpI18n (): WpI18n | undefined {
  return typeof window !== 'undefined' ? window.wp?.i18n : undefined
}

export function __ (text: string, domain?: string): string {
  return wpI18n()?.__(text, domain) ?? text
}

export function _x (text: string, context: string, domain?: string): string {
  return wpI18n()?._x(text, context, domain) ?? text
}

export function _n (single: string, plural: string, number: number, domain?: string): string {
  return wpI18n()?._n(single, plural, number, domain) ?? (number === 1 ? single : plural)
}

export function _nx (single: string, plural: string, number: number, context: string, domain?: string): string {
  return wpI18n()?._nx(single, plural, number, context, domain) ?? (number === 1 ? single : plural)
}

/**
 * Replace `%s` / `%d` and numbered `%1$s` placeholders, mirroring the subset
 * of `wp.i18n.sprintf` the SPA uses.
 */
export function sprintf (format: string, ...args: unknown[]): string {
  const i18n = wpI18n()
  if (i18n) return i18n.sprintf(format, ...args)

  let next = 0
  return format.replace(/%(?:(\d+)\$)?([sd%])/g, (match, position: string | undefined, type: string) => {
    if (type === '%') return '%'
    const value = args[position ? Number(position) - 1 : next++]
    return type === 'd' ? String(parseInt(String(value), 10)) : String(value)
  })
}
