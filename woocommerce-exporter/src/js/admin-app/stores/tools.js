import { __ } from '@wordpress/i18n'
import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import { api } from '../lib/api'

const PER_PAGE = 10

export const useToolsStore = defineStore('tools', () => {
  const data = ref(null)
  const isLoading = ref(false)
  const isRefreshing = ref(false)
  const error = ref(null)

  // Install flow state: null | 'installing' | 'activating' | 'success' | 'error'
  const installStatus = ref(null)
  const installError = ref(null)

  // Client-side filter/search/pagination state.
  const statusFilter = ref('all')
  const searchQuery = ref('')
  const currentPage = ref(1)

  /**
   * Apply a Tools snapshot returned by the refresh or install route.
   *
   * Replaces the object reference so Vue's reactivity system detects the change reliably -
   * mutating nested properties can be missed by watchers that track the ref itself rather than
   * its individual properties. woo_tools travels with the modules so the companion-tool cards
   * follow a plugin switched on without leaving this screen; they are otherwise only built when
   * the app mounts. Each key falls back to what is already held, so a route that omits one
   * leaves it alone rather than blanking the screen.
   *
   * @param {object} snapshot  The route's response: { woo_tools, modules, counts }.
   */
  function applySnapshot (snapshot) {
    if (!data.value || !snapshot) return

    data.value = {
      ...data.value,
      woo_tools: snapshot.woo_tools ?? data.value.woo_tools,
      modules: snapshot.modules ?? data.value.modules,
      counts: snapshot.counts ?? data.value.counts
    }
  }

  async function fetchData () {
    if (data.value) return
    isLoading.value = true
    error.value = null
    try {
      data.value = await api.get('tools')
    } catch (err) {
      error.value = err instanceof Error ? err.message : __('Failed to load tools data.', 'woocommerce-exporter')
    } finally {
      isLoading.value = false
    }
  }

  async function refresh () {
    isRefreshing.value = true
    error.value = null
    try {
      applySnapshot(await api.post('tools/refresh', {}))
      currentPage.value = 1
    } catch (err) {
      error.value = err instanceof Error ? err.message : __('Failed to refresh module status.', 'woocommerce-exporter')
    } finally {
      isRefreshing.value = false
    }
  }

  /**
   * Install and activate a wordpress.org plugin by slug.
   *
   * Tracks progress through installStatus: installing → activating → success | error.
   * On success, refreshes the module list so status badges update immediately.
   *
   * @param {string} slug  Plugin directory slug (e.g. "woocommerce-store-toolkit").
   */
  async function installPlugin (slug) {
    installStatus.value = 'installing'
    installError.value = null

    // After ~1 s show "activating" as a visual bridge while the server
    // downloads and installs (which typically takes 2–8 s).
    const activatingTimer = setTimeout(() => {
      if (installStatus.value === 'installing') {
        installStatus.value = 'activating'
      }
    }, 1200)

    try {
      const result = await api.post('tools/install', { slug })
      installStatus.value = 'success'
      // The install endpoint returns the same snapshot as the refresh route, so the status badges
      // and the card links update in one round trip - no separate /refresh call, no page reload.
      applySnapshot(result)
    } catch (err) {
      installStatus.value = 'error'
      installError.value = err instanceof Error ? err.message : __('Installation failed. Please try again.', 'woocommerce-exporter')
    } finally {
      clearTimeout(activatingTimer)
    }
  }

  function resetInstall () {
    installStatus.value = null
    installError.value = null
  }

  function setStatusFilter (status) {
    statusFilter.value = status
    currentPage.value = 1
  }

  function setSearch (query) {
    searchQuery.value = query
    currentPage.value = 1
  }

  const filteredModules = computed(() => {
    const modules = data.value?.modules ?? []
    let list = modules

    if (statusFilter.value !== 'all') {
      list = list.filter(m => m.status === statusFilter.value)
    }

    const q = searchQuery.value.trim().toLowerCase()
    if (q) {
      list = list.filter(
        m =>
          m.title.toLowerCase().includes(q) ||
          (m.description ?? '').toLowerCase().includes(q)
      )
    }

    return list
  })

  const totalPages = computed(() =>
    Math.max(1, Math.ceil(filteredModules.value.length / PER_PAGE))
  )

  const paginatedModules = computed(() => {
    const start = (currentPage.value - 1) * PER_PAGE
    return filteredModules.value.slice(start, start + PER_PAGE)
  })

  const showingFrom = computed(() => {
    if (filteredModules.value.length === 0) return 0
    return (currentPage.value - 1) * PER_PAGE + 1
  })

  const showingTo = computed(() =>
    Math.min(currentPage.value * PER_PAGE, filteredModules.value.length)
  )

  return {
    data,
    isLoading,
    isRefreshing,
    error,
    installStatus,
    installError,
    statusFilter,
    searchQuery,
    currentPage,
    filteredModules,
    paginatedModules,
    totalPages,
    showingFrom,
    showingTo,
    PER_PAGE,
    fetchData,
    refresh,
    setStatusFilter,
    setSearch,
    installPlugin,
    resetInstall,
  }
})
