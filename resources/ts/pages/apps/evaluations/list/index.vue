<script setup lang="ts">
import AddEvaluationPeriodDialog from '@/views/apps/evaluations/list/AddEvaluationPeriodDialog.vue'
import AddEvaluationDialog from '@/views/apps/evaluations/list/AddEvaluationDialog.vue'
import type { EvaluationPeriod, Evaluation } from '@db/apps/evaluations/types'

definePage({
  meta: { action: 'read', subject: 'Evaluation' },
})

const periods = ref<EvaluationPeriod[]>([])
const totalPeriods = ref(0)
const periodsLoading = ref(false)

const selectedYear = ref<number | undefined>(undefined)
const selectedStatus = ref<string | undefined>(undefined)

const fetchPeriods = async () => {
  periodsLoading.value = true
  try {
    const query = new URLSearchParams()
    if (selectedYear.value) query.append('year', String(selectedYear.value))
    if (selectedStatus.value) query.append('status', selectedStatus.value)
    
    const data = await $api<any>(`/apps/evaluation-periods?${query.toString()}`)
    periods.value = data?.periods ?? []
    totalPeriods.value = data?.totalPeriods ?? 0
  } catch (e) {
    console.error(e)
  } finally {
    periodsLoading.value = false
    tryAutoOpen()
  }
}

// Data
const activePeriod = ref<EvaluationPeriod | null>(null)
const evaluations = ref<Evaluation[]>([])
const employees = ref<any[]>([])
const dataLoading = ref(false)
const selectedBranchFilter = ref<number | undefined>(undefined)
const branchOptions = ref<{ title: string; value: number }[]>([])

// View Mode
const viewMode = ref<'all' | 'pending' | 'completed'>('all')

// User data
const userData = useCookie<any>('userData')
const isAdmin = computed(() => {
  const superAdmins = ['mus2afa30@gmail.com', 'admin@admin.com', 'mus@mus.com']
  return userData.value?.role === 'admin' || superAdmins.includes(userData.value?.email)
})
const isBranchManager = computed(() => !isAdmin.value && userData.value?.branch_id !== null)

const route = useRoute()

const tryAutoOpen = () => {
  const pid = route.query.periodId
  if (pid && periods.value.length > 0) {
    const p = periods.value.find(x => x.id === Number(pid))
    if (p) openPeriod(p)
  }
}

onMounted(async () => {
  const r = await $api<any>('/apps/branches?itemsPerPage=-1')
  branchOptions.value = (r?.branches ?? []).map((b: any) => ({ title: b.name, value: b.id }))
  
  if (userData.value?.branch_id) {
    selectedBranchFilter.value = userData.value.branch_id
  }

  await fetchPeriods()

  if (isBranchManager.value) {
    const activeData = await $api<any>('/apps/evaluation-periods/active')
    if (activeData?.activePeriod) {
      openPeriod(activeData.activePeriod)
    }
  }
})

watch([selectedYear, selectedStatus], fetchPeriods)

const fetchData = async () => {
  if (!activePeriod.value) return
  dataLoading.value = true
  try {
    const params = selectedBranchFilter.value ? `?branchId=${selectedBranchFilter.value}` : ''
    
    // Fetch both employees and existing evaluations in parallel
    const [evalsRes, empsRes] = await Promise.all([
      $api<any>(`/apps/evaluation-periods/${activePeriod.value.id}/evaluations${params}`),
      $api<any>(`/apps/employees?itemsPerPage=-1${params}`)
    ])
    
    evaluations.value = evalsRes?.evaluations ?? []
    employees.value = empsRes?.employees ?? []
  } catch (e) {
    console.error(e)
  } finally {
    dataLoading.value = false
  }
}

const openPeriod = (period: EvaluationPeriod) => {
  activePeriod.value = period
  fetchData()
}

watch(selectedBranchFilter, fetchData)

