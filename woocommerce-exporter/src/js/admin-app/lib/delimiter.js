import { __, _x } from '@wordpress/i18n'

// Single source of truth for the global Field Delimiter vocabulary on
// Settings > Export Formats. Mirrors Export_Helper's delimiter rules
// (includes/Helpers/Export.php), so the screen refuses the same values the
// settings endpoint refuses.

// The pills the Field Delimiter offers. A stored value outside this list (a
// '^' or '~' from an older build) is shown through the Custom pill.
export const DELIMITER_OPTIONS = [
  { value: ',', label: __('Comma ( , )', 'woocommerce-exporter') },
  { value: ';', label: __('Semicolon ( ; )', 'woocommerce-exporter') },
  { value: '\t', label: _x('Tab', 'delimiter', 'woocommerce-exporter') },
  { value: '|', label: __('Pipe ( | )', 'woocommerce-exporter') }
]

// Labels for the Settings screen's Configuration Summary, which names the
// character as well as the delimiter. A delimiter outside the table is shown
// as itself.
export const SETTINGS_SUMMARY_LABELS = {
  ',': __('Comma (,)', 'woocommerce-exporter'),
  ';': __('Semicolon (;)', 'woocommerce-exporter'),
  '\t': _x('Tab', 'delimiter', 'woocommerce-exporter')
}

// Sentinel for the "Custom" pill. It is a UI mode, not a delimiter: picking it
// reveals a one-character input. It cannot collide with a real delimiter,
// which is always a single character or the TAB token.
export const CUSTOM_DELIMITER = '__custom__'

// The token persisted in place of a literal tab, because sanitize_text_field()
// collapses whitespace and would store ''. Mirrors
// Export_Helper::DELIMITER_TAB_TOKEN.
export const TAB_DELIMITER_TOKEN = 'TAB'

// Single characters the delimited writer cannot use, mirroring
// Export_Helper::UNUSABLE_DELIMITERS: '0' is falsy, so the export engine swaps
// it for the default; '"' and '\' are the CSV enclosure and escape bytes.
export const UNUSABLE_DELIMITERS = ['0', '"', '\\']

// The one character sanitize_text_field() does not hand back as itself:
// a lone '<' becomes '&lt;', which the server then refuses. Everything else it
// rewrites is whitespace, which the trim below covers.
const SANITIZER_REWRITES = ['<']

/**
 * Turn a stored delimiter into the literal separator the pill values use.
 *
 * @param {string} stored The delimiter as the settings endpoint reports it.
 * @return {string} The literal separator.
 */
export function decodeStoredDelimiter (stored) {
  return stored === TAB_DELIMITER_TOKEN ? '\t' : (stored ?? '')
}

/**
 * Reverse decodeStoredDelimiter(): the shape to send back to the server.
 *
 * @param {string} delimiter The literal separator a pill carries.
 * @return {string} The value to store.
 */
export function encodeStoredDelimiter (delimiter) {
  return delimiter === '\t' ? TAB_DELIMITER_TOKEN : (delimiter ?? '')
}

/**
 * Whether a delimiter is one the CSV writer can use.
 *
 * Mirrors Export_Helper::is_storable_delimiter(), the rule the settings
 * endpoint applies to a submitted value. The server stays the authority; this
 * only lets the screen say so beside the field.
 *
 *  - The server counts BYTES. A string's `.length` counts UTF-16 units, so
 *    'é' reads as one. TextEncoder gives the byte count.
 *  - The value is sanitize_text_field()'d before it is stored, which trims
 *    whitespace to '' and turns '<' into '&lt;'.
 *
 * @param {string} delimiter The delimiter, as stored or as a literal tab.
 * @return {boolean} True when the writer can use it.
 */
export function isValidDelimiter (delimiter) {
  if (typeof delimiter !== 'string') {
    return false
  }

  const stored = encodeStoredDelimiter(delimiter)

  if (stored === TAB_DELIMITER_TOKEN) {
    return true
  }

  if (stored.trim() === '' || SANITIZER_REWRITES.includes(stored)) {
    return false
  }

  return new TextEncoder().encode(stored).length === 1 && !UNUSABLE_DELIMITERS.includes(stored)
}
