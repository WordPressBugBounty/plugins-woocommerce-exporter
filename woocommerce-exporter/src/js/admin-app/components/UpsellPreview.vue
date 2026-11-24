<script setup>
/**
 * UpsellPreview — a static, non-interactive facsimile of a Deluxe screen.
 *
 * Rendered (blurred + dimmed) behind the UpsellView lock card so free users
 * see roughly what the premium feature looks like. This is real markup, not a
 * screenshot, so it reflows with the viewport. It is purely decorative:
 * aria-hidden and pointer-events-none are applied by the parent, and the
 * sample data below is illustrative only.
 *
 * @since 2.7.10
 */
import { __ } from '@wordpress/i18n'

defineProps({
  feature: { type: String, required: true },
})

const schedules = [
  { name: __('Nightly Orders', 'woocommerce-exporter'), freq: __('Daily · 02:00', 'woocommerce-exporter'), next: __('in 6h', 'woocommerce-exporter'), last: __('Yesterday', 'woocommerce-exporter'), format: 'CSV', status: __('Active', 'woocommerce-exporter'), active: true },
  { name: __('Weekly Catalog', 'woocommerce-exporter'), freq: __('Mon · 06:00', 'woocommerce-exporter'), next: __('in 3d', 'woocommerce-exporter'), last: __('Last Mon', 'woocommerce-exporter'), format: 'XLSX', status: __('Active', 'woocommerce-exporter'), active: true },
  { name: __('Monthly Customers', 'woocommerce-exporter'), freq: __('1st · 00:00', 'woocommerce-exporter'), next: __('in 12d', 'woocommerce-exporter'), last: __('1 Jul', 'woocommerce-exporter'), format: 'CSV', status: __('Paused', 'woocommerce-exporter'), active: false },
]

const starters = [
  { title: __('Product Catalog Export', 'woocommerce-exporter'), meta: __('Products · CSV · 30+ fields', 'woocommerce-exporter') },
  { title: __('Orders Backup', 'woocommerce-exporter'), meta: __('Orders · CSV · 20+ fields', 'woocommerce-exporter') },
  { title: __('Customer Export', 'woocommerce-exporter'), meta: __('Customers · CSV · 15+ fields', 'woocommerce-exporter') },
]

const archives = [
  { file: 'orders-2026-07-14.csv', type: __('Orders', 'woocommerce-exporter'), size: '2.1 MB', when: __('Yesterday', 'woocommerce-exporter') },
  { file: 'products-2026-07-13.xlsx', type: __('Products', 'woocommerce-exporter'), size: '860 KB', when: __('2 days ago', 'woocommerce-exporter') },
  { file: 'customers-2026-07-01.csv', type: __('Customers', 'woocommerce-exporter'), size: '1.3 MB', when: __('1 Jul', 'woocommerce-exporter') },
]

const audit = [
  { user: 'admin', action: __('Exported', 'woocommerce-exporter'), type: __('Orders', 'woocommerce-exporter'), rows: '9,207', when: __('2 min ago', 'woocommerce-exporter') },
  { user: 'accounts', action: __('Downloaded', 'woocommerce-exporter'), type: __('Products', 'woocommerce-exporter'), rows: '2,481', when: __('1 hr ago', 'woocommerce-exporter') },
  { user: 'warehouse', action: __('Scheduled run', 'woocommerce-exporter'), type: __('Customers', 'woocommerce-exporter'), rows: '3,644', when: __('Yesterday', 'woocommerce-exporter') },
]
</script>

