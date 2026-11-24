<script setup>
import { __ } from '@wordpress/i18n'
import { SlidersHorizontal, Wrench, ArrowRight } from 'lucide-vue-next'
</script>

<template>
  <div class="grid grid-cols-2 gap-4">
    <div class="flex items-center gap-3.5 py-[18px] px-5 bg-white border border-gray-200 rounded-[10px]">
      <span class="w-11 h-11 bg-indigo-50 rounded-[10px] flex items-center justify-center flex-shrink-0">
        <SlidersHorizontal :size="20" color="#6C7BFF" :stroke-width="1.75" />
      </span>
      <div class="flex-1 min-w-0">
        <h3 class="text-[13.5px] font-semibold text-gray-900 m-0 mb-[3px]">{{ __('Global Settings', 'woocommerce-exporter') }}</h3>
        <p class="text-xs text-gray-500 m-0 leading-relaxed">{{ __('Configure default export options, formats, file naming, and more.', 'woocommerce-exporter') }}</p>
      </div>
      <router-link to="/settings" class="flex items-center gap-[3px] text-[13px] font-semibold text-gray-700 no-underline whitespace-nowrap flex-shrink-0 hover:text-gray-900 hover:underline">
        {{ __('Manage', 'woocommerce-exporter') }} <ArrowRight :size="13" :stroke-width="2" />
      </router-link>
    </div>

    <div class="flex items-center gap-3.5 py-[18px] px-5 bg-white border border-gray-200 rounded-[10px]">
      <span class="w-11 h-11 bg-indigo-50 rounded-[10px] flex items-center justify-center flex-shrink-0">
        <Wrench :size="20" color="#6C7BFF" :stroke-width="1.75" />
      </span>
      <div class="flex-1 min-w-0">
        <h3 class="text-[13.5px] font-semibold text-gray-900 m-0 mb-[3px]">{{ __('System Tools', 'woocommerce-exporter') }}</h3>
        <p class="text-xs text-gray-500 m-0 leading-relaxed">{{ __('Access advanced tools like log viewer, cleanup, and diagnostics.', 'woocommerce-exporter') }}</p>
      </div>
      <router-link to="/tools" class="flex items-center gap-[3px] text-[13px] font-semibold text-gray-700 no-underline whitespace-nowrap flex-shrink-0 hover:text-gray-900 hover:underline">
        {{ __('Open Tools', 'woocommerce-exporter') }} <ArrowRight :size="13" :stroke-width="2" />
      </router-link>
    </div>
  </div>
</template>
