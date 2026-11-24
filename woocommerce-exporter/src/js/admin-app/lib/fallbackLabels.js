import { __ } from '@wordpress/i18n'

/**
 * English keys the SPA uses as internal fallbacks (field category 'Custom' or
 * 'Basic Info', data type 'Text' or 'Number', filter group 'Other'), each
 * mapped to a function returning its translation.
 *
 * Functions, not module-level __() results, so locale data registered after
 * this module loads still applies.
 */
export const FALLBACK_LABEL_KEYS = {
  Custom: () => __('Custom', 'woocommerce-exporter'),
  Text: () => __('Text', 'woocommerce-exporter'),
  Number: () => __('Number', 'woocommerce-exporter'),
  Other: () => __('Other', 'woocommerce-exporter'),
  'Basic Info': () => __('Basic Info', 'woocommerce-exporter'),
}

/**
 * Translate an internal fallback key at render time.
 *
 * @param {string} key Internal key.
 * @return {string} Translated label; unknown keys are returned unchanged.
 */
export function fallbackLabel (key) {
  return FALLBACK_LABEL_KEYS[key]?.() ?? key
}

/**
 * Display label for an item carrying a stable key and an optional
 * server-provided translated label.
 *
 * @param {string} key   Stable English key.
 * @param {string} label Server-provided translated label, if any.
 * @return {string}
 */
export function displayLabel (key, label) {
  return label || fallbackLabel(key)
}
