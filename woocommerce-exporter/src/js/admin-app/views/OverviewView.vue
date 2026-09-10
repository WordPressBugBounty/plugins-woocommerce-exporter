<script setup>
import { onMounted } from 'vue'
import { storeToRefs } from 'pinia'
import { AlertCircle } from 'lucide-vue-next'
import { useOverviewStore } from '../stores/overview'
import PageActions from '../components/PageActions.vue'
import QuickExportSection from '../components/QuickExportSection.vue'
import BottomLinks from '../components/BottomLinks.vue'
import PageFooter from '../components/PageFooter.vue'

const store = useOverviewStore()
const { data, isLoading, error } = storeToRefs(store)

onMounted(store.fetchData)
</script>

<template>
  <main class="wse-main" data-testid="app-main">
    <!-- Page header -->
    <div class="wse-header">
      <div>
        <h1 class="wse-page-title">Overview</h1>
        <p class="wse-page-sub">Central hub for managing your export operations.</p>
      </div>
      <PageActions />
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="wse-loading">
      <span class="wse-spinner" />
      Loading overview&hellip;
    </div>

    <!-- Error -->
    <div v-else-if="error" class="wse-error">
      <AlertCircle :size="16" color="#DC2626" :stroke-width="1.75" />
      {{ error }}
    </div>

    <!-- Content -->
    <template v-else-if="data">
      <QuickExportSection :export-url="data.urls?.export ?? '#'" />

      <BottomLinks />

      <PageFooter :plugin="data.plugin" />
    </template>
  </main>
</template>