// Combined Data for Table
const tableData = computed(() => {
  return employees.value.map(emp => {
    const evaluation = evaluations.value.find(ev => ev.employeeId === emp.id)
    return {
      employeeId: emp.id,
      employeeName: `${emp.firstName} ${emp.lastName}`,
      employeeNo: emp.employeeNo,
      branchName: emp.branch?.name ?? 'المكتب الرئيسي',
      evaluation: evaluation ?? null,
      isEvaluated: !!evaluation
    }
  }).filter(item => {
    if (viewMode.value === 'pending') return !item.isEvaluated
    if (viewMode.value === 'completed') return item.isEvaluated
    return true
  })
})

// Dialogs
const isEvalDialogOpen = ref(false)
const isPeriodDialogOpen = ref(false)
const isLockConfirmDialogOpen = ref(false)
const periodToLock = ref<EvaluationPeriod | null>(null)
const selectedEvaluation = ref<Evaluation | null>(null)
const selectedEmployeeForEval = ref<number | null>(null)

const openEvalDialog = (item: any) => {
  if (item.isEvaluated) {
    selectedEvaluation.value = item.evaluation
    selectedEmployeeForEval.value = item.employeeId
  } else {
    selectedEvaluation.value = null
    selectedEmployeeForEval.value = item.employeeId
  }
  isEvalDialogOpen.value = true
}

const isSnackbarVisible = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref('success')

const showSnackbar = (message: string, color = 'success') => {
  snackbarMessage.value = message
  snackbarColor.value = color
  isSnackbarVisible.value = true
}

const saveEvaluation = async (ev: Evaluation) => {
  try {
    await $api(`/apps/evaluation-periods/${activePeriod.value!.id}/evaluations`, {
      method: 'POST',
      body: ev,
    })
    showSnackbar('تم حفظ التقييم بنجاح')
    fetchData()
  } catch (e) {
    showSnackbar('حدث خطأ أثناء حفظ التقييم', 'error')
  }
}

const deleteEvaluation = async (evalId: number) => {
  try {
    await $api(`/apps/evaluation-periods/${activePeriod.value!.id}/evaluations/${evalId}`, {
      method: 'DELETE',
    })
    showSnackbar('تم حذف التقييم بنجاح')
    fetchData()
  } catch (e) {
    showSnackbar('حدث خطأ أثناء حذف التقييم', 'error')
  }
}

const gradeColor = (grade: string) => {
  if (grade === 'ممتاز') return 'success'
  if (grade === 'جيد جداً') return 'info'
  if (grade === 'جيد') return 'primary'
  if (grade === 'مقبول') return 'warning'
  return 'error'
}

const savePeriod = async (data: any) => {
  try {
    await $api('/apps/evaluation-periods', {
      method: 'POST',
      body: data,
    })
    showSnackbar('تم فتح فترة التقييم بنجاح')
    fetchPeriods()
  } catch (e) {
    showSnackbar('حدث خطأ أثناء فتح فترة التقييم', 'error')
  }
}

const togglePeriodStatus = async (period: EvaluationPeriod) => {
  try {
    const data = await $api<any>(`/apps/evaluation-periods/${period.id}/toggle-status`, {
      method: 'POST'
    })
    showSnackbar(data.message)
    fetchPeriods()
  } catch (e) {
    showSnackbar('حدث خطأ أثناء تغيير حالة الفترة', 'error')
  }
}

const lockPeriod = (period: EvaluationPeriod) => {
  periodToLock.value = period
  isLockConfirmDialogOpen.value = true
}

const confirmLock = async () => {
  if (!periodToLock.value) return
  
  try {
    const data = await $api<any>(`/apps/evaluation-periods/${periodToLock.value.id}/lock`, {
      method: 'POST'
    })
    showSnackbar(data.message)
    isLockConfirmDialogOpen.value = false
    fetchPeriods()
  } catch (e) {
    showSnackbar('حدث خطأ أثناء إغلاق الفترة', 'error')
  }
}

import EmployeeEvaluationForm from '@/views/apps/evaluations/EmployeeEvaluationForm.vue'
const isPrintDialogOpen = ref(false)
const selectedEvalForPrint = ref<any>(null)

