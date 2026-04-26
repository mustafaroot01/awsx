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

const firstName = ref('')
const secondName = ref('')
const thirdName = ref('')
const fourthName = ref('')
const lastName = ref('')
const employeeNo = ref('')
const degree = ref<string>()
const rank = ref<string>()
const jobTrack = ref<'producer' | 'admin' | null>(null)
const education = ref<string>()
const gender = ref<'male' | 'female'>()
const jobType = ref<'permanent' | 'contract' | 'daily_wage'>()
const productionNo = ref('')
const hireDate = ref('')
const branchId = ref<number | null>(null)

const branchOptions = ref<{ title: string; value: number }[]>([])

const degrees = [
  'الخاصة', 'الأولى', 'الثانية', 'الثالثة', 'الرابعة', 'الخامسة', 'السادسة', 'السابعة',
]

const producerRanks = [
  'معاون منتج', 'منتج', 'منتج أقدم',
  'معاون رئيس منتجين', 'رئيس منتجين', 'رئيس منتجين أقدم',
]

const adminRanks = [
  'كاتب', 'معاون ملاحظ', 'ملاحظ',
  'معاون رئيس ملاحظين', 'رئيس ملاحظين', 'معاون مدير', 'مدير',
]

const filteredRankOptions = computed(() =>
  jobTrack.value === 'producer' ? producerRanks
  : jobTrack.value === 'admin'  ? adminRanks
  : []
)

watch(jobTrack, () => { rank.value = undefined })

const educationLevels = [
  'دكتوراه', 'ماجستير', 'بكالوريوس', 'دبلوم عالي', 'دبلوم متوسط', 'إعدادية', 'متوسطة', 'ابتدائية',
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
  employeeNo.value = emp.employeeNo
  degree.value = emp.degree
  rank.value = emp.rank
  jobTrack.value = producerRanks.includes(emp.rank ?? '') ? 'producer'
    : adminRanks.includes(emp.rank ?? '') ? 'admin'
    : null
  education.value = emp.education
  gender.value = emp.gender
  jobType.value = emp.jobType
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

    if (props.employeeToEdit)
      fillForm(props.employeeToEdit)
  }
})

