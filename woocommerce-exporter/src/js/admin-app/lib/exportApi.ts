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
  const data = await res.json() as { success: boolean; data: PrepareExportResult & { message?: string } }

  if (!res.ok || data.success === false) {
    throw new Error(data.data?.message ?? 'Export prepare failed.')
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
    throw new Error(data.data?.message ?? `Batch ${step} failed.`)
  }

  return data.data
}

/**
 * Return the nonce for the manual_export AJAX action.
 */
export function getExportNonce (): string {
  return wpData().quickExportNonce ?? ''
}