const openPrintDialog = (ev: any) => {
  selectedEvalForPrint.value = ev
  isPrintDialogOpen.value = true
}
</script>

<template>
  <section>
    <VRow>
      <!-- Periods Sidebar (Compact) -->
      <VCol v-if="!isBranchManager" cols="12" md="3">
        <VCard variant="outlined" :loading="periodsLoading" class="rounded-lg bg-surface">
          <VCardItem class="border-b py-3">
            <div class="d-flex align-center gap-2">
              <VIcon icon="tabler-list-details" size="20" class="text-primary" />
              <VCardTitle class="text-subtitle-1 font-weight-bold">فترات التقييم</VCardTitle>
            </div>
          </VCardItem>

          <VList nav density="comfortable" class="pa-2">
            <VListItem
              v-for="period in periods"
              :key="period.id"
              :active="activePeriod?.id === period.id"
              active-color="primary"
              class="mb-1 rounded-lg border-opacity-25"
              :class="activePeriod?.id === period.id ? 'border-primary' : 'border'"
              @click="openPeriod(period)"
            >
              <template #prepend>
                <VIcon 
                  :icon="period.status === 'locked' ? 'tabler-lock' : (period.status === 'suspended' ? 'tabler-circle-x' : 'tabler-circle-check')" 
                  :color="period.status === 'locked' ? 'error' : (period.status === 'suspended' ? 'warning' : 'success')"
                  size="18"
                />
              </template>
              
              <VListItemTitle class="font-weight-medium">
                {{ period.periodLabel }}
              </VListItemTitle>
              <VListItemSubtitle class="text-caption d-flex align-center gap-1">
                {{ period.year }} 
                <VChip v-if="period.status === 'suspended'" size="x-small" color="warning" variant="tonal">معلقة</VChip>
              </VListItemSubtitle>

              <template #append v-if="isAdmin">
                <div class="d-flex align-center gap-1">
                  <!-- Toggle Resume/Suspend -->
                  <VBtn
                    v-if="period.status !== 'locked'"
                    icon
                    variant="text"
                    size="x-small"
                    :color="period.status === 'open' ? 'warning' : 'success'"
                    @click.stop="togglePeriodStatus(period)"
                  >
                    <VIcon :icon="period.status === 'open' ? 'tabler-player-pause' : 'tabler-player-play'" />
                    <VTooltip activator="parent" location="top">{{ period.status === 'open' ? 'تعليق الفترة' : 'تفعيل الفترة' }}</VTooltip>
                  </VBtn>

                  <!-- Lock (Final Stop) -->
                  <VBtn
                    v-if="period.status !== 'locked'"
                    icon
                    variant="text"
                    size="x-small"
                    color="error"
                    @click.stop="lockPeriod(period)"
                  >
                    <VIcon icon="tabler-lock-square" />
                    <VTooltip activator="parent" location="top">إغلاق نهائي للفترة</VTooltip>
                  </VBtn>
                </div>
              </template>
            </VListItem>
          </VList>
          
          <VCardText v-if="periods.length === 0 && !periodsLoading" class="text-center py-6 text-disabled">
            لا توجد فترات تقييم حالياً
          </VCardText>

          <VDivider v-if="isAdmin" />
          <VCardActions v-if="isAdmin" class="pa-2">
            <VBtn
              v-if="$can('create', 'Evaluation')"
              block
              color="primary"
              variant="tonal"
              prepend-icon="tabler-plus"
              class="rounded-lg"
              @click="isPeriodDialogOpen = true"
            >
              فتح فترة تقييم جديدة
            </VBtn>
          </VCardActions>
        </VCard>
      </VCol>

      <!-- Main Evaluations View -->
      <VCol cols="12" :md="isBranchManager ? 12 : 9">
        <VCard variant="outlined" :loading="dataLoading" class="rounded-lg bg-surface shadow-none">
          <VCardItem class="pb-0">
            <div class="d-flex flex-wrap align-center justify-space-between gap-4">
              <div>
                <h4 class="text-h5 font-weight-bold mb-1">
                  <span v-if="activePeriod">تقييم موظفي: {{ activePeriod.year }} ({{ activePeriod.periodLabel }})</span>
                  <span v-else>اختر فترة التقييم</span>
                </h4>
                <p class="text-caption text-medium-emphasis mb-0">إدارة تقييمات الموظفين وإصدار استمارات التقييم</p>
              </div>

              <div class="d-flex gap-3 align-center">
                <AppSelect
                  v-model="selectedBranchFilter"
                  placeholder="فلتر الفرع"
                  :items="branchOptions"
                  :disabled="isBranchManager"
                  clearable
                  density="compact"
                  style="inline-size:200px"
                  variant="outlined"
                />
              </div>
            </div>

            <!-- Tabs/Filter Mode -->
            <VTabs v-model="viewMode" class="mt-4">
              <VTab value="all">الكل ({{ tableData.length }})</VTab>
              <VTab value="pending" color="error">بانتظار التقييم</VTab>
              <VTab value="completed" color="success">تم التقييم</VTab>
            </VTabs>
          </VCardItem>

          <VDivider />

          <VCardText v-if="!activePeriod" class="text-center py-16">
            <VIcon icon="tabler-users" size="80" color="primary" class="mb-4 opacity-20" />
            <div class="text-h4 font-weight-bold opacity-50">بانتظار اختيار فترة التقييم</div>
          </VCardText>

          <VDataTable
            v-else
            :items="tableData"
            :headers="[
              { title: 'الموظف', key: 'employeeName' },
              { title: 'الفرع', key: 'branchName' },
              { title: 'الحالة', key: 'status' },
              { title: 'الدرجة', key: 'totalScore' },
              { title: 'التقدير', key: 'grade' },
              { title: 'الإجراءات', key: 'actions', sortable: false, align: 'end' },
            ]"
            class="text-no-wrap"
            hover
          >
            <template #item.employeeName="{ item }">
              <div class="d-flex align-center gap-3 py-2">
                <VAvatar size="38" color="primary" variant="tonal" class="font-weight-bold">
                  {{ item.employeeName.charAt(0) }}
                </VAvatar>
                <div class="d-flex flex-column">
                  <span class="font-weight-bold text-high-emphasis">{{ item.employeeName }}</span>
                  <span class="text-caption text-medium-emphasis">رقم: {{ item.employeeNo }}</span>
                </div>
              </div>
            </template>

            <template #item.status="{ item }">
              <VChip v-if="item.isEvaluated" color="success" size="small" variant="tonal" prepend-icon="tabler-check">تم التقييم</VChip>
              <VChip v-else color="error" size="small" variant="tonal" prepend-icon="tabler-alert-circle">بانتظار التقييم</VChip>
            </template>

            <template #item.totalScore="{ item }">
              <span v-if="item.isEvaluated" class="font-weight-black text-h6">{{ item.evaluation.totalScore }}</span>
              <span v-else class="text-disabled">—</span>
            </template>

            <template #item.grade="{ item }">
              <VChip v-if="item.isEvaluated" :color="gradeColor(item.evaluation.grade ?? '')" size="small" label>{{ item.evaluation.grade }}</VChip>
              <span v-else class="text-disabled">—</span>
            </template>

            <template #item.actions="{ item }">
              <div class="d-flex justify-end gap-2">
                <!-- Branch Manager Actions: Evaluate & Edit (Only if period is OPEN) -->
                <template v-if="isBranchManager && activePeriod?.status === 'open'">
                  <VBtn
                    v-if="!item.isEvaluated"
                    color="primary"
                    size="small"
                    prepend-icon="tabler-bolt"
                    @click="openEvalDialog(item)"
                  >
                    بدء التقييم
                  </VBtn>
                  
                  <template v-else>
                    <VBtn v-if="$can('update', 'Evaluation')" icon variant="text" size="small" color="primary" @click="openEvalDialog(item)"><VIcon icon="tabler-edit" /></VBtn>
                    <VBtn v-if="$can('delete', 'Evaluation')" icon variant="text" size="small" color="error" @click="deleteEvaluation(item.evaluation.id!)"><VIcon icon="tabler-trash" /></VBtn>
                  </template>
                </template>

                <div v-else-if="isBranchManager && activePeriod?.status === 'suspended'" class="text-caption text-warning italic">الفترة معلقة حالياً</div>
                <div v-else-if="isBranchManager && activePeriod?.status === 'locked'" class="text-caption text-error italic">الفترة مقفلة نهائياً</div>

                <!-- Common Action: Print (Available for both Admin and Manager) -->
                <VBtn 
                  v-if="item.isEvaluated" 
                  icon 
                  variant="text" 
                  size="small" 
                  color="info" 
                  @click="openPrintDialog(item.evaluation)"
                >
                  <VIcon icon="tabler-printer" />
                </VBtn>

                <span v-if="isAdmin && !item.isEvaluated" class="text-caption text-disabled italic">بانتظار تقييم الفرع</span>
              </div>
            </template>

            <template #no-data>
              <div class="text-center py-12 text-medium-emphasis">لا يوجد موظفين في هذا الفرع حالياً</div>
            </template>
          </VDataTable>
        </VCard>
      </VCol>
    </VRow>

    <!-- Dialogs -->
    <AddEvaluationPeriodDialog v-model:is-dialog-visible="isPeriodDialogOpen" @period-data="savePeriod" />
    <AddEvaluationDialog 
      v-if="activePeriod" 
      v-model:is-dialog-visible="isEvalDialogOpen" 
      :period-id="activePeriod.id" 
      :employee-id="selectedEmployeeForEval"
      :evaluation-to-edit="selectedEvaluation" 
      @evaluation-data="saveEvaluation" 
    />
    
    <VDialog v-model="isPrintDialogOpen" max-width="900">
      <VCard class="rounded-xl">
        <VCardText class="pa-0"><EmployeeEvaluationForm v-if="selectedEvalForPrint" :evaluation="selectedEvalForPrint" /></VCardText>
        <VCardText class="d-flex justify-end gap-3 pa-4 border-t">
          <VBtn color="primary" prepend-icon="tabler-printer" @click="window.print()">طباعة الاستمارة</VBtn>
          <VBtn color="secondary" variant="tonal" @click="isPrintDialogOpen = false">إغلاق</VBtn>
        </VCardText>
      </VCard>
    </VDialog>

    <VDialog v-model="isLockConfirmDialogOpen" max-width="500">
      <VCard class="rounded-lg">
        <VCardItem class="bg-error text-white py-4">
          <div class="d-flex align-center gap-2">
            <VIcon icon="tabler-alert-triangle" color="white" />
            <VCardTitle class="text-white">تأكيد الإغلاق النهائي</VCardTitle>
          </div>
        </VCardItem>
        
        <VCardText class="pt-6 pb-2 text-center">
          <p class="text-h6 font-weight-bold mb-2">هل أنت متأكد من إغلاق هذه الفترة نهائياً؟</p>
          <p class="text-body-2 text-error">تحذير: لا يمكن التراجع عن هذه العملية أو إعادة تفعيل الفترة بعد إغلاقها.</p>
        </VCardText>

        <VCardActions class="pa-4 justify-center gap-3">
          <VBtn color="error" variant="elevated" @click="confirmLock">تأكيد الإغلاق</VBtn>
          <VBtn color="secondary" variant="tonal" @click="isLockConfirmDialogOpen = false">إلغاء</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- 👉 Snackbar -->
    <VSnackbar
      v-model="isSnackbarVisible"
      :color="snackbarColor"
      location="top end"
      variant="flat"
    >
      {{ snackbarMessage }}
    </VSnackbar>
  </section>
</template>

<style lang="scss" scoped>
.v-card {
  transition: box-shadow 0.2s ease-in-out;
}
.shadow-sm {
  box-shadow: 0 2px 4px rgba(0,0,0,0.05) !important;
}
</style>
