/**
 * Export Filename pattern: the default, the tokens the Settings screen offers,
 * and a preview that mirrors how the server resolves a pattern.
 *
 * Kept free of Vue and the store so the preview can be unit-tested on its own.
 *
 * @since 2.8.3
 */

// Must match Export::DEFAULT_FILENAME_PATTERN in includes/Helpers/Export.php, so the
// reset-to-default button stores what the server falls back to when nothing is saved.
export const DEFAULT_FILENAME_PATTERN = '{store}-export_{type}-{date}-{time}-{random}'

// The tokens offered as chips on the Settings screen.
export const SETTINGS_FILENAME_TOKENS = [
  '{date}', '{time}', '{type}', '{store}', '{year}', '{month}', '{day}', '{hour}', '{minute}', '{second}', '{random}'
]

// The server draws a different random number each export, so the preview shows a
// fixed sample of the same shape (eight digits).
const SAMPLE_RANDOM = '12345678'

const pad = (n) => String(n).padStart(2, '0')

/**
 * Preview a Filename pattern the way the server will resolve it.
 *
 * Dates and times use UTC because the server resolves them in UTC; local-time getters
 * would preview a different filename than the one exported. The legacy `%tag%`
 * spellings are resolved too, so a store with an older stored pattern previews correctly.
 *
 * @param {string} pattern The Filename pattern.
 * @param {{ now?: Date, type?: string, store?: string, format?: string }} [sample] Values standing in for the export.
 * @return {string} The pattern with its tokens replaced.
 */
export function previewFilename (pattern, { now = new Date(), type = 'product', store = 'my-store', format = 'csv' } = {}) {
  if (!pattern) return ''

  const year = String(now.getUTCFullYear())
  const month = pad(now.getUTCMonth() + 1)
  const day = pad(now.getUTCDate())
  const hour = pad(now.getUTCHours())
  const minute = pad(now.getUTCMinutes())
  const second = pad(now.getUTCSeconds())

  // Tag name => value. Each tag is matched as {tag} and as the legacy %tag%.
  const values = {
    date: `${year}_${month}_${day}`,
    time: `${hour}_${minute}_${second}`,
    year,
    month,
    day,
    hour,
    minute,
    second,
    random: SAMPLE_RANDOM,
    // The server names RSS exports .xml, so {format} resolves the same way.
    format: format === 'rss' ? 'xml' : format,
    timestamp: String(Math.floor(now.getTime() / 1000)),
    // The legacy spellings of these two differ from the brace names.
    type,
    store
  }
  const legacyNames = { type: 'dataset', store: 'store_name' }

  return Object.entries(values).reduce((result, [tag, value]) => {
    const legacy = legacyNames[tag] ?? tag
    return result.split(`{${tag}}`).join(value).split(`%${legacy}%`).join(value)
  }, pattern)
}
