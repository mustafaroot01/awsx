<script setup lang="ts">
interface UserData {
  id: number | null
  fullName: string
  company: string
  username: string
  role: string
  country: string
  contact: string | undefined
  email: string | undefined
  currentPlan: string
  status: string | undefined
  avatar: string
  taskDone: number | null
  projectDone: number | null
  taxId: string
  language: string
}

interface Props {
  userData: UserData
}

const props = defineProps<Props>()

const localUserData = ref({
  name: props.userData.fullName,
  email: props.userData.email,
})

const passwordData = ref({
  currentPassword: '',
  newPassword: '',
  confirmPassword: '',
})

const isPasswordVisible = ref(false)
const isNewPasswordVisible = ref(false)
const isConfirmPasswordVisible = ref(false)

const onFormSubmit = async () => {
  if (passwordData.value.newPassword && passwordData.value.newPassword !== passwordData.value.confirmPassword) {
    alert('كلمات المرور غير متطابقة')
    return
  }
  
  try {
    await $api(`/apps/users/${props.userData.id}`, {
      method: 'PUT',
      body: {
        name: localUserData.value.name,
        email: localUserData.value.email,
        password: passwordData.value.newPassword || undefined,
      }
    })
    
    alert('تم تحديث البيانات بنجاح')
    if (passwordData.value.newPassword) {
      passwordData.value = { currentPassword: '', newPassword: '', confirmPassword: '' }
    }
  } catch (err) {
    console.error(err)
    alert('حدث خطأ أثناء التحديث')
  }
}
</script>

<template>
  <VCard class="pa-6 rounded-lg elevation-2">
    <VCardText>
      <div class="d-flex align-center gap-2 mb-6">
        <VAvatar size="40" color="primary" variant="tonal" rounded="lg">
          <VIcon icon="tabler-user-edit" size="24" />
        </VAvatar>
        <h4 class="text-h4 font-weight-bold">تعديل البيانات الشخصية</h4>
      </div>

      <VForm @submit.prevent="onFormSubmit">
        <VRow>
          <!-- Personal Info -->
          <VCol cols="12" md="6">
            <AppTextField
              v-model="localUserData.name"
              label="الاسم بالكامل"
              prepend-inner-icon="tabler-user"
            />
          </VCol>
          
          <VCol cols="12" md="6">
            <AppTextField
              v-model="localUserData.email"
              label="البريد الإلكتروني"
              prepend-inner-icon="tabler-mail"
            />
          </VCol>

          <VCol cols="12">
            <VDivider class="my-4" />
            <div class="d-flex align-center gap-2 mb-4">
              <VAvatar size="32" color="secondary" variant="tonal" rounded="sm">
                <VIcon icon="tabler-lock" size="18" />
              </VAvatar>
              <span class="text-h6">تغيير كلمة المرور (اختياري)</span>
            </div>
          </VCol>

          <VCol cols="12">
            <AppTextField
              v-model="passwordData.currentPassword"
              label="كلمة المرور الحالية"
              :type="isPasswordVisible ? 'text' : 'password'"
              :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
              @click:append-inner="isPasswordVisible = !isPasswordVisible"
              prepend-inner-icon="tabler-lock-password"
            />
          </VCol>

          <VCol cols="12" md="6">
            <AppTextField
              v-model="passwordData.newPassword"
              label="كلمة المرور الجديدة"
              :type="isNewPasswordVisible ? 'text' : 'password'"
              :append-inner-icon="isNewPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
              @click:append-inner="isNewPasswordVisible = !isNewPasswordVisible"
              prepend-inner-icon="tabler-lock-plus"
            />
          </VCol>

          <VCol cols="12" md="6">
            <AppTextField
              v-model="passwordData.confirmPassword"
              label="تأكيد كلمة المرور الجديدة"
              :type="isConfirmPasswordVisible ? 'text' : 'password'"
              :append-inner-icon="isConfirmPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
              @click:append-inner="isConfirmPasswordVisible = !isConfirmPasswordVisible"
              prepend-inner-icon="tabler-lock-check"
            />
          </VCol>

          <VCol cols="12" class="mt-8 d-flex justify-end">
            <VBtn
              type="submit"
              size="large"
              class="px-10 rounded-lg"
              prepend-icon="tabler-device-floppy"
            >
              حفظ التغييرات
            </VBtn>
          </VCol>
        </VRow>
      </VForm>
    </VCardText>
  </VCard>
</template>

<style lang="scss" scoped>
.v-card {
  border-radius: 12px !important;
}
</style>
