import { ref } from 'vue'
import { defineStore } from 'pinia'

let nextId = 1

export const useToastsStore = defineStore('toasts', () => {
  const toasts = ref([])

  function push (message, type = 'success', duration = 2600) {
    const id = nextId++
    toasts.value.push({ id, message, type })
    setTimeout(() => dismiss(id), duration)
    return id
  }

  function dismiss (id) {
    toasts.value = toasts.value.filter(t => t.id !== id)
  }

  return { toasts, push, dismiss }
})
