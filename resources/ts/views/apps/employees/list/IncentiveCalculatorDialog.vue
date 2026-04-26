<script setup lang="ts">
import type { Employee } from '@db/apps/employees/types'

interface Props {
  isDialogVisible: boolean
  employee: Employee | null
}

interface Emit {
  (e: 'update:isDialogVisible', value: boolean): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

const actualWorkingDays = ref(240)
const competencyPoints = ref(23)
const selectedPenalties = ref([])
const calculationResult = ref<any>(null)
const loading = ref(false)

const penaltyOptions = [
  { title: 'لفت نظر', value: 'lfat_nazar' },
  { title: 'إنذار', value: 'warning' },
  { title: 'قطع راتب', value: 'salary_cut' },
  { title: 'تأخير ترفيع', value: 'promotion_delay' },
  { title: 'تنزيل درجة', value: 'grade_reduction' },
  { title: 'عزل', value: 'dismissal' },
  { title: 'ترك عمل/استقالة', value: 'resignation' },
]

const errorMessage = ref('')
const isSaving = ref(false)
const saveSuccess = ref(false)

const selectedMonth = ref(new Date().getMonth() + 1)
const currentYear = new Date().getFullYear()
const years = [currentYear - 1, currentYear, currentYear + 1]
const months = [
  { title: 'يناير', value: 1 }, { title: 'فبراير', value: 2 }, { title: 'مارس', value: 3 },
  { title: 'أبريل', value: 4 }, { title: 'مايو', value: 5 }, { title: 'يونيو', value: 6 },
  { title: 'يوليو', value: 7 }, { title: 'أغسطس', value: 8 }, { title: 'سبتمبر', value: 9 },
  { title: 'أكتوبر', value: 10 }, { title: 'نوفمبر', value: 11 }, { title: 'ديسمبر', value: 12 },
]

const calculate = async () => {
  if (!props.employee) return

  loading.value = true
  errorMessage.value = ''
  saveSuccess.value = false
  calculationResult.value = null
  
  try {
    const response = await $api<any>(`/apps/employees/${props.employee.id}/calculate-incentives`, {
      method: 'POST',
      body: {
        actualWorkingDays: Number(actualWorkingDays.value),
        competencyPoints: Number(competencyPoints.value),
        penalties: selectedPenalties.value,
      },
    })
    
    calculationResult.value = response
  } catch (error: any) {
    console.error('Calculation failed', error)
    errorMessage.value = error.response?._data?.message || 'حدث خطأ أثناء الاحتساب.'
  } finally {
    loading.value = false
  }
}

const saveResult = async () => {
  if (!props.employee || !calculationResult.value) return

  isSaving.value = true
  errorMessage.value = ''
  
  try {
    await $api(`/apps/employees/${props.employee.id}/incentives`, {
      method: 'POST',
      body: {
        year: currentYear,
        month: selectedMonth.value,
        actualWorkingDays: actualWorkingDays.value,
        competencyPoints: competencyPoints.value,
        totalPoints: calculationResult.value.total_points,
        netWorkingDays: calculationResult.value.net_working_days,
        penalties: selectedPenalties.value,
      },
    })
    saveSuccess.value = true
    setTimeout(() => {
      emit('update:isDialogVisible', false)
    }, 2000)
  } catch (error: any) {
    errorMessage.value = error.response?._data?.message || 'حدث خطأ أثناء حفظ النتائج.'
  } finally {
    isSaving.value = false
  }
}

watch(() => props.isDialogVisible, (val) => {
  if (!val) {
    calculationResult.value = null
    selectedPenalties.value = []
    saveSuccess.value = false
    errorMessage.value = ''
  }
})
</script>

<template>
  <VDialog
    :model-value="props.isDialogVisible"
    max-width="600"
    @update:model-value="val => emit('update:isDialogVisible', val)"
  >
    <VCard title="احتساب الحوافز">
      <VCardText v-if="employee">
        الموظف: <strong>{{ employee.firstName }} {{ employee.lastName }}</strong>
      </VCardText>

