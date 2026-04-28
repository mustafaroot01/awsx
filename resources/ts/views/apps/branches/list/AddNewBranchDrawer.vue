<script setup lang="ts">
import type { Branch } from '@db/apps/branches/types'
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import type { VForm } from 'vuetify/components/VForm'

interface Emit {
  (e: 'update:isDrawerOpen', value: boolean): void
  (e: 'branchData', value: Branch): void
}

interface Props {
  isDrawerOpen: boolean
  branchToEdit?: Branch | null
}

const props = withDefaults(defineProps<Props>(), {
  branchToEdit: null,
})
const emit = defineEmits<Emit>()

const isEditMode = computed(() => !!props.branchToEdit)

const isFormValid = ref(false)
const refForm = ref<VForm>()

const name = ref('')
const location = ref('')
const governorate = ref<string>()
const managerId = ref<number | null>(null)
const deputyId = ref<number | null>(null)

const governorates = [
  'الإدارة العامة', 'بغداد', 'البصرة', 'نينوى', 'أربيل', 'كربلاء', 'النجف',
  'ذي قار', 'المثنى', 'القادسية', 'ميسان', 'بابل', 'ديالى', 'الأنبار',
  'صلاح الدين', 'كركوك', 'واسط', 'السليمانية', 'دهوك',
]

interface UserOption {
  id: number
  fullName: string
  email: string
}

const allUsers = ref<UserOption[]>([])
const isLoadingUsers = ref(false)

const managerSearch = ref('')
const deputySearch = ref('')

const managerOptions = computed(() => {
  const q = managerSearch.value.toLowerCase()
  const base = allUsers.value.filter(u => u.id !== deputyId.value)
  return q ? base.filter(u => u.fullName.toLowerCase().includes(q) || u.email.toLowerCase().includes(q)) : base
})

const deputyOptions = computed(() => {
  const q = deputySearch.value.toLowerCase()
  const base = allUsers.value.filter(u => u.id !== managerId.value)
  return q ? base.filter(u => u.fullName.toLowerCase().includes(q) || u.email.toLowerCase().includes(q)) : base
})

const fillForm = (b: Branch) => {
  name.value = b.name
  location.value = b.location
  governorate.value = b.governorate
  managerId.value = b.managerId
  deputyId.value = b.deputyId
}

watch(() => props.isDrawerOpen, async open => {
  if (!open) {
    allUsers.value = []
    return
  }

  // Reset selections before loading
  managerId.value = null
  deputyId.value  = null

  isLoadingUsers.value = true
  try {
    const params = new URLSearchParams()
    if (props.branchToEdit?.managerId) params.append('managerId', String(props.branchToEdit.managerId))
    if (props.branchToEdit?.deputyId)  params.append('deputyId',  String(props.branchToEdit.deputyId))
    const result = await $api<any>(`/apps/users/for-selection?${params.toString()}`)
    allUsers.value = result?.users ?? []
  } catch (e) {
    console.error('forSelection error:', e)
    allUsers.value = []
  } finally {
    isLoadingUsers.value = false
  }

  // Fill form AFTER users are loaded so VAutocomplete finds the items
  if (props.branchToEdit) fillForm(props.branchToEdit)
})

const closeNavigationDrawer = () => {
  emit('update:isDrawerOpen', false)
  nextTick(() => {
    refForm.value?.reset()
    refForm.value?.resetValidation()
    managerId.value = null
    deputyId.value = null
  })
}

const onSubmit = () => {
  refForm.value?.validate().then(({ valid }) => {
    if (valid) {
      emit('branchData', {
        id: props.branchToEdit?.id ?? 0,
        name: name.value,
        location: location.value,
        governorate: governorate.value ?? '',
        managerId: managerId.value,
        deputyId: deputyId.value,
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
    :width="480"
    location="end"
    class="scrollable-content"
    :model-value="props.isDrawerOpen"
    @update:model-value="handleDrawerModelValueUpdate"
  >
    <AppDrawerHeaderSection
      :title="isEditMode ? 'تعديل بيانات الفرع' : 'إضافة فرع جديد'"
      @cancel="closeNavigationDrawer"
    />

    <VDivider />

    <PerfectScrollbar :options="{ wheelPropagation: false }">
      <VCard flat>
        <VCardText>
          <VForm
            ref="refForm"
            v-model="isFormValid"
            @submit.prevent="onSubmit"
          >
            <VRow>
              <VCol cols="12">
                <AppTextField
                  v-model="name"
                  :rules="[requiredValidator]"
                  label="اسم الفرع"
                  placeholder="مثال: فرع الكرادة"
                />
              </VCol>

              <VCol cols="12">
                <AppTextField
                  v-model="location"
                  :rules="[requiredValidator]"
                  label="مكان الفرع (العنوان التفصيلي)"
                  placeholder="المحافظة، المنطقة، الشارع"
                />
              </VCol>

              <VCol cols="12">
                <AppSelect
                  v-model="governorate"
                  :rules="[requiredValidator]"
                  label="المحافظة / الإدارة العامة"
                  placeholder="اختر المحافظة"
                  :items="governorates"
                />
              </VCol>

              <!-- مدير الفرع (من المستخدمين) -->
              <VCol cols="12">
                <VAutocomplete
                  v-model="managerId"
                  v-model:search="managerSearch"
                  :items="managerOptions"
                  item-value="id"
                  item-title="fullName"
                  :loading="isLoadingUsers"
                  label="مدير الفرع (المسؤول)"
                  placeholder="ابحث باسم المستخدم أو البريد..."
                  clearable
                  no-filter
                  variant="outlined"
                  density="comfortable"
                  hide-details="auto"
                  no-data-text="لا يوجد مستخدمون في النظام"
                >
                  <template #item="{ props: itemProps, item }">
                    <VListItem v-bind="itemProps">
                      <template #subtitle>{{ item.raw.email }}</template>
                    </VListItem>
                  </template>
                </VAutocomplete>
              </VCol>

              <!-- معاون المدير (اختياري) -->
              <VCol cols="12">
                <VAutocomplete
                  v-model="deputyId"
                  v-model:search="deputySearch"
                  :items="deputyOptions"
                  item-value="id"
                  item-title="fullName"
                  :loading="isLoadingUsers"
                  label="معاون المدير (اختياري)"
                  placeholder="ابحث باسم المستخدم أو البريد..."
                  clearable
                  no-filter
                  variant="outlined"
                  density="comfortable"
                  hide-details="auto"
                  no-data-text="لا يوجد مستخدمون في النظام"
                >
                  <template #item="{ props: itemProps, item }">
                    <VListItem v-bind="itemProps">
                      <template #subtitle>
                        {{ item.raw.email }}
                      </template>
                    </VListItem>
                  </template>
                </VAutocomplete>
              </VCol>

              <VCol cols="12">
                <VBtn type="submit" class="me-3">
                  {{ isEditMode ? 'تحديث' : 'حفظ الفرع' }}
                </VBtn>
                <VBtn variant="tonal" color="secondary" @click="closeNavigationDrawer">إلغاء</VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </PerfectScrollbar>
  </VNavigationDrawer>
</template>
