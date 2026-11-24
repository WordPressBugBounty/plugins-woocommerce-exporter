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
  dataTypeLabel?: string // dataType translated for display (export-fields endpoint)
  category?: string
  categoryLabel?: string // category translated for display (export-fields endpoint)
  custom?:   boolean
}

export interface ExportFieldGroup {
  label:  string
  display_label?: string // label translated for display; label stays the stable key
  fields: ExportField[]
}

// A registered custom meta key, listed in its dedicated Manage Fields section.
export interface CustomMetaField {
  name:        string
  definition:  string
  column_key?: string // the name the saved column order and label override hold this column under
  selected:    boolean
  available:   boolean
}

export interface ExportFieldsResponse {
  fields: ExportField[]   // flat list (export-templates endpoint)
  groups?: ExportFieldGroup[] // grouped (quick-export/fields endpoint)
  custom_meta_fields?: CustomMetaField[] // quick-export/fields endpoint
  meta_field_names?: Record<string, Record<string, string>> // quick-export/fields endpoint: prefixed definitions' field names
  field_order?: string[] // quick-export/fields endpoint: served order before meta keys were stripped
  saved_field_order?: string[] // quick-export/fields endpoint: the column order the last export ran with
  default_fields?: string[] // quick-export/fields endpoint: the effective default selection
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
  group?:       string // stable English group key
  group_label?: string // group translated for display
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
  dateLocale?:      DateLocale
  /**
   * "Reset language to English" setting is on. wp_localize_script
   * stringifies scalars, so this arrives as "1" / "".
   */
  resetLanguage?:   boolean | string
}

// The admin's language (BCP 47) and its date names from WordPress's
// $wp_locale (weekdays Sunday first).
export interface DateLocale {
  locale:        string
  months:        string[]
  monthsShort:   string[]
  weekdays:      string[]
  weekdaysShort: string[]
  meridiem:      Record<'am' | 'pm' | 'AM' | 'PM', string>
}

declare global {
  interface Window {
    wseAdminData?: WseAdminData
  }
}
