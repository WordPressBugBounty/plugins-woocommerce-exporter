import { ref } from 'vue'
import { defineStore } from 'pinia'
import { api } from '../lib/api'

export const useAiMcpStore = defineStore('aiMcp', () => {
  const data = ref(null)
  const isLoading = ref(false)
  const error = ref(null)
  const isSavingWrite = ref(false)

  // Install flow state: null | 'installing' | 'success' | 'error'
  const installStatus = ref(null)

  // Always refetch: StoreAgent can change on its own screens between visits.
  async function fetchData () {
    isLoading.value = !data.value
    error.value = null
    try {
      data.value = await api.get('ai-mcp')
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Failed to load the AI / MCP screen.'
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Allow or block all Store Exporter write abilities in the StoreAgent
   * MCP allowlist.
   *
   * The toggle state comes from the server response only, so a failed save
   * keeps the old state.
   *
   * @param {boolean} enabled Whether to allow the write abilities.
   * @returns {Promise<{status: string, message: string}>}
   */
  async function setWriteAccess (enabled) {
    isSavingWrite.value = true
    try {
      const result = await api.post('ai-mcp/write-access', { enabled })
      data.value = { ...data.value, write_access: result.write_access }
      return { status: result.status, message: result.message }
    } catch (err) {
      return { status: 'error', message: err instanceof Error && err.message ? err.message : data.value?.labels?.write_failed }
    } finally {
      isSavingWrite.value = false
    }
  }

  /**
   * Install and activate StoreAgent, or activate it when it is installed.
   *
   * @returns {Promise<string|null>} The error message, or null on success.
   */
  async function installStoreAgent () {
    installStatus.value = 'installing'
    try {
      await api.post('tools/install', { slug: data.value.install_slug })
      installStatus.value = 'success'
      return null
    } catch (err) {
      installStatus.value = 'error'
      return err instanceof Error ? err.message : null
    }
  }

  return {
    data,
    isLoading,
    error,
    isSavingWrite,
    installStatus,
    fetchData,
    setWriteAccess,
    installStoreAgent,
  }
})
