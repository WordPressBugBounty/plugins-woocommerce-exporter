/**
 * Dates in the admin's language.
 *
 * `formatWpDate()` formats a date with a PHP/WordPress date format string
 * (`F j, Y`, `jS M Y, g:i a`, …), the way `wp_date()` / `mysql2date()` would.
 * Month, weekday and am/pm names come from WordPress's `$wp_locale`, which
 * WP_Admin passes in `wseAdminData.dateLocale`, so they follow the core
 * language pack. English is the fallback when that data is absent (unit tests).
 *
 * Why not `wp.date.format()` (`@wordpress/date`)? It is not a dependency of the
 * admin script (only `wp-i18n` is registered in WP_Admin), so using it would add
 * a runtime script dependency. It also reads month/weekday names from its own
 * `wp.date.setSettings()` state rather than `wseAdminData.dateLocale`, which
 * would break the SPA's own language switch (including Reset to English), and
 * it formats in the site timezone whereas the SPA formats plain local calendar
 * dates (`YYYY-MM-DD`) that must not shift across timezones.
 *
 * `intlLocale()` is the same language as a BCP 47 tag, for the number format
 * the SPA builds with `Intl`.
 *
 * @since 2.8.3
 */

import { _x } from '@wordpress/i18n'

const ENGLISH_DATE_LOCALE = {
  locale: 'en-US',
  months: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
  monthsShort: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
  weekdays: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
  weekdaysShort: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
  meridiem: { am: 'am', pm: 'pm', AM: 'AM', PM: 'PM' },
}

/**
 * Month, weekday and am/pm names for the admin's language.
 *
 * @returns {typeof ENGLISH_DATE_LOCALE}
 */
export function dateLocale () {
  const locale = typeof window !== 'undefined' ? window.wseAdminData?.dateLocale : undefined
  return locale?.months?.length === 12 ? { ...ENGLISH_DATE_LOCALE, ...locale } : ENGLISH_DATE_LOCALE
}

let cachedIntlLocale = null
let cachedNumberFormat = null

/**
 * Forget the cached locale and number format. For tests that change the locale.
 *
 * @since 2.8.3
 */
export function _resetLocaleCache () {
  cachedIntlLocale = null
  cachedNumberFormat = null
}

/**
 * The admin's language as a BCP 47 tag `Intl` accepts, e.g. `de-DE`.
 *
 * WordPress locales are not always valid tags (`pt-PT-ao90`) or ones the
 * browser supports, so the tag is shortened a part at a time (`pt-PT`,
 * then `pt`) before falling back to English.
 *
 * @returns {string}
 */
export function intlLocale () {
  if (cachedIntlLocale === null) cachedIntlLocale = resolveIntlLocale()
  return cachedIntlLocale
}

function resolveIntlLocale () {
  const parts = String(dateLocale().locale).split('-')
  for (let length = parts.length; length > 0; length--) {
    const tag = parts.slice(0, length).join('-')
    try {
      if (Intl.DateTimeFormat.supportedLocalesOf([tag]).length) return tag
    } catch {
      // Not a valid tag; try a shorter one.
    }
  }
  return ENGLISH_DATE_LOCALE.locale
}

/**
 * The `S` token: the suffix after a day of the month.
 *
 * @param {number} day Day of the month, 1–31.
 * @returns {string}
 */
function ordinalSuffix (day) {
  if (day >= 11 && day <= 13) return _x('th', 'ordinal suffix for 11–13 and days ending in 4–9 or 0, as in 11th', 'woocommerce-exporter')
  switch (day % 10) {
    case 1: return _x('st', 'ordinal suffix for days ending in 1, as in 21st', 'woocommerce-exporter')
    case 2: return _x('nd', 'ordinal suffix for days ending in 2, as in 22nd', 'woocommerce-exporter')
    case 3: return _x('rd', 'ordinal suffix for days ending in 3, as in 23rd', 'woocommerce-exporter')
    default: return _x('th', 'ordinal suffix for 11–13 and days ending in 4–9 or 0, as in 11th', 'woocommerce-exporter')
  }
}

const pad2 = (n) => String(n).padStart(2, '0')

/**
 * A Date as a local calendar date, `YYYY-MM-DD` — the value the date filters store.
 *
 * @since 2.8.3
 * @param {Date} date
 * @returns {string}
 */
export function toIsoDate (date) {
  return `${date.getFullYear()}-${pad2(date.getMonth() + 1)}-${pad2(date.getDate())}`
}

/**
 * Turn a `YYYY-MM-DD` string into a local Date, or pass a Date through.
 *
 * @param {Date|string} value
 * @returns {Date|null} Null when the value is not a date.
 */
function toDate (value) {
  if (value instanceof Date) return Number.isNaN(value.getTime()) ? null : value
  const [y, m, d] = String(value).split('-').map((part) => parseInt(part, 10))
  return [y, m, d].some(Number.isNaN) ? null : new Date(y, m - 1, d)
}

/**
 * @param {Date|string} value  A Date, or a date as `YYYY-MM-DD`.
 * @param {string}      format PHP date format; tokens d j D l N w S m n M F Y y
 *                             a A g G h H i s, with `\` escaping a character.
 * @returns {string} The formatted date, the input if it is not a date, or '' when empty.
 */
export function formatWpDate (value, format) {
  if (!value) return ''
  const date = toDate(value)
  if (!date) return String(value)

  const names = dateLocale()
  const day = date.getDate()
  const month = date.getMonth()
  const weekday = date.getDay()
  const hours = date.getHours()
  const meridiem = hours >= 12 ? 'pm' : 'am'
  const token = (ch) => {
    switch (ch) {
      case 'd': return pad2(day)
      case 'j': return String(day)
      case 'D': return names.weekdaysShort[weekday]
      case 'l': return names.weekdays[weekday]
      case 'N': return String(weekday || 7)
      case 'w': return String(weekday)
      case 'S': return ordinalSuffix(day)
      case 'm': return pad2(month + 1)
      case 'n': return String(month + 1)
      case 'M': return names.monthsShort[month]
      case 'F': return names.months[month]
      case 'Y': return String(date.getFullYear())
      case 'y': return String(date.getFullYear()).slice(-2)
      case 'a': return names.meridiem[meridiem]
      case 'A': return names.meridiem[meridiem.toUpperCase()]
      case 'g': return String(hours % 12 || 12)
      case 'G': return String(hours)
      case 'h': return pad2(hours % 12 || 12)
      case 'H': return pad2(hours)
      case 'i': return pad2(date.getMinutes())
      case 's': return pad2(date.getSeconds())
      default: return ch
    }
  }

  let out = ''
  for (let i = 0; i < format.length; i++) {
    const ch = format[i]
    if (ch === '\\' && i + 1 < format.length) { out += format[++i]; continue }
    out += token(ch)
  }
  return out
}

/**
 * A count with the admin's digit grouping, e.g. 1234 -> "1,234".
 *
 * @since 2.8.3
 * @param {number} n
 * @returns {string}
 */
export function formatCount (n) {
  if (cachedNumberFormat === null) cachedNumberFormat = new Intl.NumberFormat(intlLocale())
  return cachedNumberFormat.format(n)
}
