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
  userData?: UserData
  isDialogVisible: boolean
}

interface Emit {
  (e: 'submit', value: UserData): void
  (e: 'update:isDialogVisible', val: boolean): void
}

const props = withDefaults(defineProps<Props>(), {
  userData: () => ({
    id: 0,
    fullName: '',
    company: '',
    role: '',
    username: '',
    country: '',
    contact: '',
    email: '',
    currentPlan: '',
    status: '',
    avatar: '',
    taskDone: null,
    projectDone: null,
    taxId: '',
    language: '',
  }),
})

const emit = defineEmits<Emit>()

const userData = ref<UserData>(structuredClone(toRaw(props.userData)))

watch(() => props.isDialogVisible, (val) => {
  if (val) userData.value = structuredClone(toRaw(props.userData))
})

const onFormSubmit = () => {
  emit('update:isDialogVisible', false)
  emit('submit', userData.value)
}

const onFormReset = () => {
  userData.value = structuredClone(toRaw(props.userData))
  emit('update:isDialogVisible', false)
}

const dialogModelValueUpdate = (val: boolean) => {
  emit('update:isDialogVisible', val)
}
</script>

<template>
  <VDialog
    :width="$vuetify.display.smAndDown ? 'auto' : 800"
    :model-value="props.isDialogVisible"
    persistent
    @update:model-value="dialogModelValueUpdate"
  >
    <!-- Dialog close btn -->
    <DialogCloseBtn @click="dialogModelValueUpdate(false)" />

    <VCard class="pa-sm-10 pa-4 overflow-visible">
      <VCardText>
        <!-- 👉 Title -->
        <div class="text-center mb-10">
          <h4 class="text-h4 mb-2 font-weight-bold">
            تعديل بيانات المستخدم
          </h4>
          <p class="text-body-1 text-medium-emphasis">
            تأكد من صحة البيانات قبل حفظ التغييرات
          </p>
        </div>

        <!-- 👉 Form -->
        <VForm @submit.prevent="onFormSubmit">
          <VRow>
            <!-- SECTION Basic Info -->
            <VCol cols="12">
              <div class="d-flex align-center gap-2 mb-4">
                <VIcon icon="tabler-user" size="20" color="primary" />
                <span class="text-h6 font-weight-bold">المعلومات الأساسية</span>
              </div>
              <VDivider class="mb-6" />
            </VCol>

            <VCol cols="12" md="6">
              <AppTextField
                v-model="userData.fullName"
                label="الاسم الكامل"
                placeholder="مثال: مصطفى قاسم"
                prepend-inner-icon="tabler-user"
              />
            </VCol>

            <VCol cols="12" md="6">
              <AppTextField
                v-model="userData.username"
                label="اسم المستخدم"
                placeholder="mostafa.qasim"
                prepend-inner-icon="tabler-at"
              />
            </VCol>

            <!-- SECTION Contact Info -->
            <VCol cols="12" class="mt-6">
              <div class="d-flex align-center gap-2 mb-4">
                <VIcon icon="tabler-phone" size="20" color="primary" />
                <span class="text-h6 font-weight-bold">معلومات الاتصال</span>
              </div>
              <VDivider class="mb-6" />
            </VCol>

            <VCol cols="12" md="6">
              <AppTextField
                v-model="userData.email"
                label="البريد الإلكتروني"
                placeholder="example@mail.com"
                prepend-inner-icon="tabler-mail"
              />
            </VCol>

            <VCol cols="12" md="6">
              <AppTextField
                v-model="userData.contact"
                label="رقم الهاتف"
                placeholder="+964 7XX XXX XXXX"
                prepend-inner-icon="tabler-phone"
              />
            </VCol>

            <!-- SECTION System Info -->
            <VCol cols="12" class="mt-6">
              <div class="d-flex align-center gap-2 mb-4">
                <VIcon icon="tabler-settings" size="20" color="primary" />
                <span class="text-h6 font-weight-bold">إعدادات النظام</span>
              </div>
              <VDivider class="mb-6" />
            </VCol>

            <VCol cols="12" md="6">
              <AppSelect
                v-model="userData.status"
                label="حالة الحساب"
                :items="[
                  { title: 'نشط', value: 'active' },
                  { title: 'غير نشط', value: 'inactive' },
                  { title: 'معلق', value: 'pending' }
                ]"
                prepend-inner-icon="tabler-activity"
              />
            </VCol>

            <VCol cols="12" md="6">
              <AppTextField
                v-model="userData.role"
                label="الدور الوظيفي"
                readonly
                disabled
                prepend-inner-icon="tabler-lock"
              />
            </VCol>

            <!-- 👉 Submit and Cancel -->
            <VCol cols="12" class="d-flex flex-wrap justify-center gap-4 mt-10">
              <VBtn type="submit" size="large" class="px-10">
                حفظ التغييرات
              </VBtn>

              <VBtn
                color="secondary"
                variant="tonal"
                size="large"
                class="px-10"
                @click="onFormReset"
              >
                إلغاء
              </VBtn>
            </VCol>
          </VRow>
        </VForm>
      </VCardText>
    </VCard>
  </VDialog>
</template>

<style lang="scss" scoped>
.v-card {
  border-radius: 16px;
}
</style>
