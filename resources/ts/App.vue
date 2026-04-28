<script setup lang="ts">
import { useTheme } from 'vuetify'
import ScrollToTop from '@core/components/ScrollToTop.vue'
import initCore from '@core/initCore'
import { initConfigStore, useConfigStore } from '@core/stores/config'
import { hexToRgb } from '@core/utils/colorConverter'
import { useBranding } from '@/composables/useBranding'
import { useGlobalToast } from '@/composables/useGlobalToast'

const { global } = useTheme()
const { fetchSettings } = useBranding()

// ℹ️ Sync current theme with initial loader theme
initCore()
initConfigStore()
fetchSettings()

// Auto-refresh CASL rules on every app load (handles role changes without re-login)
const accessToken = useCookie<string>('accessToken')
const userAbilityRules = useCookie<any[]>('userAbilityRules')
const userData = useCookie<any>('userData')
const ability = useAbility()

onMounted(async () => {
  if (!accessToken.value) return
  try {
    const res = await $api<any>('/auth/refresh-rules')
    if (res?.userAbilityRules) {
      userAbilityRules.value = res.userAbilityRules
      userData.value = { ...userData.value, ...res.userData }
      ability.update(res.userAbilityRules)
    }
  } catch { /* silent */ }
})

const configStore = useConfigStore()
const { visible: toastVisible, message: toastMessage, color: toastColor } = useGlobalToast()
</script>

<template>
  <VLocaleProvider :rtl="configStore.isAppRTL">
    <!-- ℹ️ This is required to set the background color of active nav link based on currently active global theme's primary -->
    <VApp :style="`--v-global-theme-primary: ${hexToRgb(global.current.value.colors.primary)}`">
      <RouterView />
      <ScrollToTop />

      <!-- Global API Error Snackbar -->
      <VSnackbar
        v-model="toastVisible"
        :color="toastColor"
        location="top end"
        :timeout="5000"
        min-width="300"
      >
        <div class="d-flex align-center gap-2">
          <VIcon :icon="toastColor === 'error' ? 'tabler-shield-x' : 'tabler-alert-triangle'" />
          {{ toastMessage }}
        </div>
        <template #actions>
          <VBtn variant="text" @click="toastVisible = false">إغلاق</VBtn>
        </template>
      </VSnackbar>
    </VApp>
  </VLocaleProvider>
</template>
