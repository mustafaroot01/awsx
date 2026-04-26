import { useTheme } from 'vuetify'

const settings = ref({
  app_name: 'Dr AWS',
  app_logo_url: '',
})

let isFetched = false

export const useBranding = () => {

  const { global } = useTheme()

  const fetchSettings = async (force = false) => {
    if (isFetched && !force) return

    try {
      const data = await $api<any>('/apps/settings')
      if (data) {
        settings.value = { ...settings.value, ...data }
        isFetched = true
        
        // Apply Name to Document Title
        if (settings.value.app_name) {
          document.title = settings.value.app_name
        }
      }
    } catch (e) {
      console.error('Failed to fetch branding settings', e)
    }
  }

  return {
    settings,
    fetchSettings,
  }
}
