<script setup>
/**
 * BaseSelect — unified select primitive.
 *
 * Consolidates filter bar and form field select variants into one component.
 *
 * variant="filter" → filter toolbar style (h-[40px], rounded-[6px], optional
 *                    label + leading icon + clear button; chevron via CSS
 *                    background-image to avoid WP admin SVG interference)
 * variant="form"   → form field style (wse-form-select CSS class, h-[44px],
 *                    CSS background-image chevron, full-width by default)
 */
import { computed, useAttrs } from 'vue'

defineOptions({ inheritAttrs: false })

const props = defineProps({
  modelValue: { type: String, default: '' },
  options:    { type: Array,  default: () => [] }, // [{ value, label }]
  variant:    { type: String, default: 'filter' }, // 'filter' | 'form'
  // filter variant only
  label:        { type: String,  default: '' },
  leadingIcon:  { type: Object,  default: null },
  clearable:    { type: Boolean, default: false },
  defaultValue: { type: String,  default: '' },
  // form variant only
  hasError: { type: Boolean, default: false },
  size:     { type: String,  default: 'md' }, // 'md' | 'sm'
  id:       { type: String,  default: null },
})

const emit = defineEmits(['update:modelValue'])

const attrs = useAttrs()
// class/style keep falling through to whichever element was the component
// root pre-inheritAttrs:false (the wrapper div for "filter", the <select>
// itself for "form"); everything else (e.g. data-testid) always targets the
// actual <select> so tests can address the real control in both variants.
const selectAttrs = computed(() => {
  const { class: _class, style: _style, ...rest } = attrs
  return rest
})

const showClear = computed(() => props.clearable && props.modelValue !== props.defaultValue)
function clear (e) {
  e.stopPropagation()
  emit('update:modelValue', props.defaultValue)
}
function onChange (e) {
  emit('update:modelValue', e.target.value)
  e.target.blur()
}
</script>

<template>
  <!-- Filter variant -->
  <div v-if="variant === 'filter'" class="wse-ui-filter-select flex flex-col gap-1.5" :class="attrs.class" :style="attrs.style">
    <p v-if="label" class="text-[11px] font-semibold uppercase tracking-[0.08em] text-gray-500 m-0">{{ label }}</p>
    <div class="relative">
      <component
        :is="leadingIcon"
        v-if="leadingIcon"
        :size="16"
        :stroke-width="1.75"
        class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
        color="#6C7BFF"
      />
      <select
        :id="id"
        :value="modelValue"
        class="wse-filter-select"
        :style="{
          paddingLeft: leadingIcon ? '38px' : '12px',
          paddingRight: showClear ? '76px' : '36px',
          backgroundPosition: showClear ? 'right 52px center' : 'right 12px center',
        }"
        v-bind="selectAttrs"
        @change="onChange"
      >
        <option v-for="opt in options" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
      </select>
      <div v-if="showClear" class="absolute right-0 inset-y-0 flex items-center gap-1.5 pr-3 pointer-events-none">
        <button
          type="button"
          class="pointer-events-auto text-[12px] text-gray-400 hover:text-gray-700 transition-colors cursor-pointer bg-transparent border-none p-0 leading-none"
          @click="clear"
        >Clear</button>
        <span class="w-px h-3 bg-gray-200" />
      </div>
    </div>
  </div>

  <!-- Form variant -->
  <select
    v-else
    :id="id"
    :value="modelValue"
    class="wse-form-select cursor-pointer"
    :class="[{ 'wse-form-select--error': hasError, 'wse-form-select--sm': size === 'sm' }, attrs.class]"
    :style="attrs.style"
    v-bind="selectAttrs"
    @change="onChange"
  >
    <option v-for="opt in options" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
  </select>
</template>
