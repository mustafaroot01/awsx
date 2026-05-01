<script setup lang="ts">
import type { Evaluation } from '@db/apps/evaluations/types'
import type { VForm } from 'vuetify/components/VForm'

interface Emit {
  (e: 'update:isDialogVisible', value: boolean): void
  (e: 'evaluationData', value: Evaluation): void
}
interface Props {
  isDialogVisible: boolean
  periodId: number
  evaluationToEdit?: Evaluation | null
  employeeId?: number | null
}

const props = withDefaults(defineProps<Props>(), { evaluationToEdit: null, employeeId: null })
const emit = defineEmits<Emit>()

const isEditMode = computed(() => !!props.evaluationToEdit?.id)
const isFormValid = ref(false)
const refForm = ref<VForm>()

// Employee data auto-fetched
const employeeDetail = ref<any>(null)
const adminPositions = ref<{ name: string; points: number }[]>([])

// Qualitative Data
const efficiencyExperience = ref('بلا تقييم')
const speedOfAchievement = ref('بلا تقييم')
const senseOfResponsibility = ref('بلا تقييم')
const behaviorWithOthers = ref('بلا تقييم')
const attendanceCommitment = ref('بلا تقييم')
const appreciationPenalties = ref('بلا تقييم')

// Incentive Data
const pointsCompetency = ref(23)
const actualWorkingDays = ref(60)
const notes = ref('')

const qualitativeOptions = ['بلا تقييم', 'ممتاز', 'جيد جداً', 'جيد', 'متوسط', 'ضعيف']
const employeeOptions = ref<{ title: string; value: number; branchId: number | null }[]>([])

// Calculate service years from hire date
const serviceYearsText = computed(() => {
  const hireDate = employeeDetail.value?.hireDate
  if (!hireDate) return '-'

  const hire = new Date(hireDate)
  const now = new Date()

  let years = now.getFullYear() - hire.getFullYear()
  let months = now.getMonth() - hire.getMonth()
  let days = now.getDate() - hire.getDate()

  if (days < 0) {
    months--
    days += 30
  }
  if (months < 0) {
    years--
    months += 12
  }

  const parts: string[] = []
  if (years > 0) parts.push(`${years} سنة`)
  if (months > 0) parts.push(`${months} شهر`)
  if (days > 0) parts.push(`${days} يوم`)

  return parts.length ? parts.join(' و ') : 'أقل من يوم'
})

// Calculate service years (raw number) for points calculation
const serviceYearsCount = computed(() => {
  const hireDate = employeeDetail.value?.hireDate
  if (!hireDate) return 0

  const hire = new Date(hireDate)
  const now = new Date()

  let years = now.getFullYear() - hire.getFullYear()
  const months = now.getMonth() - hire.getMonth()

  if (months < 0) years--
  return Math.max(0, years)
})

// Points calculations
const degreePoints = computed(() => {
  const degree = employeeDetail.value?.degree
  if (!degree) return 0

  // Extract number from degree string (e.g., "الدرجة الأولى" -> 1, "الدرجة 2" -> 2)
  const match = String(degree).match(/(\d+)/)
  const num = match ? parseInt(match[1]) : null

  if (num === 1) return 25
  if (num === 2 || num === 3) return 20
  if (num === 4 || num === 5) return 15
  if (num === 6) return 12
  if (num && num >= 7) return 10

  // Try Arabic numerals
  const arabicNum = String(degree).match(/(أولى|أول|1)/) ? 1 :
    String(degree).match(/(ثانية|ثاني|2)/) ? 2 :
    String(degree).match(/(ثالثة|ثالث|3)/) ? 3 :
    String(degree).match(/(رابعة|رابع|4)/) ? 4 :
    String(degree).match(/(خامسة|خامس|5)/) ? 5 :
    String(degree).match(/(سادسة|سادس|6)/) ? 6 :
    String(degree).match(/(سابعة|سابع|7)/) ? 7 : null

  if (arabicNum === 1) return 25
  if (arabicNum === 2 || arabicNum === 3) return 20
  if (arabicNum === 4 || arabicNum === 5) return 15
  if (arabicNum === 6) return 12
  if (arabicNum && arabicNum >= 7) return 10

  return 0
})