<template>
  <div class="wse-upsell-preview p-6 bg-gray-50 text-gray-900 w-full" data-testid="upsell-preview">

    <!-- Faux toolbar shared by the list-style screens -->
    <div class="flex flex-wrap items-center gap-3 mb-4">
      <div class="flex-1 min-w-[180px] h-9 rounded-lg border border-gray-200 bg-white flex items-center px-3 text-gray-400 text-[13px]">{{ __('Search…', 'woocommerce-exporter') }}</div>
      <div class="h-9 px-3 rounded-lg border border-gray-200 bg-white flex items-center text-[13px] text-gray-600">{{ __('Status: All', 'woocommerce-exporter') }}</div>
      <div class="h-9 px-3 rounded-lg border border-gray-200 bg-white flex items-center text-[13px] text-gray-600">{{ __('All Types', 'woocommerce-exporter') }}</div>
      <div class="h-9 px-3 rounded-lg border border-gray-200 bg-white flex items-center text-[13px] text-gray-600">{{ __('Clear', 'woocommerce-exporter') }}</div>
    </div>

    <!-- Scheduled Exports -->
    <div v-if="feature === 'scheduled_exports'" class="rounded-xl border border-gray-200 bg-white overflow-x-auto">
      <table class="w-full text-left text-[13px]">
        <thead>
          <tr class="text-[11px] uppercase tracking-wide text-gray-500 border-b border-gray-200">
            <th class="font-semibold px-4 py-3">{{ __('Schedule', 'woocommerce-exporter') }}</th>
            <th class="font-semibold px-4 py-3">{{ __('Frequency', 'woocommerce-exporter') }}</th>
            <th class="font-semibold px-4 py-3">{{ __('Next run', 'woocommerce-exporter') }}</th>
            <th class="font-semibold px-4 py-3">{{ __('Format', 'woocommerce-exporter') }}</th>
            <th class="font-semibold px-4 py-3">{{ __('Status', 'woocommerce-exporter') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="s in schedules" :key="s.name" class="border-b border-gray-100 last:border-0">
            <td class="px-4 py-3 font-medium text-gray-800">{{ s.name }}</td>
            <td class="px-4 py-3 text-gray-600">{{ s.freq }}</td>
            <td class="px-4 py-3 text-gray-600">{{ s.next }}</td>
            <td class="px-4 py-3"><span class="font-mono text-[11px] text-indigo-700 bg-indigo-50 rounded px-1.5 py-0.5">{{ s.format }}</span></td>
            <td class="px-4 py-3">
              <span class="text-[11px] font-semibold rounded-full px-2 py-0.5"
                :class="s.active ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'">{{ s.status }}</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Export Templates -->
    <div v-else-if="feature === 'export_templates'">
      <div class="text-[13px] font-semibold text-gray-700 mb-3">{{ __('Starter Templates', 'woocommerce-exporter') }}</div>
      <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
        <div v-for="t in starters" :key="t.title" class="rounded-xl border border-gray-200 bg-white p-4">
          <div class="w-9 h-9 rounded-lg bg-indigo-50 mb-3"></div>
          <div class="font-semibold text-[14px] text-gray-800">{{ t.title }}</div>
          <div class="text-[12px] text-gray-500 mt-2 leading-relaxed">{{ __('Reusable field & format preset.', 'woocommerce-exporter') }}</div>
          <div class="text-[11px] text-gray-400 mt-3">{{ t.meta }}</div>
          <div class="mt-3 h-8 rounded-lg border border-indigo-200 text-indigo-600 text-[12px] font-semibold flex items-center justify-center">{{ __('Use Template', 'woocommerce-exporter') }}</div>
        </div>
      </div>
    </div>

    <!-- Archives -->
    <div v-else-if="feature === 'archives'" class="rounded-xl border border-gray-200 bg-white overflow-x-auto">
      <table class="w-full text-left text-[13px]">
        <thead>
          <tr class="text-[11px] uppercase tracking-wide text-gray-500 border-b border-gray-200">
            <th class="font-semibold px-4 py-3">{{ __('File', 'woocommerce-exporter') }}</th>
            <th class="font-semibold px-4 py-3">{{ __('Type', 'woocommerce-exporter') }}</th>
            <th class="font-semibold px-4 py-3">{{ __('Size', 'woocommerce-exporter') }}</th>
            <th class="font-semibold px-4 py-3">{{ __('Generated', 'woocommerce-exporter') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="a in archives" :key="a.file" class="border-b border-gray-100 last:border-0">
            <td class="px-4 py-3 font-mono text-[12px] text-gray-800">{{ a.file }}</td>
            <td class="px-4 py-3 text-gray-600">{{ a.type }}</td>
            <td class="px-4 py-3 text-gray-600">{{ a.size }}</td>
            <td class="px-4 py-3 text-gray-600">{{ a.when }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Audit Log -->
    <div v-else-if="feature === 'audit_log'" class="rounded-xl border border-gray-200 bg-white overflow-x-auto">
      <table class="w-full text-left text-[13px]">
        <thead>
          <tr class="text-[11px] uppercase tracking-wide text-gray-500 border-b border-gray-200">
            <th class="font-semibold px-4 py-3">{{ __('User', 'woocommerce-exporter') }}</th>
            <th class="font-semibold px-4 py-3">{{ __('Action', 'woocommerce-exporter') }}</th>
            <th class="font-semibold px-4 py-3">{{ __('Type', 'woocommerce-exporter') }}</th>
            <th class="font-semibold px-4 py-3">{{ __('Rows', 'woocommerce-exporter') }}</th>
            <th class="font-semibold px-4 py-3">{{ __('When', 'woocommerce-exporter') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="a in audit" :key="a.user + a.when" class="border-b border-gray-100 last:border-0">
            <td class="px-4 py-3 font-medium text-gray-800">{{ a.user }}</td>
            <td class="px-4 py-3 text-gray-600">{{ a.action }}</td>
            <td class="px-4 py-3 text-gray-600">{{ a.type }}</td>
            <td class="px-4 py-3 font-mono text-[12px] text-gray-600">{{ a.rows }}</td>
            <td class="px-4 py-3 text-gray-600">{{ a.when }}</td>
          </tr>
        </tbody>
      </table>
    </div>

  </div>
</template>
