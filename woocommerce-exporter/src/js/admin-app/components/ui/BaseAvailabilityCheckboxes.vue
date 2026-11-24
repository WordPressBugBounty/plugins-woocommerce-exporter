<script setup>
import { __ } from '@wordpress/i18n'
import BaseCheckbox from './BaseCheckbox.vue'

defineProps({
  modelValue: { type: Object, required: true },
})
defineEmits(['update:modelValue'])
</script>

<template>
  <div class="wse-ui-availability-checkboxes">
    <BaseCheckbox
      :model-value="modelValue.quick_export"
      :label="__('Quick Export', 'woocommerce-exporter')"
      @update:model-value="v => $emit('update:modelValue', { ...modelValue, quick_export: v })"
    />
    <BaseCheckbox
      :model-value="modelValue.scheduled_export"
      :label="__('Scheduled Export', 'woocommerce-exporter')"
      @update:model-value="v => $emit('update:modelValue', { ...modelValue, scheduled_export: v })"
    />
    <BaseCheckbox
      :model-value="modelValue.export_template"
      :label="__('Export Templates', 'woocommerce-exporter')"
      @update:model-value="v => $emit('update:modelValue', { ...modelValue, export_template: v })"
    />
  </div>
</template>

<style scoped>
.wse-ui-availability-checkboxes {
  display: flex;
  flex-direction: column;
  gap: 10px;
}
</style>
