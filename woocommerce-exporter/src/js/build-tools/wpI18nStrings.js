/**
 * Build-time string catalogue for the admin SPA's translations.
 *
 * `wp i18n make-pot` only parses PHP and plain JavaScript, so it never sees
 * the `__()` calls inside `.vue` single-file components, and it cannot read
 * them out of the minified Vite bundle either. This module scans the SPA
 * source for every `__()` / `_x()` / `_n()` / `_nx()` call and writes them,
 * verbatim, into one plain JS file in the build output. make-pot extracts the
 * strings from that file, and WP_Admin points the SPA's script translations
 * at the same path, so a JSON file generated from a translated `.po`
 * (`wp i18n make-json`) is the one WordPress loads.
 *
 * Any call it cannot read — a non-literal string, a missing or wrong text
 * domain, an HTML entity Vue would decode, an aliased or namespace import of
 * `@wordpress/i18n` — fails the build instead of silently dropping out of the
 * catalogue. Calls inside comments are ignored; comments are found with Vue's
 * SFC parser and Babel, so a `//` inside a string or URL is not one.
 *
 * @since 2.8.3
 */

import { readdirSync, readFileSync } from 'node:fs'
import { join, relative, sep } from 'node:path'
import { parse as parseSfc, babelParse } from 'vue/compiler-sfc'

export const TEXT_DOMAIN = 'woocommerce-exporter'

// Must match WP_Admin::ADMIN_APP_TRANSLATION_SOURCE (relative to the plugin root);
// src/tests/lib/wpI18nStrings.test.js checks the two agree.
export const STRINGS_FILE_NAME = 'i18n-strings.js'

// Positional argument kinds per function: 'string' must be a string literal,
// 'number' is any expression. The text domain always follows.
const SIGNATURES = {
  __: ['string'],
  _x: ['string', 'string'],
  _n: ['string', 'string', 'number'],
  _nx: ['string', 'string', 'number', 'string'],
}

