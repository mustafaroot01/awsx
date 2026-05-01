<script setup lang="ts">
import type { Employee } from '@db/apps/employees/types'
import type { VForm } from 'vuetify/components/VForm'

interface Emit {
  (e: 'update:isDialogVisible', value: boolean): void
  (e: 'employeeData', value: Employee): void
}

interface Props {
  isDialogVisible: boolean
  employeeToEdit?: Employee | null
}

const props = withDefaults(defineProps<Props>(), {
  employeeToEdit: null,
})
const emit = defineEmits<Emit>()

const isEditMode = computed(() => !!props.employeeToEdit)

const isFormValid = ref(false)
const refForm = ref<VForm>()
const currentStep = ref(1)

const firstName = ref('')
const secondName = ref('')
const thirdName = ref('')
const fourthName = ref('')
const lastName = ref('')
const degree = ref<string>()
const rank = ref<string>()
const jobTrack = ref<'producer' | 'admin' | null>(null)
const education = ref<string>()
const gender = ref<'male' | 'female'>()
const jobType = ref<'permanent' | 'contract' | 'daily_wage'>()
const productionNo = ref('')
const birthDate = ref('')
const nationalId = ref('')
const phone = ref('')
const address = ref('')
const hireDate = ref('')
const branchId = ref<number | null>(null)

const branchOptions = ref<{ title: string; value: number }[]>([])

const degrees = [
  'الخاصة', 'الأولى', 'الثانية', 'الثالثة', 'الرابعة', 'الخامسة', 'السادسة', 'السابعة', 'الثامنة', 'التاسعة', 'العاشرة',
]

const rankOptions = ref<{ title: string; value: string; name: string; type: string }[]>([])

const producerRanks = computed(() =>
  rankOptions.value.filter(r => r.type === 'producer').map(r => r.name)
)
const adminRanks = computed(() =>
  rankOptions.value.filter(r => r.type === 'admin').map(r => r.name)
)

const filteredRankOptions = computed(() =>
  jobTrack.value === 'producer' ? producerRanks.value
  : jobTrack.value === 'admin'  ? adminRanks.value
  : []
)

watch(jobTrack, () => { rank.value = undefined })

const educationLevels = [
  'دكتوراه', 'ماجستير', 'بكالوريوس', 'دبلوم عالي', 'دبلوم متوسط', 'إعدادية', 'متوسطة', 'ابتدائية', 'يقرأ ويكتب',
]

const genderOptions = [
  { title: 'ذكر', value: 'male' },
  { title: 'أنثى', value: 'female' },
]

const jobTypeOptions = [
  { title: 'تعيين ملاك', value: 'permanent' },
  { title: 'عقد', value: 'contract' },
  { title: 'أجر يومي', value: 'daily_wage' },
]

const fillForm = (emp: Employee) => {
  firstName.value = emp.firstName
  secondName.value = emp.secondName
  thirdName.value = emp.thirdName
  fourthName.value = emp.fourthName
  lastName.value = emp.lastName
  degree.value = emp.degree
  rank.value = emp.rank
  jobTrack.value = emp.jobTrack ?? (producerRanks.value.includes(emp.rank ?? '') ? 'producer'
    : adminRanks.value.includes(emp.rank ?? '') ? 'admin'
    : null)
  education.value = emp.education
  gender.value = emp.gender
  jobType.value = emp.jobType
  birthDate.value = emp.birthDate ?? ''
  nationalId.value = emp.nationalId ?? ''
  phone.value = emp.phone ?? ''
  address.value = emp.address ?? ''
  productionNo.value = emp.productionNo ?? ''
  hireDate.value = emp.hireDate
  branchId.value = emp.branchId
}

watch(() => props.employeeToEdit, emp => {
  if (emp)
    fillForm(emp)
})

const userData = useCookie<any>('userData')

