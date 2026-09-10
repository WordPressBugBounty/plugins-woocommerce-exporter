/**
 * Export transport layer — AJAX calls for the quick-export process.
 *
 * Separates HTTP transport from the Pinia store so the store owns state only.
 * All window.wseAdminData access is centralised here.
 *
 * @since 5.8.2 (JS) / 5.8.3 (TypeScript)
 */

import type { WseAdminData } from './types'

function wpData (): Partial<WseAdminData> {
  return window.wseAdminData ?? {}
}

function ajaxUrl (): string {
  return wpData().ajaxUrl ?? '/wp-admin/admin-ajax.php'
}

/** One label/value row of the debug-mode gate's diagnostics payload. */
export interface ExportDiagnostic {
  label: string
  value: string
}

/**
 * Thrown when the server rejects a request. Carries the `premium`/`upsell`/
 * `feature` fields the AJAX gates attach to premium (Deluxe-only) export
 * type/format rejections, so callers can surface an upsell instead of
 * generic error text, plus the `debug`/`diagnostics` fields the debugging-mode
 * gate attaches so the progress dialog can explain the refusal.
 */
export class ExportApiError extends Error {
  premium: boolean
  upsell: string | null
  feature: string | null
  debug: boolean
  diagnostics: ExportDiagnostic[]

  constructor (
    message: string,
    premium = false,
    upsell: string | null = null,
    feature: string | null = null,
    debug = false,
    diagnostics: ExportDiagnostic[] = []
  ) {
    super(message)
    this.name = 'ExportApiError'
    this.premium = premium
    this.upsell = upsell
    this.feature = feature
    this.debug = debug
    this.diagnostics = diagnostics
  }
}

interface PrepareExportParams {
  type:     string
  nonce:    string
  formData: string
}

interface PrepareExportResult {
  total:   number
  batches: number[]
}

/**
 * Call the prepare step — creates the temp file and returns total/batch count.
 */
export async function prepareExport ({ type, nonce, formData }: PrepareExportParams): Promise<PrepareExportResult> {
  const body = new URLSearchParams({
    action:    'woo_ce_quick_export',
    nonce,
    type,
    method:    'prepare',
    form_data: formData,
  })

  const res  = await fetch(ajaxUrl(), {
    method:  'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body:    body.toString(),
  })
  const data = await res.json() as { success: boolean; data: PrepareExportResult & { message?: string; premium?: boolean; upsell?: string; feature?: string; debug?: boolean; diagnostics?: ExportDiagnostic[] } }

  if (!res.ok || data.success === false) {
    throw new ExportApiError(data.data?.message ?? 'Export prepare failed.', Boolean(data.data?.premium), data.data?.upsell ?? null, data.data?.feature ?? null, Boolean(data.data?.debug), data.data?.diagnostics ?? [])
  }

  return data.data
}

interface ProcessBatchParams {
  type:  string
  nonce: string
  step:  number
}

interface ProcessBatchResult {
  exported: number
  done:     boolean
  file_url?: string
  message?: string
  premium?: boolean
  upsell?: string
  feature?: string
  debug?: boolean
  diagnostics?: ExportDiagnostic[]
}

/**
 * Process one export batch.
 */
export async function processBatch ({ type, nonce, step }: ProcessBatchParams): Promise<ProcessBatchResult> {
  const body = new URLSearchParams({
    action: 'woo_ce_quick_export',
    nonce,
    type,
    method: 'batch',
    step:   String(step),
  })

  const res  = await fetch(ajaxUrl(), {
    method:  'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body:    body.toString(),
  })
  const data = await res.json() as { success: boolean; data: ProcessBatchResult }

  if (!res.ok || data.success === false) {
    throw new ExportApiError(data.data?.message ?? `Batch ${step} failed.`, Boolean(data.data?.premium), data.data?.upsell ?? null, data.data?.feature ?? null, Boolean(data.data?.debug), data.data?.diagnostics ?? [])
  }

  return data.data
}

/**
 * Return the nonce for the manual_export AJAX action.
 */
export function getExportNonce (): string {
  return wpData().quickExportNonce ?? ''
}
