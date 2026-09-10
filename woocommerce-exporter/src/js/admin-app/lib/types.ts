/**
 * REST API response types for wse/v1/ endpoints.
 *
 * These are the shapes returned by the PHP REST controllers. Keep them in
 * sync with the controller return values. Add new types here as new
 * endpoints are created — never inline response shapes in stores.
 *
 * @since 5.8.3
 */

// ── Shared primitives ────────────────────────────────────────────────────────

export interface SelectOption {
  value: string
  label: string
}

// ── Export types ─────────────────────────────────────────────────────────────

export interface ExportType {
  slug:    string
  label:   string
  records: number
}

export interface ExportFormat {
  value: string
  label: string
}

export interface ExportTemplate {
  id:     number
  label:  string
  type?:  string
  format?: string
  fields?: TemplateField[]
}

export interface TemplateField {
  name:   string
  label:  string
  order?: number
}

// ── Fields ───────────────────────────────────────────────────────────────────

export interface ExportField {
  name:     string
  label:    string
  selected: boolean
  dataType?: string
  category?: string
  custom?:   boolean
}

export interface ExportFieldGroup {
  label:  string
  fields: ExportField[]
}

export interface ExportFieldsResponse {
  fields: ExportField[]   // flat list (export-templates endpoint)
  groups?: ExportFieldGroup[] // grouped (quick-export/fields endpoint)
}

// ── Filters ──────────────────────────────────────────────────────────────────

export interface FilterSubOption {
  name:     string
  type:     'multiselect' | 'date_range' | 'text' | 'number'
  label?:   string
  options?: SelectOption[]
}

export interface Filter {
  key:         string
  label:       string
  icon?:       string
  description?: string
  sub_options?: FilterSubOption[]
}

export interface SortConfig {
  options: SelectOption[]
}

export interface ExtraOption {
  key:         string
  label:       string
  description?: string
  value:       string | number
  options:     Array<{ value: string | number; label: string }>
}

export interface CustomMetaDef {
  name:         string
  label:        string
  description?: string
  value?:       string
}

export interface FiltersResponse {
  filters:      Filter[]
  sort:         SortConfig | null
  custom_meta?: CustomMetaDef[]
  extra_options?: ExtraOption[]
  type_label?:  string
}

// ── Quick export config ───────────────────────────────────────────────────────

export interface QuickExportConfig {
  types:      ExportType[]
  formats:    ExportFormat[]
  templates:  ExportTemplate[]
  dateFormat?: string
}

// ── Archives ─────────────────────────────────────────────────────────────────

export interface Archive {
  id:          number
  filename:    string
  size:        string
  type:        string
  created_at:  string
  download_url: string
}

// ── Scheduled exports ────────────────────────────────────────────────────────

export interface ScheduledExport {
  id:          number
  title:       string
  status:      string
  frequency:   string
  next_run?:   string
  last_run?:   string
  export_type?: string
}

// ── Settings ─────────────────────────────────────────────────────────────────

export type SettingsRecord = Record<string, string | number | boolean | null>

// ── License ──────────────────────────────────────────────────────────────────

export interface LicenseData {
  email:            string
  key:              string
  status:           'active' | 'inactive' | 'expired' | string
  expiredDate?:     string
  activateNonce:    string
  deactivateNonce:  string
  isMultisite:      boolean
}

// ── Overview ─────────────────────────────────────────────────────────────────

export interface OverviewData {
  plugin:    { version: string; name: string }
  recent_exports?: Archive[]
  recent_schedules?: ScheduledExport[]
}

// ── Tools ────────────────────────────────────────────────────────────────────

export interface ToolResult {
  success: boolean
  message: string
}

// ── wp_localize_script global ─────────────────────────────────────────────────

export interface WseAdminData {
  restUrl:          string
  nonce:            string
  initialRoute:     string
  ajaxUrl:          string
  quickExportNonce: string
  licenseUrl:       string
  exportTypes:      SelectOption[]
  licenseData:      LicenseData
  /**
   * Whether the plugin is in debugging mode, seeded from the WOO_CE_DEBUG
   * constant (never the raw option — a site may define the constant in
   * wp-config.php, so the two can disagree). wp_localize_script stringifies
   * scalars, so this arrives as "1" / "" rather than a real boolean.
   */
  debugMode:        boolean | string
}

declare global {
  interface Window {
    wseAdminData?: WseAdminData
  }
}
