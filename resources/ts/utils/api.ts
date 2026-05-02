import { useGlobalToast } from '@/composables/useGlobalToast'
import { useCookie } from '@core/composable/useCookie'
import { ofetch } from 'ofetch'

/**
 * Show a permission-denied toast to the user.
 * Call this ONLY in catch blocks of user-initiated actions (button clicks, form submits, etc.).
 * Do NOT call this for background/page-load requests.
 */
export const showPermissionError = (error: any) => {
  const { show } = useGlobalToast()
  const responseData = error?.data ?? error?.response?._data ?? error?._data
  const perm = responseData?.required_permission
  const status = error?.status ?? error?.statusCode ?? error?.response?.status

  if (status === 403) {
    show(
      perm
        ? `ليس لديك صلاحية «${perm}»`
        : (responseData?.message ?? 'ليس لديك صلاحية لتنفيذ هذا الإجراء'),
      'error',
    )
    return true
  }
  return false
}

export const $api = ofetch.create({
  // Use a relative path to ensure it works on any host (localhost, 127.0.0.1, etc.)
  baseURL: import.meta.env.VITE_API_BASE_URL || '/api',
  async onRequest({ options }) {
    const accessToken = useCookie('accessToken').value
    if (accessToken)
      options.headers.append('Authorization', `Bearer ${accessToken}`)
    
    // Add common headers
    options.headers.append('Accept', 'application/json')

    // Prevent browser from caching API GET responses (fixes stale data after add/edit/delete)
    options.headers.append('Cache-Control', 'no-cache, no-store, must-revalidate')
    options.headers.append('Pragma', 'no-cache')
  },
  async onResponseError({ request, options, response }) {
    console.error('API Error:', response.status, response._data)

    const method = (options.method ?? 'GET').toString().toUpperCase()
    const isMutation = ['POST', 'PUT', 'PATCH', 'DELETE'].includes(method)

    if (!isMutation) return

    const { show } = useGlobalToast()

    // 403 Permission errors are NOT shown here automatically.
    // They are only shown when the calling code explicitly calls showPermissionError()
    // in a catch block after a user-initiated action.
    // This prevents "spam" toasts when the page loads and background requests fail.

    if (response.status === 422) {
      const msg = response._data?.message
      if (msg) show(msg, 'warning')
    }
  },
})