const educationPoints = computed(() => {
  const edu = String(employeeDetail.value?.education || '').toLowerCase()

  if (edu.includes('دكتوراه') || edu.includes('doctor') || edu.includes('phd')) return 15
  if (edu.includes('ماجستير') || edu.includes('master') || edu.includes('دبلوم عالي') || edu.includes('postgraduate')) return 13
  if (edu.includes('بكالوريوس') || edu.includes('bachelor') || edu.includes('ليسانس')) return 11
  if (edu.includes('دبلوم') || edu.includes('diploma')) return 10
  if (edu.includes('إعدادية') || edu.includes('ثانوية') || edu.includes('preparatory') || edu.includes('secondary')) return 8
  if (edu.includes('متوسطة') || edu.includes('إبتدائية') || edu.includes('intermediate') || edu.includes('primary')) return 5

  return 0
})

const serviceYearsPoints = computed(() => {
  const years = serviceYearsCount.value

  if (years >= 35) return 7
  if (years >= 25) return 6
  if (years >= 20) return 5
  if (years >= 15) return 4
  if (years >= 10) return 3
  if (years >= 5) return 2
  return 1
})

const adminPositionPoints = computed(() => {
  const posName = employeeDetail.value?.adminPosition
  if (!posName) return 0

  const pos = adminPositions.value.find(p => p.name === posName)
  return pos?.points ?? 0
})

// Qualitative criteria points map
const qualPointsMap: Record<string, number> = {
  'بلا تقييم': 0,
  'ممتاز': 7,
  'جيد جداً': 6,
  'جيد': 5,
  'متوسط': 4,
  'ضعيف': 3,
}

const qualitativePointsTotal = computed(() => {
  return (
    (qualPointsMap[efficiencyExperience.value] || 0) +
    (qualPointsMap[speedOfAchievement.value] || 0) +
    (qualPointsMap[senseOfResponsibility.value] || 0) +
    (qualPointsMap[behaviorWithOthers.value] || 0) +
    (qualPointsMap[attendanceCommitment.value] || 0)
  )
})

// Total score = employee data points + qualitative criteria points
const totalScore = computed(() => {
  const employeePoints = degreePoints.value + educationPoints.value + serviceYearsPoints.value + adminPositionPoints.value
  return employeePoints + qualitativePointsTotal.value
})

// Maximum possible = max employee points (25+15+7+maxAdmin) + 5 criteria × 7
const maxScore = computed(() => {
  const maxAdmin = adminPositions.value.length ? Math.max(...adminPositions.value.map(p => p.points), 0) : 0
  return 25 + 15 + 7 + maxAdmin + (5 * 7)
})

const fillForm = (ev: any) => {
  efficiencyExperience.value = ev.efficiencyExperience || 'جيد جداً'
  speedOfAchievement.value = ev.speedOfAchievement || 'جيد جداً'
  senseOfResponsibility.value = ev.senseOfResponsibility || 'جيد جداً'
  behaviorWithOthers.value = ev.behaviorWithOthers || 'جيد جداً'
  attendanceCommitment.value = ev.attendanceCommitment || 'جيد جداً'
  appreciationPenalties.value = ev.appreciationPenalties || 'جيد جداً'
  pointsCompetency.value = ev.pointsCompetency ?? 23
  actualWorkingDays.value = ev.actualWorkingDays ?? 60
  notes.value = ev.notes ?? ''
}

watch(() => props.isDialogVisible, async open => {
  if (open) {
    const empRes = await $api<any>('/apps/employees?itemsPerPage=-1')
    employeeOptions.value = (empRes?.employees ?? []).map((e: any) => ({
      title: `${e.firstName} ${e.lastName} (${e.employeeNo})`,
      value: e.id,
      branchId: e.branchId,
    }))

    // Fetch full employee details
    if (props.employeeId) {
      try {
        employeeDetail.value = await $api<any>(`/apps/employees/${props.employeeId}`)
      } catch (e) {
        employeeDetail.value = null
      }
    }

    // Fetch admin positions for points lookup
    try {
      const posRes = await $api<any>('/apps/admin-positions?active=true')
      const posArray = Array.isArray(posRes) ? posRes : posRes?.data ?? []
      adminPositions.value = posArray.map((p: any) => ({ name: p.name, points: p.points }))
    } catch {
      adminPositions.value = []
    }

    if (props.evaluationToEdit) fillForm(props.evaluationToEdit)
  }
})

