<script setup>
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
      label="Quick Export"
      @update:model-value="v => $emit('update:modelValue', { ...modelValue, quick_export: v })"
    />
    <BaseCheckbox
      :model-value="modelValue.scheduled_export"
      label="Scheduled Export"
      @update:model-value="v => $emit('update:modelValue', { ...modelValue, scheduled_export: v })"
    />
    <BaseCheckbox
      :model-value="modelValue.export_template"
      label="Export Templates"
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
