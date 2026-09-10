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
defineProps({
  feature: { type: String, required: true },
})

const schedules = [
  { name: 'Nightly Orders', freq: 'Daily · 02:00', next: 'in 6h', last: 'Yesterday', format: 'CSV', status: 'Active' },
  { name: 'Weekly Catalog', freq: 'Mon · 06:00', next: 'in 3d', last: 'Last Mon', format: 'XLSX', status: 'Active' },
  { name: 'Monthly Customers', freq: '1st · 00:00', next: 'in 12d', last: '1 Jul', format: 'CSV', status: 'Paused' },
]

const starters = [
  { title: 'Product Catalog Export', meta: 'Products · CSV · 30+ fields' },
  { title: 'Orders Backup', meta: 'Orders · CSV · 20+ fields' },
  { title: 'Customer Export', meta: 'Customers · CSV · 15+ fields' },
]

const archives = [
  { file: 'orders-2026-07-14.csv', type: 'Orders', size: '2.1 MB', when: 'Yesterday' },
  { file: 'products-2026-07-13.xlsx', type: 'Products', size: '860 KB', when: '2 days ago' },
  { file: 'customers-2026-07-01.csv', type: 'Customers', size: '1.3 MB', when: '1 Jul' },
]

const audit = [
  { user: 'admin', action: 'Exported', type: 'Orders', rows: '9,207', when: '2 min ago' },
  { user: 'accounts', action: 'Downloaded', type: 'Products', rows: '2,481', when: '1 hr ago' },
  { user: 'warehouse', action: 'Scheduled run', type: 'Customers', rows: '3,644', when: 'Yesterday' },
]
</script>

<template>
  <div class="wse-upsell-preview p-6 bg-gray-50 text-gray-900 w-full" data-testid="upsell-preview">

    <!-- Faux toolbar shared by the list-style screens -->
    <div class="flex flex-wrap items-center gap-3 mb-4">
      <div class="flex-1 min-w-[180px] h-9 rounded-lg border border-gray-200 bg-white flex items-center px-3 text-gray-400 text-[13px]">Search…</div>
      <div class="h-9 px-3 rounded-lg border border-gray-200 bg-white flex items-center text-[13px] text-gray-600">Status: All</div>
      <div class="h-9 px-3 rounded-lg border border-gray-200 bg-white flex items-center text-[13px] text-gray-600">All Types</div>
      <div class="h-9 px-3 rounded-lg border border-gray-200 bg-white flex items-center text-[13px] text-gray-600">Clear</div>
    </div>

    <!-- Scheduled Exports -->
    <div v-if="feature === 'scheduled_exports'" class="rounded-xl border border-gray-200 bg-white overflow-x-auto">
      <table class="w-full text-left text-[13px]">
        <thead>
          <tr class="text-[11px] uppercase tracking-wide text-gray-500 border-b border-gray-200">
            <th class="font-semibold px-4 py-3">Schedule</th>
            <th class="font-semibold px-4 py-3">Frequency</th>
            <th class="font-semibold px-4 py-3">Next run</th>
            <th class="font-semibold px-4 py-3">Format</th>
            <th class="font-semibold px-4 py-3">Status</th>
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
                :class="s.status === 'Active' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'">{{ s.status }}</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Export Templates -->
    <div v-else-if="feature === 'export_templates'">
      <div class="text-[13px] font-semibold text-gray-700 mb-3">Starter Templates</div>
      <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
        <div v-for="t in starters" :key="t.title" class="rounded-xl border border-gray-200 bg-white p-4">
          <div class="w-9 h-9 rounded-lg bg-indigo-50 mb-3"></div>
          <div class="font-semibold text-[14px] text-gray-800">{{ t.title }}</div>
          <div class="text-[12px] text-gray-500 mt-2 leading-relaxed">Reusable field &amp; format preset.</div>
          <div class="text-[11px] text-gray-400 mt-3">{{ t.meta }}</div>
          <div class="mt-3 h-8 rounded-lg border border-indigo-200 text-indigo-600 text-[12px] font-semibold flex items-center justify-center">Use Template</div>
        </div>
      </div>
    </div>

    <!-- Archives -->
    <div v-else-if="feature === 'archives'" class="rounded-xl border border-gray-200 bg-white overflow-x-auto">
      <table class="w-full text-left text-[13px]">
        <thead>
          <tr class="text-[11px] uppercase tracking-wide text-gray-500 border-b border-gray-200">
            <th class="font-semibold px-4 py-3">File</th>
            <th class="font-semibold px-4 py-3">Type</th>
            <th class="font-semibold px-4 py-3">Size</th>
            <th class="font-semibold px-4 py-3">Generated</th>
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
            <th class="font-semibold px-4 py-3">User</th>
            <th class="font-semibold px-4 py-3">Action</th>
            <th class="font-semibold px-4 py-3">Type</th>
            <th class="font-semibold px-4 py-3">Rows</th>
            <th class="font-semibold px-4 py-3">When</th>
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
