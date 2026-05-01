import { ref } from 'vue'

interface ConfirmDeleteState {
  visible: boolean
  itemId: number | null
  itemName: string
  title: string
  message: string
  confirmLabel: string
  onConfirm: (() => Promise<void> | void) | null
}

const state = ref<ConfirmDeleteState>({
  visible: false,
  itemId: null,
  itemName: '',
  title: 'تأكيد الحذف',
  message: 'هل أنت متأكد من حذف هذا العنصر؟ لا يمكن التراجع عن هذا الإجراء.',
  confirmLabel: 'نعم، احذف',
  onConfirm: null,
})

export function useConfirmDelete() {
  const open = (options: {
    id: number
    name?: string
    title?: string
    message?: string
    confirmLabel?: string
    onConfirm: () => Promise<void> | void
  }) => {
    state.value.itemId = options.id
    state.value.itemName = options.name ?? ''
    state.value.title = options.title ?? 'تأكيد الحذف'
    state.value.message = options.message
      ?? (options.name
        ? `هل أنت متأكد من حذف «${options.name}»؟ لا يمكن التراجع عن هذا الإجراء.`
        : 'هل أنت متأكد من حذف هذا العنصر؟ لا يمكن التراجع عن هذا الإجراء.')
    state.value.confirmLabel = options.confirmLabel ?? 'نعم، احذف'
    state.value.onConfirm = options.onConfirm
    state.value.visible = true
  }

  const close = () => {
    state.value.visible = false
    state.value.itemId = null
    state.value.onConfirm = null
  }

  const confirm = async () => {
    if (state.value.onConfirm) {
      await state.value.onConfirm()
    }
    close()
  }

  return {
    state,
    open,
    close,
    confirm,
  }
}
