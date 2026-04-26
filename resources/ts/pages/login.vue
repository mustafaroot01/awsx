<script setup lang="ts">
import { useGenerateImageVariant } from '@core/composable/useGenerateImageVariant'
import authV2LoginIllustrationBorderedDark from '@images/pages/auth-v2-login-illustration-bordered-dark.png'
import authV2LoginIllustrationBorderedLight from '@images/pages/auth-v2-login-illustration-bordered-light.png'
import authV2LoginIllustrationDark from '@images/pages/auth-v2-login-illustration-dark.png'
import authV2LoginIllustrationLight from '@images/pages/auth-v2-login-illustration-light.png'
import authV2MaskDark from '@images/pages/misc-mask-dark.png'
import authV2MaskLight from '@images/pages/misc-mask-light.png'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'
import { useCookie } from '@core/composable/useCookie'
import { ability } from '@/plugins/0.casl'

const authThemeImg = useGenerateImageVariant(authV2LoginIllustrationLight, authV2LoginIllustrationDark, authV2LoginIllustrationBorderedLight, authV2LoginIllustrationBorderedDark, true)
const authThemeMask = useGenerateImageVariant(authV2MaskLight, authV2MaskDark)

definePage({
  meta: {
    layout: 'blank',
    public: true,
  },
})

// On login page mount: clear ALL stale session data and service workers
onMounted(async () => {
  // Unregister all service workers (kills MSW)
  if ('serviceWorker' in navigator) {
    const registrations = await navigator.serviceWorker.getRegistrations()
    for (const reg of registrations)
      reg.unregister()
  }

  // Clear all auth cookies
  const cookiesToClear = ['userData', 'accessToken', 'userAbilityRules']
  cookiesToClear.forEach(name => {
    document.cookie = `${name}=; Max-Age=-1; path=/`
  })

  // Reset ability rules
  ability.update([])
})

const isPasswordVisible = ref(false)
const credentials = ref({ email: '', password: '' })
const rememberMe = ref(false)
const errors = ref<any>({})
const isLoading = ref(false)

const router = useRouter()

const login = async () => {
  isLoading.value = true
  errors.value = {}
  
  try {
    const res = await $api<any>('/auth/login', {
      method: 'POST',
      body: credentials.value,
      onResponseError({ response }) {
        errors.value = response._data.errors || {}
        isLoading.value = false
      },
    })

    const { accessToken, userData, userAbilityRules } = res

    useCookie('userAbilityRules').value = userAbilityRules
    ability.update(userAbilityRules)
    useCookie('userData').value = userData
    useCookie('accessToken').value = accessToken

    // Add a small delay to ensure cookies are written before redirect
    setTimeout(() => {
      router.replace('/')
    }, 100)
  } catch (err) {
    console.error(err)
    isLoading.value = false
  }
}
</script>

<template>
  <div class="auth-logo d-flex align-center gap-x-3 pa-8">
    <VNodeRenderer :nodes="themeConfig.app.logo" />
    <h1 class="auth-title">{{ themeConfig.app.title }}</h1>
  </div>

  <VRow no-gutters class="auth-wrapper bg-surface">
    <VCol md="8" class="d-none d-md-flex">
      <div class="position-relative bg-background w-100 me-0">
        <div class="d-flex align-center justify-center w-100 h-100" style="padding-inline: 6.25rem;">
          <VImg max-width="613" :src="authThemeImg" class="auth-illustration mt-16 mb-2" />
        </div>
        <img class="auth-footer-mask" :src="authThemeMask" alt="mask" height="280" width="100">
      </div>
    </VCol>

    <VCol cols="12" md="4" class="auth-card-v2 d-flex align-center justify-center">
      <VCard flat :max-width="500" class="mt-12 mt-sm-0 pa-4">
        <VCardText>
          <h4 class="text-h4 mb-1">مرحباً بك في {{ themeConfig.app.title }}</h4>
          <p class="mb-0">الرجاء تسجيل الدخول للمتابعة</p>
        </VCardText>
        <VCardText>
          <VForm @submit.prevent="login">
            <VRow>
              <VCol cols="12">
                <AppTextField
                  v-model="credentials.email"
                  label="البريد الإلكتروني"
                  type="email"
                  autofocus
                  :error-messages="errors.email"
                  :disabled="isLoading"
                />
              </VCol>
              <VCol cols="12">
                <AppTextField
                  v-model="credentials.password"
                  label="كلمة المرور"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  :error-messages="errors.password"
                  :disabled="isLoading"
                  :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                />
                <div class="d-flex align-center flex-wrap justify-space-between my-6">
                  <VCheckbox v-model="rememberMe" label="تذكرني" />
                  <a href="javascript:void(0)" class="text-primary">نسيت كلمة المرور؟</a>
                </div>
                <VBtn 
                  block 
                  type="submit" 
                  :loading="isLoading"
                  :disabled="isLoading"
                >
                  تسجيل الدخول
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth";
</style>
