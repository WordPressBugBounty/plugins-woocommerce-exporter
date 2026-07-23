import { ref } from 'vue'
import { defineStore } from 'pinia'

/**
 * Global upsell-modal state.
 *
 * Any premium (Deluxe-only) export type routes here instead of navigating away:
 * the three type lists (Quick Export picker, Export Items grid + list) call
 * open() with the type's label/description/upgrade URL, and a single
 * <UpsellModal /> mounted in App.vue renders the pitch.
 */
export const useUpsellStore = defineStore('upsell', () => {
  const isOpen = ref(false)
  const feature = ref('')
  const description = ref('')
  const url = ref('')

  function open ({ label = '', description: desc = '', url: upgradeUrl = '' } = {}) {
    feature.value = label
    description.value = desc
    url.value = upgradeUrl
    isOpen.value = true
  }

  function close () {
    isOpen.value = false
  }

  return { isOpen, feature, description, url, open, close }
})
