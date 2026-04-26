<script setup lang="ts">
definePage({
  meta: { action: 'read', subject: 'User' },
})

import { useBranding } from '@/composables/useBranding'

const branding = useBranding()
const { settings, fetchSettings } = branding

const logoFile = ref<File | null>(null)
const loading = ref(false)
const saveSuccess = ref(false)

onMounted(async () => {
  await fetchSettings()
})

const saveSettings = async () => {
  loading.value = true
  saveSuccess.value = false
  
  const formData = new FormData()
  formData.append('app_name', settings.value.app_name || '')
  formData.append('footer_text', settings.value.footer_text || '')
  
  if (logoFile.value) {
    // Vuetify 3 VFileInput returns an array even for single selection
    const file = Array.isArray(logoFile.value) ? logoFile.value[0] : logoFile.value
    if (file) {
      formData.append('logo', file)
    }
  }


  try {
    await $api('/apps/settings', {
      method: 'POST',
      body: formData,
    })
    saveSuccess.value = true
    
    // Force refresh global settings
    await fetchSettings(true)
    
  } catch (error) {
    console.error('Failed to save settings', error)
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <VRow>
    <VCol cols="12" md="8">
      <VCard title="إعدادات الهوية والشعار">
        <VCardText>
          <VRow>
            <VCol cols="12">
              <AppTextField
                v-model="settings.app_name"
                label="اسم النظام"
                placeholder="أدخل اسم النظام الذي يظهر في الأعلى"
              />
            </VCol>

            <VCol cols="12">
              <VFileInput
                v-model="logoFile"
                label="رفع شعار المؤسسة (Logo)"
                accept="image/*"
                prepend-icon="tabler-camera"
                placeholder="اختر ملف الصورة"
              />
            </VCol>
            

            <VCol cols="12">
              <AppTextarea
                v-model="settings.footer_text"
                label="نص التذييل (Footer)"
                rows="2"
              />
            </VCol>

            <VCol cols="12">
              <VBtn
                color="primary"
                :loading="loading"
                @click="saveSettings"
                prepend-icon="tabler-device-floppy"
              >
                حفظ الإعدادات
              </VBtn>
            </VCol>
          </VRow>

          <VAlert
            v-if="saveSuccess"
            type="success"
            variant="tonal"
            class="mt-4"
            closable
          >
            تم حفظ إعدادات الهوية بنجاح! تم تطبيق التغييرات فوراً.
          </VAlert>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12" md="4">
      <VCard title="معاينة الشعار الحالي">
        <VCardText class="text-center py-10">
          <div v-if="settings.app_logo_url" class="mb-4">
            <img :src="settings.app_logo_url" style="max-inline-size: 200px; max-block-size: 200px; object-fit: contain;">
          </div>
          <div v-else class="mb-4 d-flex align-center justify-center border rounded" style="inline-size: 100%; block-size: 150px; background: #f5f5f5;">
            <VIcon icon="tabler-photo" size="48" color="secondary" />
          </div>
          <h5 class="text-h5">{{ settings.app_name }}</h5>
          <p class="text-caption">هذه معاينة تقريبية لكيفية ظهور الهوية</p>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