const closeDialog = () => {
  emit('update:isDialogVisible', false)
  nextTick(() => {
    refForm.value?.reset()
    refForm.value?.resetValidation()
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
        employeeNo: employeeNo.value,
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
        productionNo: productionNo.value || null,
        hireDate: hireDate.value,
        avatar: props.employeeToEdit?.avatar ?? null,
        branchId: branchId.value,
      })
      emit('update:isDialogVisible', false)
      nextTick(() => {
        refForm.value?.reset()
        refForm.value?.resetValidation()
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
    :width="$vuetify.display.smAndDown ? 'auto' : 900"
    :model-value="props.isDialogVisible"
    @update:model-value="dialogModelValueUpdate"
  >
    <DialogCloseBtn @click="dialogModelValueUpdate(false)" />

    <VCard class="pa-sm-8 pa-4">
      <VCardText>
        <h4 class="text-h4 text-center mb-2">
          {{ isEditMode ? 'تعديل بيانات الموظف' : 'إضافة موظف جديد' }}
        </h4>
        <p class="text-body-1 text-center mb-6">
          الرجاء تعبئة جميع الحقول المطلوبة
        </p>
        <VForm
          ref="refForm"
          v-model="isFormValid"
          class="mt-4"
          @submit.prevent="onSubmit"
        >
            <VRow>
              <!-- الاسم الأول -->
              <VCol
                cols="12"
                md="6"
              >
                <AppTextField
                  v-model="firstName"
                  :rules="[requiredValidator]"
                  label="الاسم الأول"
                  placeholder="أحمد"
                />
              </VCol>

              <!-- اسم الأب -->
              <VCol cols="12" md="6">
                <AppTextField
                  v-model="secondName"
                  :rules="[requiredValidator]"
                  label="اسم الأب"
                  placeholder="محمد"
                />
              </VCol>

              <!-- ── السطر الثاني: اسم الجد الأول + الجد الثاني + اللقب ── -->
              <VCol cols="12" md="4">
                <AppTextField
                  v-model="thirdName"
                  :rules="[requiredValidator]"
                  label="اسم الجد الأول"
                  placeholder="علي"
                />
              </VCol>

              <VCol cols="12" md="4">
                <AppTextField
                  v-model="fourthName"
                  :rules="[requiredValidator]"
                  label="اسم الجد الثاني"
                  placeholder="جاسم"
                />
              </VCol>

              <VCol cols="12" md="4">
                <AppTextField
                  v-model="lastName"
                  :rules="[requiredValidator]"
                  label="اللقب / العائلة"
                  placeholder="الموسوي"
                />
              </VCol>

              <!-- ── السطر الثالث: الجنس + الرقم الوظيفي ── -->
              <VCol cols="12" md="6">
                <AppSelect
                  v-model="gender"
                  :rules="[requiredValidator]"
                  label="الجنس"
                  placeholder="اختر الجنس"
                  :items="genderOptions"
                />
              </VCol>

              <VCol cols="12" md="6">
                <AppTextField
                  v-model="employeeNo"
                  :rules="[requiredValidator]"
                  label="الرقم الوظيفي"
                  placeholder="101009"
                />
              </VCol>

              <!-- ── السطر الرابع: الدرجة + نوع الموظف ── -->
              <VCol cols="12" md="6">
                <AppSelect
                  v-model="degree"
                  :rules="[requiredValidator]"
                  label="الدرجة"
                  placeholder="اختر الدرجة"
                  :items="degrees"
                />
              </VCol>

              <VCol cols="12" md="6">
                <div class="text-body-2 text-medium-emphasis mb-2">نوع الموظف</div>
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

              <!-- ── السطر الخامس: العنوان الوظيفي + الفرع ── -->
              <VCol cols="12" md="6">
                <AppSelect
                  v-model="rank"
                  :rules="[requiredValidator]"
                  :disabled="!jobTrack"
                  label="العنوان الوظيفي"
                  :placeholder="!jobTrack ? 'حدد نوع الموظف أولاً' : 'اختر العنوان الوظيفي'"
                  :items="filteredRankOptions"
                />
              </VCol>

              <VCol cols="12" md="6">
                <AppSelect
                  v-model="branchId"
                  label="الفرع"
                  placeholder="اختر الفرع"
                  :items="branchOptions"
                  :disabled="!!userData?.branch_id"
                  :readonly="!!userData?.branch_id"
                  :clearable="!userData?.branch_id"
                  clear-icon="tabler-x"
                />
              </VCol>

              <!-- ── السطر السادس: الشهادة + نوع الوظيفة ── -->
              <VCol cols="12" md="6">
                <AppSelect
                  v-model="education"
                  :rules="[requiredValidator]"
                  label="الشهادة"
                  placeholder="اختر الشهادة"
                  :items="educationLevels"
                />
              </VCol>

              <VCol cols="12" md="6">
                <AppSelect
                  v-model="jobType"
                  :rules="[requiredValidator]"
                  label="نوع الوظيفة"
                  placeholder="اختر نوع الوظيفة"
                  :items="jobTypeOptions"
                />
              </VCol>

              <!-- ── السطر السابع: تاريخ التعيين + الرقم الإنتاجي ── -->
              <VCol cols="12" md="6">
                <AppDateTimePicker
                  v-model="hireDate"
                  :rules="[requiredValidator]"
                  label="تاريخ التعيين"
                  placeholder="YYYY-MM-DD"
                  :config="{ dateFormat: 'Y-m-d', maxDate: 'today' }"
                />
              </VCol>

              <VCol cols="12" md="6">
                <AppTextField
                  v-model="productionNo"
                  label="الرقم الإنتاجي"
                  placeholder="2024009"
                />
              </VCol>

              <!-- أزرار الإرسال -->
              <VCol
                cols="12"
                class="d-flex flex-wrap justify-center gap-4"
              >
                <VBtn type="submit">
                  {{ isEditMode ? 'تحديث' : 'حفظ' }}
                </VBtn>
                <VBtn
                  color="secondary"
                  variant="tonal"
                  @click="closeDialog"
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
