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

// Evaluation Scores
const branchId = ref<number | null>(null)
const scoreAttendance = ref(100)
const scorePerformance = ref(100)
const scoreBehavior = ref(100)
const scoreProduction = ref(100)
const scoreTeamwork = ref(100)
const notes = ref('')

// Qualitative Data
const efficiencyExperience = ref('جيد جداً')
const speedOfAchievement = ref('جيد جداً')
const senseOfResponsibility = ref('جيد جداً')
const behaviorWithOthers = ref('جيد جداً')
const attendanceCommitment = ref('جيد جداً')
const appreciationPenalties = ref('جيد جداً')

// Incentive Data
const pointsCompetency = ref(23)
const actualWorkingDays = ref(60)

const qualitativeOptions = ['ممتاز', 'جيد جداً', 'جيد', 'متوسط', 'ضعيف']
const employeeOptions = ref<{ title: string; value: number; branchId: number | null }[]>([])

const totalScore = computed(() => 
  Number(scoreAttendance.value) + 
  Number(scorePerformance.value) + 
  Number(scoreBehavior.value) + 
  Number(scoreProduction.value) + 
  Number(scoreTeamwork.value)
)

const grade = computed(() => {
  const t = totalScore.value
  if (t >= 450) return { label: 'ممتاز', color: 'success' }
  if (t >= 375) return { label: 'جيد جداً', color: 'info' }
  if (t >= 300) return { label: 'جيد', color: 'primary' }
  if (t >= 200) return { label: 'مقبول', color: 'warning' }
  return { label: 'ضعيف', color: 'error' }
})

const fillForm = (ev: any) => {
  branchId.value = ev.branchId
  scoreAttendance.value = ev.scoreAttendance || 0
  scorePerformance.value = ev.scorePerformance || 0
  scoreBehavior.value = ev.scoreBehavior || 0
  scoreProduction.value = ev.scoreProduction || 0
  scoreTeamwork.value = ev.scoreTeamwork || 0
  notes.value = ev.notes ?? ''
  efficiencyExperience.value = ev.efficiencyExperience || 'جيد جداً'
  speedOfAchievement.value = ev.speedOfAchievement || 'جيد جداً'
  senseOfResponsibility.value = ev.senseOfResponsibility || 'جيد جداً'
  behaviorWithOthers.value = ev.behaviorWithOthers || 'جيد جداً'
  attendanceCommitment.value = ev.attendanceCommitment || 'جيد جداً'
  appreciationPenalties.value = ev.appreciationPenalties || 'جيد جداً'
  pointsCompetency.value = ev.pointsCompetency ?? 23
  actualWorkingDays.value = ev.actualWorkingDays ?? 60
}

watch(() => props.isDialogVisible, async open => {
  if (open) {
    const empRes = await $api<any>('/apps/employees?itemsPerPage=-1')
    employeeOptions.value = (empRes?.employees ?? []).map((e: any) => ({
      title: `${e.firstName} ${e.lastName} (${e.employeeNo})`,
      value: e.id,
      branchId: e.branchId,
    }))

    if (props.evaluationToEdit) fillForm(props.evaluationToEdit)
    else {
      // Pre-select employee if passed from parent
      if (props.employeeId) {
        const emp = employeeOptions.value.find(e => e.value === props.employeeId)
        if (emp) branchId.value = emp.branchId
      }
      scoreAttendance.value = 100
      scorePerformance.value = 100
      scoreBehavior.value = 100
      scoreProduction.value = 100
      scoreTeamwork.value = 100
    }
  }
})