// A call to one of the functions above — not a method such as `wp.i18n.__(`.
const CALL_PATTERN = /(?<![\w$.])(__|_x|_nx|_n)\s*\(/g

const STRING_LITERAL = /^(['"])(?:\\[\s\S]|(?!\1)[^\\\n])*\1$/

// A `translators:` comment, carried into the catalogue with its call. A
// template can only hold an HTML one: `<!-- translators: … -->`.
const TRANSLATORS_COMMENT = /\/\*\s*translators:(?:(?!\*\/)[\s\S])*\*\/|\/\/\s*translators:[^\n]*|<!--\s*translators:(?:(?!-->)[\s\S])*-->/gi

// How far before a call to look for its translators comment.
const COMMENT_LOOKBEHIND = 500

// Imports that would hide calls from CALL_PATTERN: `{ __ as t }` or `* as i18n`.
const HIDDEN_IMPORT = /import\s*(?:\{[^}]*\bas\b[^}]*\}|\*\s*as\s+\w+)\s*from\s*['"]@wordpress\/i18n['"]/

const HTML_COMMENT = /<!--[\s\S]*?-->/g

// An entity in a template string is decoded by Vue at runtime, so the
// catalogued msgid would never match what the SPA looks up.
const HTML_ENTITY = /&(?:[a-z]+|#\d+|#x[\da-f]+);/i

const SOURCE_EXTENSIONS = /\.(vue|js|ts)$/

/**
 * Return the index of the quote that closes the string starting at `start`.
 *
 * @param {string} source
 * @param {number} start Index of the opening quote.
 * @returns {number}
 */
function closingQuoteIndex (source, start) {
  const quote = source[start]
  for (let i = start + 1; i < source.length; i++) {
    if (source[i] === '\\') {
      i++
    } else if (source[i] === quote) {
      return i
    }
  }
  return source.length
}

/**
 * Find the comments in a source file, as [start, end) offsets.
 *
 * Script code — a `.js`/`.ts` file or a `.vue` file's `<script>` blocks — is
 * parsed with Babel, so `//` inside a string or URL is not taken for a
 * comment. The rest of a `.vue` file is markup, where only `<!-- -->` is one.
 *
 * @param {string} source
 * @param {string} file
 * @returns {number[][]}
 */
function commentRanges (source, file) {
  const ranges = []
  const addScriptComments = (code, offset, lang) => {
    const ast = babelParse(code, {
      sourceType: 'module',
      plugins: lang === 'ts' ? ['typescript'] : [],
      errorRecovery: true,
    })
    for (const comment of ast.comments) ranges.push([comment.start + offset, comment.end + offset])
  }

  if (!file.endsWith('.vue')) {
    addScriptComments(source, 0, file.endsWith('.ts') ? 'ts' : 'js')
    return ranges
  }

  const { descriptor } = parseSfc(source, { filename: file })
  const scripts = [descriptor.script, descriptor.scriptSetup].filter(Boolean)
  for (const block of scripts) addScriptComments(block.content, block.loc.start.offset, block.lang)

  const inScript = (index) => scripts.some((block) => index >= block.loc.start.offset && index < block.loc.end.offset)
  for (const match of source.matchAll(HTML_COMMENT)) {
    if (!inScript(match.index)) ranges.push([match.index, match.index + match[0].length])
  }
  return ranges
}

/**
 * Find the translators comment that belongs to the call at `callIndex`.
 *
 * Mirrors make-pot: the comment ends on the call's line or the line before
 * (so a comment above `return sprintf(__(…), value)` counts), and
 * no other i18n call sits between them to claim it first.
 *
 * @param {string} source
 * @param {number} callIndex
 * @returns {string} The comment, or '' when there is none.
 */
function translatorsComment (source, callIndex) {
  const before = source.slice(Math.max(0, callIndex - COMMENT_LOOKBEHIND), callIndex)
  const comment = [...before.matchAll(TRANSLATORS_COMMENT)].at(-1)
  if (!comment) return ''

  const between = before.slice(comment.index + comment[0].length)
  const claimedEarlier = new RegExp(CALL_PATTERN.source).test(between)
  if (between.split('\n').length > 2 || claimedEarlier) return ''

  // The catalogue is JavaScript, so an HTML comment is rewritten as a block one.
  return comment[0].replace(/^<!--\s*([\s\S]*?)\s*-->$/, '/* $1 */')
}

/**
 * Split a call's arguments, starting just after its opening parenthesis.
 *
 * @param {string} source
 * @param {number} start
 * @returns {string[]|null} Trimmed argument sources, or null when unbalanced.
 */
function splitArguments (source, start) {
  const args = []
  let depth = 0
  let current = ''

  for (let i = start; i < source.length; i++) {
    const char = source[i]

    if (char === '"' || char === "'" || char === '`') {
      const end = closingQuoteIndex(source, i)
      current += source.slice(i, end + 1)
      i = end
    } else if (char === ',' && depth === 0) {
      args.push(current.trim())
      current = ''
    } else if (char === ')' && depth === 0) {
      args.push(current.trim())
      // Allow a trailing comma after the last argument.
      return args.at(-1) === '' ? args.slice(0, -1) : args
    } else {
      if ('([{'.includes(char)) depth++
      if (')]}'.includes(char)) depth--
      current += char
    }
  }

  return null
}

/**
 * Find every i18n call in one source file.
 *
 * @param {string} source File contents.
 * @param {string} file   Path used in references and error messages.
 * @returns {{ code: string, reference: string, translators: string }[]}
 * @throws {Error} When a call has a non-literal string or a wrong text domain.
 */
export function extractI18nCalls (source, file) {
  const calls = []

  if (HIDDEN_IMPORT.test(source)) {
    throw new Error(`${file}: import __, _x, _n and _nx from '@wordpress/i18n' by name, without \`as\`, so their strings can be catalogued.`)
  }

  // Blank out comments, keeping offsets and newlines, so a call mentioned in a
  // comment is not mistaken for a real one.
  let code = source
  for (const [start, end] of commentRanges(source, file)) {
    code = code.slice(0, start) + code.slice(start, end).replace(/[^\n]/g, ' ') + code.slice(end)
  }

  for (const match of code.matchAll(CALL_PATTERN)) {
    const fn = match[1]
    const line = source.slice(0, match.index).split('\n').length
    const reference = `${file}:${line}`
    const args = splitArguments(source, match.index + match[0].length)
    const signature = SIGNATURES[fn]

    if (!args || args.length !== signature.length + 1) {
      throw new Error(`${reference}: ${fn}() needs ${signature.length + 1} arguments, ending with the '${TEXT_DOMAIN}' text domain.`)
    }

    signature.forEach((kind, position) => {
      if (kind === 'string' && !STRING_LITERAL.test(args[position])) {
        throw new Error(`${reference}: ${fn}() argument ${position + 1} must be a single string literal so it can be translated.`)
      }
      if (kind === 'string' && HTML_ENTITY.test(args[position])) {
        throw new Error(`${reference}: ${fn}() argument ${position + 1} contains an HTML entity; write the character itself.`)
      }
    })

    const domain = args.at(-1)
    if (!STRING_LITERAL.test(domain) || domain.slice(1, -1) !== TEXT_DOMAIN) {
      throw new Error(`${reference}: ${fn}() must use the '${TEXT_DOMAIN}' text domain.`)
    }

    // The plural count only selects a form, so any number stands in for it.
    const catalogueArgs = signature.map((kind, position) => kind === 'number' ? '1' : args[position])

    calls.push({
      code: `${fn}( ${[...catalogueArgs, `'${TEXT_DOMAIN}'`].join(', ')} );`,
      reference,
      translators: translatorsComment(source, match.index),
    })
  }

  return calls
}

/**
 * Collect the i18n calls of every SPA source file, in a stable order.
 *
 * @param {string}   srcDir  Directory to scan.
 * @param {string}   rootDir Plugin root; references are relative to it.
 * @param {string[]} exclude Files to skip (the i18n shim's own definitions).
 * @returns {{ code: string, reference: string, translators: string }[]}
 */
export function collectI18nCalls (srcDir, rootDir, exclude = []) {
  return readdirSync(srcDir, { recursive: true })
    .map((entry) => join(srcDir, entry))
    .filter((path) => SOURCE_EXTENSIONS.test(path) && !exclude.includes(path))
    .sort()
    .flatMap((path) => extractI18nCalls(
      readFileSync(path, 'utf8'),
      relative(rootDir, path).split(sep).join('/')
    ))
}

/**
 * Render the catalogue file make-pot reads.
 *
 * @param {{ code: string, reference: string, translators: string }[]} calls
 * @returns {string}
 */
export function renderI18nStrings (calls) {
  const lines = [
    '/**',
    ' * Translatable strings of the admin SPA, generated by `pnpm run build`.',
    ' * Do not edit: wp i18n make-pot reads this file, and the SPA\'s script',
    ' * translations are registered against its path. Each line notes its source.',
    ' */',
  ]

  for (const call of calls) {
    if (call.translators) lines.push(call.translators)
    lines.push(`${call.code} // ${call.reference}`)
  }

  return lines.join('\n') + '\n'
}

/**
 * Vite plugin: emit the catalogue into the build output.
 *
 * @param {{ srcDir: string, rootDir: string, exclude?: string[] }} options
 * @returns {import('vite').Plugin}
 */
export function wpI18nStrings ({ srcDir, rootDir, exclude = [] }) {
  return {
    name: 'wse-wp-i18n-strings',
    apply: 'build',
    generateBundle () {
      this.emitFile({
        type: 'asset',
        fileName: STRINGS_FILE_NAME,
        source: renderI18nStrings(collectI18nCalls(srcDir, rootDir, exclude)),
      })
    },
  }
}
