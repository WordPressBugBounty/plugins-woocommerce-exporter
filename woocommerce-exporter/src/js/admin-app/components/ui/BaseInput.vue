<script setup>
defineProps({
  modelValue: { type: String, default: '' },
  type: { type: String, default: 'text' },
  placeholder: { type: String, default: '' },
  id: { type: String, default: null },
  autocomplete: { type: String, default: 'off' },
  readonly: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false }
})

defineEmits(['update:modelValue'])
</script>

<template>
  <div
    class="wse-ui-icon-input flex items-center gap-2.5 px-3 py-[7px] border border-gray-200 rounded-lg bg-white transition-colors"
    :class="{
      'focus-within:border-indigo-500': !readonly && !disabled,
      'bg-gray-50 cursor-default': readonly || disabled,
      'opacity-60': disabled
    }"
  >
    <slot name="icon" />
    <input
      :id="id"
      :type="type"
      :value="modelValue"
      :placeholder="placeholder"
      :autocomplete="autocomplete"
      :readonly="readonly"
      :disabled="disabled"
      class="wse-ui-base-input flex-1 text-[13px] text-gray-900 placeholder:text-gray-400"
      @input="!readonly && !disabled && $emit('update:modelValue', $event.target.value)"
    />
  </div>
</template>

<style lang="scss" scoped src="./BaseInput.scss"></style>