watch(() => props.isDialogVisible, async open => {
  if (open) {
    // If user has a branch_id (Branch Manager), we lock it to their branch
    if (userData.value?.branch_id) {
      branchId.value = userData.value.branch_id
      
      // We still might want the title for the select to show correctly
      const result = await $api<any>(`/apps/branches/${userData.value.branch_id}`)
      branchOptions.value = [{ title: result.name, value: result.id }]
    } else {
      // Admin: Fetch all branches
      const result = await $api<any>('/apps/branches?itemsPerPage=-1')

      branchOptions.value = (result?.branches ?? []).map((b: any) => ({
        title: b.name,
        value: b.id,
      }))
    }

    // Fetch active ranks from API
    try {
      const ranksRes = await $api<any>('/apps/ranks?active=true')
      rankOptions.value = (ranksRes ?? []).map((r: any) => ({
        title: r.name,
        value: r.name,
        name: r.name,
        type: r.type,
      }))
    } catch {
      rankOptions.value = []
    }

    if (props.employeeToEdit)
      fillForm(props.employeeToEdit)
  }
})

const closeDialog = () => {
  emit('update:isDialogVisible', false)
  nextTick(() => {
    refForm.value?.reset()
    refForm.value?.resetValidation()
    currentStep.value = 1
    birthDate.value = ''
    nationalId.value = ''
    phone.value = ''
    address.value = ''
    hireDate.value = ''
    branchId.value = null
    jobTrack.value = null
    rank.value = undefined
  })
}

const onSubmit = () => {
  refForm.value?.validate().then(({ valid }) => {
    if (valid) {
      emit('employeeData', {
        id: props.employeeToEdit?.id ?? 0,
        firstName: firstName.value,
        secondName: secondName.value,
        thirdName: thirdName.value,
        fourthName: fourthName.value,
        lastName: lastName.value,
        degree: degree.value ?? '',
        rank: rank.value,
        education: education.value ?? '',
        gender: gender.value ?? 'male',
        jobType: jobType.value ?? 'permanent',
        jobTrack: jobTrack.value ?? 'admin',
        birthDate: birthDate.value || null,
        nationalId: nationalId.value || null,
        phone: phone.value || null,
        address: address.value || null,
        productionNo: productionNo.value || null,
        hireDate: hireDate.value,
        avatar: props.employeeToEdit?.avatar ?? null,
        branchId: branchId.value,
      })
      emit('update:isDialogVisible', false)
      nextTick(() => {
        refForm.value?.reset()
        refForm.value?.resetValidation()
        currentStep.value = 1
        hireDate.value = ''
        branchId.value = null
      })
    }
  })
}

const dialogModelValueUpdate = (val: boolean) => {
  emit('update:isDialogVisible', val)
}
</script>

