import { ref } from 'vue'
import { defineStore } from 'pinia'
import { api } from '../lib/api'

export const useExportItemsStore = defineStore('exportItems', () => {
  const data = ref(null)
  const isLoading = ref(false)
  const error = ref(null)

  async function fetchData () {
    if (data.value) return
    isLoading.value = true
    error.value = null

    try {
      data.value = await api.get('export-items')
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Failed to load export items.'
    } finally {
      isLoading.value = false
    }
  }

  return { data, isLoading, error, fetchData }
})
