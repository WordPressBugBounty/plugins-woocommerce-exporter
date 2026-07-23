import { defineStore } from 'pinia'
import { ref } from 'vue'
import { api } from '../lib/api'

function defaultAvailability () {
  return { quick_export: true, scheduled_export: true, export_template: true }
}

export const useCustomMetaFieldsStore = defineStore('customMetaFields', () => {
  // ── List state ─────────────────────────────────────────────────────────────
  const types = ref([])
  const isLoadingList = ref(false)
  const listError = ref(null)

  // ── Form state ─────────────────────────────────────────────────────────────
  // Each entry: { name, label, description, metaKeysText, availability }
  const currentType = ref(null)
  const fieldDefinitions = ref([])
  const isLoadingForm = ref(false)
  const isSaving = ref(false)
  const formError = ref(null)

  // ── Actions ─────────────────────────────────────────────────────────────────

  async function fetchTypes () {
    isLoadingList.value = true
    listError.value = null
    try {
      const data = await api.get('custom-meta-fields')
      types.value = data.types ?? []
    } catch (err) {
      listError.value = err.message
    } finally {
      isLoadingList.value = false
    }
  }

  async function fetchType (type) {
    isLoadingForm.value = true
    formError.value = null
    currentType.value = null
    fieldDefinitions.value = []
    try {
      const data = await api.get(`custom-meta-fields/${type}`)
      currentType.value = data
      fieldDefinitions.value = (data.field_definitions ?? []).map(def => ({
        name: def.name,
        label: def.label,
        description: def.description ?? '',
        metaKeysText: (def.meta_keys ?? []).join('\n'),
        availability: {
          quick_export: def.availability?.quick_export ?? true,
          scheduled_export: def.availability?.scheduled_export ?? true,
          export_template: def.availability?.export_template ?? true,
        },
      }))
    } catch (err) {
      formError.value = err.message
    } finally {
      isLoadingForm.value = false
    }
  }

  async function saveType (type) {
    isSaving.value = true
    formError.value = null
    try {
      const data = await api.put(`custom-meta-fields/${type}`, {
        field_definitions: fieldDefinitions.value.map(def => ({
          name: def.name,
          meta_keys: def.metaKeysText.split('\n').map(k => k.trim()).filter(Boolean),
          availability: def.availability,
        })),
      })

      currentType.value = data

      // Refresh the listing so field counts update immediately on nav back.
      await fetchTypes()

      return data
    } catch (err) {
      formError.value = err.message
      throw err
    } finally {
      isSaving.value = false
    }
  }

  function resetForm () {
    currentType.value = null
    fieldDefinitions.value = []
    formError.value = null
  }

  return {
    // list
    types, isLoadingList, listError,
    // form
    currentType, fieldDefinitions, isLoadingForm, isSaving, formError,
    // actions
    fetchTypes, fetchType, saveType, resetForm,
  }
})