<template>
  <VDialog
    :width="$vuetify.display.smAndDown ? 'auto' : 800"
    :model-value="props.isDialogVisible"
    @update:model-value="dialogModelValueUpdate"
  >
    <DialogCloseBtn @click="dialogModelValueUpdate(false)" />

    <VCard>
      <!-- ── HEADER ── -->
      <VCardItem class="py-3 px-4" density="compact">
        <template #prepend>
          <VAvatar color="primary" variant="tonal" rounded size="38" class="me-2">
            <VIcon icon="tabler-user-plus" size="20" />
          </VAvatar>
        </template>
        <VCardTitle class="text-subtitle-1 font-weight-bold">
          {{ isEditMode ? 'تعديل بيانات الموظف' : 'إضافة موظف جديد' }}
        </VCardTitle>
        <VCardSubtitle class="text-caption">
          {{ currentStep === 1 ? 'الخطوة ١ — البيانات الشخصية' : 'الخطوة ٢ — البيانات الوظيفية' }}
        </VCardSubtitle>
      </VCardItem>

      <VDivider thickness="1" />

      <VCardText class="pa-4">
        <VForm
          ref="refForm"
          v-model="isFormValid"
          @submit.prevent="onSubmit"
        >
          <!-- ── STEP INDICATOR (matching screenshot style) ── -->
          <div class="d-flex justify-center gap-8 mb-6">
            <div
              v-for="(step, idx) in [
                { num: 1, label: 'البيانات الشخصية', icon: 'tabler-user' },
                { num: 2, label: 'البيانات الوظيفية', icon: 'tabler-briefcase' },
              ]"
              :key="idx"
              class="d-flex flex-column align-center gap-1 cursor-pointer"
              style="min-width: 100px;"
              @click="currentStep = step.num"
            >
              <VAvatar
                :color="currentStep >= step.num ? 'primary' : 'grey-lighten-2'"
                variant="tonal"
                size="48"
                class="transition-all"
                :class="{ 'elevation-2': currentStep === step.num }"
              >
                <VIcon
                  :icon="currentStep > step.num ? 'tabler-check' : step.icon"
                  size="22"
                />
              </VAvatar>
              <span
                class="text-caption font-weight-bold transition-all"
                :class="currentStep >= step.num ? 'text-primary' : 'text-medium-emphasis'"
              >
                {{ step.label }}
              </span>
            </div>
          </div>

          <!-- ═══════════════════════════════════════════════
               STEP 1 — البيانات الشخصية
          ═══════════════════════════════════════════════ -->
          <VScaleTransition>
            <VRow v-show="currentStep === 1">
              <!-- الاسم الأول -->
              <VCol cols="12" md="6">
                <AppTextField
                  v-model="firstName"
                  :rules="[requiredValidator]"
                  label="الاسم الأول *"
                  placeholder="أحمد"
                />
              </VCol>

              <!-- اسم الأب -->
              <VCol cols="12" md="6">
                <AppTextField
                  v-model="secondName"
                  :rules="[requiredValidator]"
                  label="اسم الأب *"
                  placeholder="محمد"
                />
              </VCol>

              <!-- اسم الجد الأول -->
              <VCol cols="12" md="4">
                <AppTextField
                  v-model="thirdName"
                  :rules="[requiredValidator]"
                  label="اسم الجد الأول *"
                  placeholder="علي"
                />
              </VCol>

              <!-- اسم الجد الثاني -->
              <VCol cols="12" md="4">
                <AppTextField
                  v-model="fourthName"
                  label="اسم الجد الثاني"
                  placeholder="جاسم"
                />
              </VCol>

              <!-- اللقب -->
              <VCol cols="12" md="4">
                <AppTextField
                  v-model="lastName"
                  label="اللقب / العائلة"
                  placeholder="الموسوي"
                />
              </VCol>

              <!-- الجنس -->
              <VCol cols="12" md="6">
                <AppSelect
                  v-model="gender"
                  :rules="[requiredValidator]"
                  label="الجنس *"
                  placeholder="اختر الجنس"
                  :items="genderOptions"
                />
              </VCol>

              <!-- الهاتف -->
              <VCol cols="12" md="6">
                <AppTextField
                  v-model="phone"
                  label="هاتف الموظف"
                  placeholder="0770 123 4567"
                />
              </VCol>

              <!-- تاريخ المواليد -->
              <VCol cols="12" md="6">
                <AppDateTimePicker
                  v-model="birthDate"
                  label="تاريخ المواليد"
                  placeholder="YYYY-MM-DD"
                  :config="{ dateFormat: 'Y-m-d', maxDate: 'today' }"
                />
              </VCol>

              <!-- البطاقة الوطنية -->
              <VCol cols="12" md="6">
                <AppTextField
                  v-model="nationalId"
                  label="رقم البطاقة الوطنية"
                  placeholder="12345678901"
                />
              </VCol>

              <!-- العنوان / أقرب نقطة دالة -->
              <VCol cols="12">
                <AppTextarea
                  v-model="address"
                  label="العنوان أو أقرب نقطة دالة على السكن"
                  placeholder="مثال: حي الجامعة - مقابل جامعة النهرين - بيت رقم ١٢"
                  rows="3"
                />
              </VCol>
            </VRow>
          </VScaleTransition>

          <!-- ═══════════════════════════════════════════════
               STEP 2 — البيانات الوظيفية
          ═══════════════════════════════════════════════ -->
          <VScaleTransition>
            <VRow v-show="currentStep === 2">
              <!-- الدرجة -->
              <VCol cols="12" md="6">
                <AppSelect
                  v-model="degree"
                  :rules="[requiredValidator]"
                  label="الدرجة *"
                  placeholder="اختر الدرجة"
                  :items="degrees"
                />
              </VCol>

              <!-- نوع الموظف -->
              <VCol cols="12" md="6">
                <div class="text-body-2 text-medium-emphasis mb-2">نوع الموظف *</div>
                <VBtnToggle
                  v-model="jobTrack"
                  divided
                  variant="outlined"
                  color="primary"
                  class="w-100"
                  style="block-size:40px"
                >
                  <VBtn value="producer" class="flex-grow-1 text-caption">
                    <VIcon start icon="tabler-briefcase" size="15" />
                    منتج
                  </VBtn>
                  <VBtn value="admin" class="flex-grow-1 text-caption">
                    <VIcon start icon="tabler-building" size="15" />
                    إداري
                  </VBtn>
                </VBtnToggle>
              </VCol>

              <!-- العنوان الوظيفي -->
              <VCol cols="12" md="6">
                <AppSelect
                  v-model="rank"
                  :rules="[requiredValidator]"
                  :disabled="!jobTrack"
                  label="العنوان الوظيفي *"
                  :placeholder="!jobTrack ? 'حدد نوع الموظف أولاً' : 'اختر العنوان الوظيفي'"
                  :items="filteredRankOptions"
                />
              </VCol>

              <!-- الشهادة -->
              <VCol cols="12" md="6">
                <AppSelect
                  v-model="education"
                  :rules="[requiredValidator]"
                  label="الشهادة *"
                  placeholder="اختر الشهادة"
                  :items="educationLevels"
                />
              </VCol>

              <!-- نوع الوظيفة -->
              <VCol cols="12" md="6">
                <AppSelect
                  v-model="jobType"
                  :rules="[requiredValidator]"
                  label="نوع الوظيفة *"
                  placeholder="اختر نوع الوظيفة"
                  :items="jobTypeOptions"
                />
              </VCol>

              <!-- الرقم الإنتاجي -->
              <VCol cols="12" md="6">
                <AppTextField
                  v-model="productionNo"
                  label="الرقم الإنتاجي"
                  placeholder="2024009"
                />
                <div class="text-caption text-medium-emphasis mt-1">
                  <VIcon icon="tabler-info-circle" size="14" class="me-1" />
                  في حال كان الموظف إدارياً، اترك هذا الحقل فارغاً
                </div>
              </VCol>

              <!-- تاريخ التعيين -->
              <VCol cols="12" md="6">
                <AppDateTimePicker
                  v-model="hireDate"
                  :rules="[requiredValidator]"
                  label="تاريخ التعيين *"
                  placeholder="YYYY-MM-DD"
                  :config="{ dateFormat: 'Y-m-d', maxDate: 'today' }"
                />
              </VCol>

              <!-- الفرع -->
              <VCol cols="12" md="6">
                <AppSelect
                  v-model="branchId"
                  :rules="[requiredValidator]"
                  label="الفرع *"
                  placeholder="اختر الفرع"
                  :items="branchOptions"
                  :disabled="!!userData?.branch_id"
                  :readonly="!!userData?.branch_id"
                  :clearable="!userData?.branch_id"
                  clear-icon="tabler-x"
                />
              </VCol>
            </VRow>
          </VScaleTransition>

          <!-- ── NAVIGATION BUTTONS ── -->
          <VDivider class="my-4" />

          <div class="d-flex justify-space-between align-center">
            <VBtn
              v-if="currentStep > 1"
              variant="tonal"
              color="secondary"
              class="px-4"
              @click="currentStep--"
            >
              <VIcon start icon="tabler-arrow-right" size="16" />
              السابق
            </VBtn>
            <div v-else />

            <div class="d-flex gap-2">
              <VBtn
                variant="tonal"
                color="error"
                class="px-4"
                @click="closeDialog"
              >
                <VIcon start icon="tabler-x" size="16" />
                إلغاء
              </VBtn>

              <VBtn
                v-if="currentStep === 1"
                color="primary"
                class="px-4"
                @click="currentStep++"
              >
                التالي
                <VIcon end icon="tabler-arrow-left" size="16" />
              </VBtn>

              <VBtn
                v-else
                type="submit"
                color="primary"
                class="px-4"
              >
                <VIcon start icon="tabler-check" size="16" />
                {{ isEditMode ? 'تحديث' : 'حفظ' }}
              </VBtn>
            </div>
          </div>
        </VForm>
      </VCardText>
    </VCard>
  </VDialog>
</template>
