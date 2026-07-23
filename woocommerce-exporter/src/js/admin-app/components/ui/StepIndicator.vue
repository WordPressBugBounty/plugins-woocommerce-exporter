<script setup>
import { Check } from 'lucide-vue-next'

defineProps({
  steps: { type: Array, required: true },
  currentStep: { type: Number, default: 1 }
})

defineEmits(['go-to'])
</script>

<template>
  <div class="flex items-center">
    <template v-for="(step, idx) in steps" :key="idx">
      <div
        class="flex items-center gap-2 flex-shrink-0 cursor-pointer select-none group"
        role="button"
        :data-testid="`step-${step.id}`"
        :aria-current="idx + 1 === currentStep ? 'step' : undefined"
        @click="$emit('go-to', idx + 1)"
      >
        <div
          class="w-7 h-7 rounded-full border-2 inline-flex items-center justify-center flex-shrink-0 text-xs font-semibold transition-all"
          :class="idx + 1 <= currentStep
            ? 'border-indigo-600 bg-indigo-600 text-white'
            : 'border-gray-300 bg-white text-gray-500'"
        >
          <Check v-if="idx + 1 < currentStep" :size="13" :stroke-width="2.5" />
          <span v-else>{{ idx + 1 }}</span>
        </div>
        <span
          class="text-[13px] whitespace-nowrap transition-colors group-hover:text-gray-900"
          :class="{
            'text-gray-900 font-semibold': idx + 1 === currentStep,
            'text-gray-500 font-medium': idx + 1 < currentStep,
            'text-gray-500 font-normal': idx + 1 > currentStep,
          }"
        >{{ step.label }}</span>
      </div>
      <div
        v-if="idx < steps.length - 1"
        class="flex-1 h-0.5 min-w-6 mx-2 transition-all"
        :class="idx + 1 < currentStep ? 'bg-indigo-600' : 'bg-gray-200'"
      />
    </template>
  </div>
</template>
