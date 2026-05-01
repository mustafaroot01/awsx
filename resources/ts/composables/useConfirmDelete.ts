import { reactive } from 'vue'

interface ConfirmOptions {
  id?: number | string
  name?: string
  title?: string
  message?: string
  confirmLabel?: string
  onConfirm?: () => void | Promise<void>
}

const state = reactive({
  visible: false,
  title: 'تأكيد الحذف',
  message: 'هل أنت متأكد؟',
  confirmLabel: 'حذف',
  _onConfirm: null as (() => void | Promise<void>) | null,
})

export function useConfirmDelete() {
  const open = (options: ConfirmOptions = {}) => {
    state.title = options.title ?? 'تأكيد الحذف'
    state.message = options.message ?? `هل أنت متأكد من حذف "${options.name ?? 'العنصر'}"؟`
    state.confirmLabel = options.confirmLabel ?? 'حذف'
    state._onConfirm = options.onConfirm ?? null
    state.visible = true
  }

  const close = () => {
    state.visible = false
  }

  const confirm = async () => {
    if (state._onConfirm) {
      await state._onConfirm()
    }
    state.visible = false
  }

  return { state, open, close, confirm }
}
