import { ref } from 'vue'

const isOpen = ref(false)
const options = ref({
  title: 'Konfirmasi',
  description: '',
  confirmText: 'Lanjutkan',
  cancelText: 'Batal',
  variant: 'danger', // 'danger' | 'warning' | 'primary' | 'info'
  items: [], // optional list of items (e.g. names) to display
})
const loading = ref(false)
let resolveFn = null

export function useConfirm() {
  function confirm(config = {}) {
    options.value = {
      title: config.title || 'Konfirmasi Tindakan',
      description: config.description || 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
      confirmText: config.confirmText || 'Lanjutkan',
      cancelText: config.cancelText || 'Batal',
      variant: config.variant || 'danger',
      items: config.items || [],
    }
    loading.value = false
    isOpen.value = true

    return new Promise((resolve) => {
      resolveFn = resolve
    })
  }

  function handleConfirm() {
    isOpen.value = false
    if (resolveFn) {
      resolveFn(true)
      resolveFn = null
    }
  }

  function handleCancel() {
    isOpen.value = false
    if (resolveFn) {
      resolveFn(false)
      resolveFn = null
    }
  }

  return {
    isOpen,
    options,
    loading,
    confirm,
    handleConfirm,
    handleCancel,
  }
}