      <VCardText>
        <VRow>
          <VCol cols="12" md="4">
            <AppSelect
              v-model="selectedMonth"
              label="الشهر"
              :items="months"
              density="compact"
            />
          </VCol>
          <VCol cols="12" md="4">
            <AppTextField
              v-model="actualWorkingDays"
              label="أيام العمل الفعلية"
              type="number"
            />
          </VCol>
          <VCol cols="12" md="4">
            <AppTextField
              v-model="competencyPoints"
              label="نقاط الكفاءة (23-35)"
              type="number"
              min="23"
              max="35"
            />
          </VCol>
          <VCol cols="12">
            <AppSelect
              v-model="selectedPenalties"
              label="العقوبات الانضباطية"
              :items="penaltyOptions"
              multiple
              chips
              closable-chips
            />
          </VCol>
        </VRow>

        <VBtn
          block
          color="primary"
          class="mt-4"
          :loading="loading"
          @click="calculate"
        >
          احتساب الآن
        </VBtn>

        <VAlert
          v-if="errorMessage"
          type="error"
          variant="tonal"
          closable
          class="mt-4"
        >
          {{ errorMessage }}
        </VAlert>

        <VAlert
          v-if="saveSuccess"
          type="success"
          variant="tonal"
          class="mt-4"
        >
          تم حفظ نتائج الحوافز لشهر {{ months.find(m => m.value === selectedMonth)?.title }} بنجاح!
        </VAlert>

        <VDivider class="my-6" v-if="calculationResult" />

        <div v-if="calculationResult" class="calculation-results">
          <h6 class="text-h6 mb-4">تفاصيل الاحتساب:</h6>
          
          <VList lines="one" density="compact">
            <VListItem>
              <template #prepend><VIcon icon="tabler-award" class="me-2" /></template>
              نقاط الدرجة الوظيفية: <strong>{{ calculationResult.grade_points }}</strong>
            </VListItem>
            <VListItem>
              <template #prepend><VIcon icon="tabler-school" class="me-2" /></template>
              نقاط التحصيل الدراسي: <strong>{{ calculationResult.education_points }}</strong>
            </VListItem>
            <VListItem>
              <template #prepend><VIcon icon="tabler-calendar-time" class="me-2" /></template>
              نقاط سنوات الخدمة: <strong>{{ calculationResult.service_points }}</strong>
            </VListItem>
            <VListItem>
              <template #prepend><VIcon icon="tabler-star" class="me-2" /></template>
              نقاط الكفاءة: <strong>{{ calculationResult.competency_points }}</strong>
            </VListItem>
          </VList>

          <VAlert
            v-if="calculationResult.total_points === 0 && selectedPenalties.length > 0"
            color="warning"
            variant="tonal"
            class="mb-4"
            icon="tabler-alert-triangle"
          >
            تم تصفير الحوافز بسبب وجود عقوبة إدارية حرجة (مثل الاستقالة أو العزل).
          </VAlert>

          <VAlert
            color="primary"
            variant="tonal"
            class="mt-4"
          >
            <div class="d-flex justify-space-between align-center">
              <div>
                <div class="text-h5 font-weight-bold">{{ calculationResult.total_points }}</div>
                <div class="text-caption">مجموع النقاط النهائي</div>
              </div>
              <VDivider vertical class="mx-4" />
              <div>
                <div class="text-h5 font-weight-bold">{{ calculationResult.net_working_days }}</div>
                <div class="text-caption">أيام العمل الصافية</div>
              </div>
            </div>
          </VAlert>

          <VBtn
            block
            color="success"
            variant="elevated"
            class="mt-6"
            prepend-icon="tabler-device-floppy"
            :loading="isSaving"
            @click="saveResult"
          >
            حفظ النتائج للسجل الرسمي
          </VBtn>
        </div>
      </VCardText>

      <VCardText class="d-flex justify-end gap-3 flex-wrap">
        <VBtn
          color="secondary"
          variant="tonal"
          @click="emit('update:isDialogVisible', false)"
        >
          إغلاق
        </VBtn>
      </VCardText>
    </VCard>
  </VDialog>
</template>

<style scoped>
.calculation-results {
  background-color: rgb(var(--v-theme-surface-variant), 0.1);
  padding: 1rem;
  border-radius: 8px;
}
</style>
