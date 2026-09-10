<script setup>
import AppSidebar from './components/AppSidebar.vue'
import DebugModeBanner from './components/DebugModeBanner.vue'
import ToastContainer from './components/ToastContainer.vue'
import UpsellModal from './components/UpsellModal.vue'
import { RouterView } from 'vue-router'
</script>

<template>
  <div class="wse-app flex flex-col m-0 bg-gray-50 min-h-[calc(100vh-32px)] font-[-apple-system,BlinkMacSystemFont,'Segoe_UI',Roboto,Oxygen,Ubuntu,sans-serif] text-[13px] text-gray-900">
    <DebugModeBanner />
    <div class="flex items-stretch flex-1">
      <AppSidebar />
      <RouterView />
    </div>
    <ToastContainer />
    <UpsellModal />
  </div>
</template>
