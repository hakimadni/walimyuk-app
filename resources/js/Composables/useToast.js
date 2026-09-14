import { ref } from 'vue'

const toasts = ref([])
let toastCounter = 0

export function useToast() {
  function addToast({ message, type = 'success', duration = 3500 }) {
    const id = ++toastCounter
    toasts.value.push({ id, message, type })

    if (duration > 0) {
      setTimeout(() => {
        removeToast(id)
      }, duration)
    }

    return id
  }

  function removeToast(id) {
    const idx = toasts.value.findIndex(t => t.id === id)
    if (idx !== -1) {
      toasts.value.splice(idx, 1)
    }
  }

  function success(message, duration) {
    return addToast({ message, type: 'success', duration })
  }

  function error(message, duration) {
    return addToast({ message, type: 'error', duration })
  }

  function info(message, duration) {
    return addToast({ message, type: 'info', duration })
  }

  function warning(message, duration) {
    return addToast({ message, type: 'warning', duration })
  }

  return {
    toasts,
    addToast,
    removeToast,
    success,
    error,
    info,
    warning,
  }
}
