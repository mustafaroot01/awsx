import { ofetch } from 'ofetch'
import { useCookie } from '@core/composable/useCookie'

export const $api = ofetch.create({
  // Use a relative path to ensure it works on any host (localhost, 127.0.0.1, etc.)
  baseURL: import.meta.env.VITE_API_BASE_URL || '/api',
  async onRequest({ options }) {
    const accessToken = useCookie('accessToken').value
    if (accessToken)
      options.headers.append('Authorization', `Bearer ${accessToken}`)
    
    // Add common headers
    options.headers.append('Accept', 'application/json')
  },
  async onResponseError({ response }) {
    console.error('API Error:', response.status, response._data)
  }
})
