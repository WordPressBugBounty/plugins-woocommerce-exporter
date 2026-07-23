// Static lookup tables for QuickExportView — extracted to keep the SFC focused
// on UI logic. No reactive state lives here; everything is pure data + helpers.

import {
  FileText, FileSpreadsheet, FileCode2, Folder, Code2, Tag, Tags, Paperclip,
  Link2, Box, CheckCircle2, Calendar, CalendarClock, CreditCard, Users,
  Filter as FilterIcon, Settings2, Hash, Barcode, Type, GitBranch, Copy,
  TrendingUp, Shuffle, Image, Images, LayoutGrid, Rows3
} from 'lucide-vue-next'

// Lucide icon name → component map for the dynamic filter cards.
const FILTER_ICON_MAP = {
  Box, CheckCircle2, Tag, Tags, Calendar, CalendarClock, CreditCard, Users,
  Filter: FilterIcon
}

export function filterIcon (name) {
  return FILTER_ICON_MAP[name] ?? FilterIcon
}

// Soft tinted backgrounds for the filter card icon badges.
const FILTER_ICON_TINTS = {
  Box:           { bg: '#EFF1FF', color: '#6C7BFF' },
  CheckCircle2:  { bg: '#DCFCE7', color: '#16A34A' },
  Tag:           { bg: '#EFF1FF', color: '#6C7BFF' },
  Tags:          { bg: '#EDE9FE', color: '#7C3AED' },
  Calendar:      { bg: '#FEE2E2', color: '#DC2626' },
  CalendarClock: { bg: '#FFEDD5', color: '#EA580C' },
  CreditCard:    { bg: '#E1E5FF', color: '#5462E6' },
  Users:         { bg: '#EFF1FF', color: '#6C7BFF' },
  Filter:        { bg: '#F3F4F6', color: '#6B7280' }
}

export function filterIconTint (name) {
  return FILTER_ICON_TINTS[name] ?? FILTER_ICON_TINTS.Filter
}

// Visual mapping for the file-format cards (Step 4 — Export Options).
const FORMAT_VISUALS = {
  csv:  { icon: FileSpreadsheet, badge: 'CSV' },
  tsv:  { icon: FileText,        badge: ''    },
  xls:  { icon: FileSpreadsheet, badge: 'X'   },
  xlsx: { icon: FileSpreadsheet, badge: 'X'   },
  xml:  { icon: FileCode2,       badge: ''    }
}

export function formatVisual (value) {
  return FORMAT_VISUALS[String(value).toLowerCase()] ?? { icon: FileText, badge: '' }
}

// Icons + descriptions for the card-style radio rows in the extra-options sections.
const EXTRA_OPTION_ICONS = {
  product_grouped_formatting:  { 0: Hash,      1: Barcode,    2: Type       },
  product_upsell_formatting:   { 0: Hash,      1: Barcode                   },
  product_crosssell_formatting:{ 0: Hash,      1: Barcode                   },
  variation_formatting:        { 0: Copy,      1: GitBranch                 },
  description_excerpt_formatting: { 0: Code2,  1: Tag                       },
  product_image_formatting:    { 0: Paperclip, 1: Link2,      2: Folder     },
  product_gallery_formatting:  { 0: Paperclip, 1: Link2,      2: Folder     },
  product_gallery_unique:      { 0: LayoutGrid, 1: Rows3                    },
}

const EXTRA_OPTION_DESCRIPTIONS = {
  product_grouped_formatting: {
    0: 'Export the WordPress post ID of each grouped product.',
    1: 'Export the SKU of each grouped product.',
    2: 'Export the product name of each grouped product.'
  },
  product_upsell_formatting: {
    0: 'Export the WordPress post ID of each up-sell product.',
    1: 'Export the SKU of each up-sell product.'
  },
  product_crosssell_formatting: {
    0: 'Export the WordPress post ID of each cross-sell product.',
    1: 'Export the SKU of each cross-sell product.'
  },
  variation_formatting: {
    0: 'Empty variation fields will remain blank in the export.',
    1: 'Empty variation fields will inherit the parent product value.'
  },
  description_excerpt_formatting: {
    0: 'HTML tags will be included as-is in the export.',
    1: 'HTML tags will be removed and only plain text will be exported.'
  },
  product_image_formatting: {
    0: 'Export the WordPress attachment ID.',
    1: 'Export the image URL.',
    2: 'Export the server filepath.'
  },
  product_gallery_formatting: {
    0: 'Export each gallery image as its WordPress attachment ID.',
    1: 'Export each gallery image as a full URL.',
    2: 'Export each gallery image as the server filepath.'
  },
  product_gallery_unique: {
    0: 'All gallery images combined into one pipe-separated cell.',
    1: 'Each gallery image in its own dedicated column.'
  }
}

export function extraOptionIcon (optionKey, choiceValue) {
  const map = EXTRA_OPTION_ICONS[optionKey]
  if (!map) return Settings2
  return map[String(choiceValue)] ?? Settings2
}

export function extraOptionDescription (optionKey, choiceValue) {
  return EXTRA_OPTION_DESCRIPTIONS[optionKey]?.[String(choiceValue)] ?? ''
}

// Predefined limit choices for the Batch Settings "Limit" select.
export const LIMIT_PRESETS = [
  { value: '',      label: 'All Records' },
  { value: '100',   label: '100' },
  { value: '500',   label: '500' },
  { value: '1000',  label: '1,000' },
  { value: '5000',  label: '5,000' },
  { value: '10000', label: '10,000' },
  { value: '50000', label: '50,000' }
]

export const MONTHS_SHORT = [
  'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
  'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
]