// Validators
const scoreValidator = (val: any) => {
  const num = Number(val)
  if (num < 0 || num > 100) return 'يجب أن تكون الدرجة بين 0 و 100'
  return true
}

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
      branchId: branchId.value,
      scoreAttendance: scoreAttendance.value,
      scorePerformance: scorePerformance.value,
      scoreBehavior: scoreBehavior.value,
      scoreProduction: scoreProduction.value,
      scoreTeamwork: scoreTeamwork.value,
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

    <VCard class="pa-sm-8 pa-4">
      <VCardText>
        <div class="mb-6">
          <h4 class="text-h4 mb-1">
            {{ isEditMode ? 'تعديل التقييم' : 'إجراء تقييم جديد' }}
          </h4>
          <p class="text-body-1 text-medium-emphasis">
            موظف: <span class="font-weight-bold text-primary">{{ selectedEmployee?.title }}</span>
          </p>
        </div>

        <VForm ref="refForm" v-model="isFormValid" @submit.prevent="onSubmit">
          <VRow>
            <!-- Score Inputs -->
            <VCol cols="12" md="6">
              <div class="d-flex flex-column gap-4">
                <AppTextField v-model="scoreAttendance" label="الحضور والانضباط" type="number" :rules="[scoreValidator]" prepend-inner-icon="tabler-clock" />
                <AppTextField v-model="scorePerformance" label="الأداء الوظيفي" type="number" :rules="[scoreValidator]" prepend-inner-icon="tabler-briefcase" />
                <AppTextField v-model="scoreBehavior" label="السلوك والتعاون" type="number" :rules="[scoreValidator]" prepend-inner-icon="tabler-users" />
                <AppTextField v-model="scoreProduction" label="الإنتاجية" type="number" :rules="[scoreValidator]" prepend-inner-icon="tabler-target" />
                <AppTextField v-model="scoreTeamwork" label="روح الفريق" type="number" :rules="[scoreValidator]" prepend-inner-icon="tabler-heart-handshake" />
              </div>
            </VCol>

            <!-- Results & Summary -->
            <VCol cols="12" md="6">
              <VCard variant="outlined" class="pa-6 h-100 d-flex flex-column justify-center align-center text-center bg-var-theme-background">
                <div class="text-overline mb-2">المجموع الكلي</div>
                <div class="text-h1 font-weight-black text-primary mb-2">{{ totalScore }}</div>
                <div class="text-body-2 text-medium-emphasis mb-6">من أصل 500 درجة</div>

                <VChip :color="grade.color" size="large" class="font-weight-bold px-8 py-4 h-auto" variant="elevated">
                  التقدير: {{ grade.label }}
                </VChip>

                <div class="mt-8 w-100">
                  <AppTextField v-model="notes" label="ملاحظات إضافية" placeholder="اكتب ملاحظاتك هنا..." />
                </div>
              </VCard>
            </VCol>

            <!-- Secondary Info -->
            <VCol cols="12" class="mt-4">
              <VDivider class="mb-6" />
              <div class="text-subtitle-1 font-weight-bold mb-4">المعايير التحفيزية والوصفية</div>
              <VRow>
                <VCol cols="12" md="6"><AppTextField v-model="pointsCompetency" label="نقاط الكفاءة" type="number" /></VCol>
                <VCol cols="12" md="6"><AppTextField v-model="actualWorkingDays" label="أيام العمل الفعلية" type="number" /></VCol>
                
                <VCol cols="12" sm="6" md="4"><AppSelect v-model="efficiencyExperience" label="الكفاءة والخبرة" :items="qualitativeOptions" /></VCol>
                <VCol cols="12" sm="6" md="4"><AppSelect v-model="speedOfAchievement" label="سرعة الإنجاز" :items="qualitativeOptions" /></VCol>
                <VCol cols="12" sm="6" md="4"><AppSelect v-model="senseOfResponsibility" label="الشعور بالمسؤولية" :items="qualitativeOptions" /></VCol>
                <VCol cols="12" sm="6" md="4"><AppSelect v-model="behaviorWithOthers" label="سلوكه مع الآخرين" :items="qualitativeOptions" /></VCol>
                <VCol cols="12" sm="6" md="4"><AppSelect v-model="attendanceCommitment" label="الالتزام بالدوام" :items="qualitativeOptions" /></VCol>
                <VCol cols="12" sm="6" md="4"><AppSelect v-model="appreciationPenalties" label="الشكر والعقوبات" :items="qualitativeOptions" /></VCol>
              </VRow>
            </VCol>

            <!-- Actions -->
            <VCol cols="12" class="d-flex justify-center gap-4 mt-8">
              <VBtn type="submit" size="large" class="px-10">حفظ التقييم</VBtn>
              <VBtn variant="tonal" color="secondary" size="large" @click="emit('update:isDialogVisible', false)">إلغاء</VBtn>
            </VCol>
          </VRow>
        </VForm>
      </VCardText>
    </VCard>
  </VDialog>
</template>

<style lang="scss" scoped>
.bg-var-theme-background {
  background-color: rgba(var(--v-theme-on-surface), 0.02) !important;
}
</style>