const onSubmit = () => {
  console.log('Submitting evaluation...')
  refForm.value?.validate().then(({ valid }) => {
    if (!valid) {
      console.warn('Form validation failed')
      return
    }

    const data = {
      id: props.evaluationToEdit?.id,
      periodId: props.periodId,
      employeeId: props.employeeId!,
      branchId: employeeDetail.value?.branchId ?? null,
      scoreAttendance: 0,
      scorePerformance: 0,
      scoreBehavior: 0,
      scoreProduction: 0,
      scoreTeamwork: 0,
      notes: notes.value || null,
      efficiencyExperience: efficiencyExperience.value,
      speedOfAchievement: speedOfAchievement.value,
      senseOfResponsibility: senseOfResponsibility.value,
      behaviorWithOthers: behaviorWithOthers.value,
      attendanceCommitment: attendanceCommitment.value,
      appreciationPenalties: appreciationPenalties.value,
      pointsCompetency: pointsCompetency.value,
      actualWorkingDays: actualWorkingDays.value,
    }

    console.log('Emitting data:', data)
    emit('evaluationData', data)
    emit('update:isDialogVisible', false)
  })
}

const selectedEmployee = computed(() => {
  return employeeOptions.value.find(e => e.value === props.employeeId)
})
</script>

<template>
  <VDialog
    :model-value="props.isDialogVisible"
    :width="$vuetify.display.smAndDown ? 'auto' : 800"
    persistent
    @update:model-value="emit('update:isDialogVisible', $event)"
  >
    <DialogCloseBtn @click="emit('update:isDialogVisible', false)" />

    <VCard>
      <VCardItem class="py-3 px-4" density="compact">
        <template #prepend>
          <VAvatar color="primary" variant="tonal" rounded size="38" class="me-2">
            <VIcon icon="tabler-clipboard-check" size="20" />
          </VAvatar>
        </template>
        <VCardTitle class="text-subtitle-1 font-weight-bold">
          {{ isEditMode ? 'تعديل التقييم' : 'إجراء تقييم جديد' }}
        </VCardTitle>
        <VCardSubtitle class="text-caption">
          موظف: <span class="font-weight-bold text-primary">{{ selectedEmployee?.title }}</span>
        </VCardSubtitle>
      </VCardItem>

      <VDivider thickness="1" />

      <VCardText class="pt-5">
        <VForm ref="refForm" v-model="isFormValid" @submit.prevent="onSubmit">
          <VRow>
            <!-- Employee Info (Auto-fetched) -->
            <VCol cols="12" md="6">
              <div class="text-subtitle-1 font-weight-bold mb-4">بيانات الموظف</div>
              <div class="d-flex flex-column gap-4">
                <AppTextField
                  :model-value="employeeDetail?.degree || '-'"
                  label="الكفاءة / الدرجة"
                  readonly
                  variant="outlined"
                  hide-details
                  prepend-inner-icon="tabler-certificate"
                >
                  <template #append-inner>
                    <VChip color="primary" variant="elevated" size="small" class="font-weight-bold">
                      {{ degreePoints }} نقطة
                    </VChip>
                  </template>
                </AppTextField>
                <AppTextField
                  :model-value="employeeDetail?.rank || '-'"
                  label="العنوان الوظيفي"
                  readonly
                  variant="outlined"
                  hide-details
                  prepend-inner-icon="tabler-id-badge"
                />
                <AppTextField
                  :model-value="employeeDetail?.adminPosition || '-'"
                  label="المنصب الإداري"
                  readonly
                  variant="outlined"
                  hide-details
                  prepend-inner-icon="tabler-crown"
                >
                  <template #append-inner>
                    <VChip color="warning" variant="elevated" size="small" class="font-weight-bold">
                      {{ adminPositionPoints }} نقطة
                    </VChip>
                  </template>
                </AppTextField>
                <AppTextField
                  :model-value="employeeDetail?.education || '-'"
                  label="الشهادة"
                  readonly
                  variant="outlined"
                  hide-details
                  prepend-inner-icon="tabler-school"
                >
                  <template #append-inner>
                    <VChip color="info" variant="elevated" size="small" class="font-weight-bold">
                      {{ educationPoints }} نقطة
                    </VChip>
                  </template>
                </AppTextField>
                <AppTextField
                  :model-value="serviceYearsText"
                  label="سنوات الخدمة"
                  readonly
                  variant="outlined"
                  hide-details
                  prepend-inner-icon="tabler-calendar"
                >
                  <template #append-inner>
                    <VChip color="success" variant="elevated" size="small" class="font-weight-bold">
                      {{ serviceYearsPoints }} نقطة
                    </VChip>
                  </template>
                </AppTextField>
              </div>
            </VCol>

            <!-- Results & Summary -->
            <VCol cols="12" md="6">
              <VCard variant="outlined" class="pa-5 h-100 bg-var-theme-background">
                <!-- Header -->
                <div class="text-center mb-4">
                  <div class="text-overline">المجموع الكلي</div>
                  <div class="text-h2 font-weight-black text-primary">{{ totalScore }}</div>
                </div>

                <!-- Points Breakdown -->
                <VList density="compact" class="bg-transparent mb-4">
                  <VListItem class="px-0">
                    <template #prepend><VIcon icon="tabler-certificate" color="primary" size="18" /></template>
                    <span class="text-body-2">الدرجة</span>
                    <template #append><span class="font-weight-bold text-primary">{{ degreePoints }}</span></template>
                  </VListItem>
                  <VListItem class="px-0">
                    <template #prepend><VIcon icon="tabler-school" color="info" size="18" /></template>
                    <span class="text-body-2">الشهادة</span>
                    <template #append><span class="font-weight-bold text-info">{{ educationPoints }}</span></template>
                  </VListItem>
                  <VListItem class="px-0">
                    <template #prepend><VIcon icon="tabler-calendar" color="success" size="18" /></template>
                    <span class="text-body-2">سنوات الخدمة</span>
                    <template #append><span class="font-weight-bold text-success">{{ serviceYearsPoints }}</span></template>
                  </VListItem>
                  <VListItem class="px-0" v-if="adminPositionPoints > 0">
                    <template #prepend><VIcon icon="tabler-crown" color="warning" size="18" /></template>
                    <span class="text-body-2">المنصب الإداري</span>
                    <template #append><span class="font-weight-bold text-warning">{{ adminPositionPoints }}</span></template>
                  </VListItem>
                  <VListItem class="px-0">
                    <template #prepend><VIcon icon="tabler-star" color="secondary" size="18" /></template>
                    <span class="text-body-2">المعايير الوصفية</span>
                    <template #append><span class="font-weight-bold">{{ qualitativePointsTotal }}</span></template>
                  </VListItem>
                </VList>

              </VCard>
            </VCol>

            <!-- Work Data -->
            <VCol cols="12" class="mt-4">
              <VDivider class="mb-4" />
              <div class="text-subtitle-1 font-weight-bold mb-4">بيانات العمل</div>
              <VRow>
                <VCol cols="12" md="6">
                  <AppTextField v-model="actualWorkingDays" label="أيام العمل الفعلية" type="number" min="0" max="31" />
                </VCol>
                <VCol cols="12" md="6">
                  <AppTextarea v-model="notes" label="ملاحظات إضافية" placeholder="اكتب ملاحظاتك هنا..." rows="2" />
                </VCol>
              </VRow>
            </VCol>

            <!-- Qualitative Criteria -->
            <VCol cols="12" class="mt-2">
              <VDivider class="mb-4" />
              <div class="text-subtitle-1 font-weight-bold mb-4">المعايير الوصفية</div>
              <VRow>
                <VCol cols="12" sm="6" md="4">
                  <AppSelect v-model="efficiencyExperience" label="الكفاءة والخبرة" :items="qualitativeOptions" />
                </VCol>
                <VCol cols="12" sm="6" md="4">
                  <AppSelect v-model="speedOfAchievement" label="سرعة الإنجاز" :items="qualitativeOptions" />
                </VCol>
                <VCol cols="12" sm="6" md="4">
                  <AppSelect v-model="senseOfResponsibility" label="الشعور بالمسؤولية" :items="qualitativeOptions" />
                </VCol>
                <VCol cols="12" sm="6" md="4">
                  <AppSelect v-model="behaviorWithOthers" label="سلوكه مع الآخرين" :items="qualitativeOptions" />
                </VCol>
                <VCol cols="12" sm="6" md="4">
                  <AppSelect v-model="attendanceCommitment" label="الالتزام بالدوام" :items="qualitativeOptions" />
                </VCol>
                <VCol cols="12" sm="6" md="4">
                  <AppSelect v-model="appreciationPenalties" label="الشكر والعقوبات" :items="qualitativeOptions" />
                </VCol>
              </VRow>
            </VCol>

          </VRow>
        </VForm>
      </VCardText>

      <VDivider thickness="1" />

      <VCardActions class="justify-center gap-2 pa-3">
        <VBtn variant="tonal" color="secondary" class="px-4" @click="emit('update:isDialogVisible', false)">
          <VIcon start icon="tabler-x" size="16" />
          إلغاء
        </VBtn>
        <VBtn type="submit" color="primary" class="px-4" @click="onSubmit">
          <VIcon start icon="tabler-check" size="16" />
          {{ isEditMode ? 'تحديث التقييم' : 'حفظ التقييم' }}
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>

<style lang="scss" scoped>
.bg-var-theme-background {
  background-color: rgba(var(--v-theme-on-surface), 0.02) !important;
}
</style>
