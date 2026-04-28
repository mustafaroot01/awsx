import { ref } from 'vue'

const message = ref('')
const color   = ref<'error' | 'warning' | 'success' | 'info'>('error')
const visible = ref(false)

export const useGlobalToast = () => {
  const show = (msg: string, type: 'error' | 'warning' | 'success' | 'info' = 'error') => {
    message.value = msg
    color.value   = type
    visible.value = true
  }

  return { message, color, visible, show }
}
