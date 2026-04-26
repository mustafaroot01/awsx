<script setup lang="ts">
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import type { VForm } from 'vuetify/components/VForm'

interface Emit {
  (e: 'update:isDrawerOpen', value: boolean): void
  (e: 'userData', value: any): void
}

interface Props {
  isDrawerOpen: boolean
  user?: any
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

const isFormValid = ref(false)
const refForm = ref<VForm>()
const fullName = ref('')
const email = ref('')
const password = ref('')
const selectedRole = ref<number | null>(null)
const selectedBranch = ref<number | null>(null)

const roleOptions = ref<{ title: string; value: number }[]>([])
const branchOptions = ref<{ title: string; value: number }[]>([])

onMounted(async () => {
  const [r, b] = await Promise.all([
    $api<any>('/apps/users/roles'),
    $api<any>('/apps/branches?itemsPerPage=-1')
  ])
  roleOptions.value = (r ?? []).map((role: any) => ({ title: role.name, value: role.id }))
  branchOptions.value = (b?.branches ?? []).map((branch: any) => ({ title: branch.name, value: branch.id }))
})

// 👉 Fill form when editing
watch(() => props.isDrawerOpen, (isOpen) => {
  if (isOpen && props.user) {
    fullName.value = props.user.fullName
    email.value = props.user.email
    password.value = '' // Leave password empty when editing
    selectedRole.value = props.user.roles && props.user.roles.length > 0 ? props.user.roles[0].id : null
    selectedBranch.value = props.user.branch_id
  } else if (isOpen && !props.user) {
    // Reset for new user
    fullName.value = ''
    email.value = ''
    password.value = ''
    selectedRole.value = null
    selectedBranch.value = null
  }
})

// 👉 drawer close
const closeNavigationDrawer = () => {
  emit('update:isDrawerOpen', false)
  nextTick(() => {
    refForm.value?.reset()
    fullName.value = ''
    email.value = ''
    password.value = ''
    selectedRole.value = null
    selectedBranch.value = null
  })
}

const onSubmit = () => {
  refForm.value?.validate().then(({ valid }) => {
    if (valid) {
      emit('userData', {
        id: props.user?.id || 0,
        name: fullName.value,
        email: email.value,
        password: password.value,
        roles: selectedRole.value ? [selectedRole.value] : [],
        branch_id: selectedBranch.value,
      })
      emit('update:isDrawerOpen', false)
    }
  })
}

const handleDrawerModelValueUpdate = (val: boolean) => {
  emit('update:isDrawerOpen', val)
}
</script>

<template>
  <VNavigationDrawer
    data-allow-mismatch
    temporary
    :width="400"
    location="end"
    class="scrollable-content"
    :model-value="props.isDrawerOpen"
    @update:model-value="handleDrawerModelValueUpdate"
  >
    <!-- 👉 Title -->
    <AppDrawerHeaderSection
      :title="props.user ? 'تعديل بيانات المستخدم' : 'إضافة مستخدم جديد'"
      @cancel="closeNavigationDrawer"
    />

    <VDivider />

    <PerfectScrollbar :options="{ wheelPropagation: false }">
      <VCard flat>
        <VCardText>
          <!-- 👉 Form -->
          <VForm
            ref="refForm"
            v-model="isFormValid"
            @submit.prevent="onSubmit"
          >
            <VRow>
              <!-- 👉 Full name -->
              <VCol cols="12">
                <AppTextField
                  v-model="fullName"
                  :rules="[requiredValidator]"
                  label="الاسم الكامل"
                  placeholder="مثال: أحمد علي"
                />
              </VCol>

              <!-- 👉 Email -->
              <VCol cols="12">
                <AppTextField
                  v-model="email"
                  :rules="[requiredValidator, emailValidator]"
                  label="البريد الإلكتروني"
                  placeholder="ahmed@example.com"
                />
              </VCol>

              <!-- 👉 Password -->
              <VCol cols="12">
                <AppTextField
                  v-model="password"
                  :rules="props.user ? [] : [requiredValidator]"
                  label="كلمة المرور"
                  type="password"
                  :placeholder="props.user ? 'اتركها فارغة لعدم التغيير' : '********'"
                />
              </VCol>

              <!-- 👉 Role -->
              <VCol cols="12">
                <AppSelect
                  v-model="selectedRole"
                  label="اختيار المجموعة (الصلاحية)"
                  placeholder="اختر مجموعة واحدة"
                  :rules="[requiredValidator]"
                  :items="roleOptions"
                />
              </VCol>

              <!-- 👉 Branch Selection (Optional) -->
              <VCol cols="12">
                <AppSelect
                  v-model="selectedBranch"
                  label="الفرع (اختياري لمسؤولي الفروع)"
                  placeholder="اختر الفرع"
                  :items="branchOptions"
                  clearable
                />
              </VCol>

              <!-- 👉 Submit and Cancel -->
              <VCol cols="12">
                <VBtn
                  type="submit"
                  class="me-3"
                >
                  {{ props.user ? 'تحديث البيانات' : 'حفظ المستخدم' }}
                </VBtn>
                <VBtn
                  type="reset"
                  variant="tonal"
                  color="error"
                  @click="closeNavigationDrawer"
                >
                  إلغاء
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </PerfectScrollbar>
  </VNavigationDrawer>
</template>
