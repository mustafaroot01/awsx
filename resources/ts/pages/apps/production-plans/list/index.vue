<script setup lang="ts">
import { useRouter, useRoute } from 'vue-router'
import type { ProductionPlan } from '@db/apps/production-plans/types'
import { showPermissionError } from '@/utils/api'
import { useConfirmDelete } from '@/composables/useConfirmDelete'
import ExportFieldsDialog from '@/views/apps/employees/list/ExportFieldsDialog.vue'
import * as XLSX from 'xlsx'

const router = useRouter()
const route = useRoute()

definePage({
  meta: { action: 'read', subject: 'ProductionPlan' },
})

const selectedYear = ref<number | undefined>(undefined)
const branchIdFromQuery = computed(() => {
  const b = route.query.branchId
  return b ? Number(b) : undefined
})

const { data: plansData, execute: fetchPlans } = await useApi<any>(createUrl('/apps/production-plans', {
  query: { year: selectedYear, branchId: branchIdFromQuery },
}))

const plans = computed((): ProductionPlan[] => plansData.value?.plans ?? [])
const totalPlans = computed(() => plansData.value?.totalPlans ?? 0)

const selectedPlan = ref<ProductionPlan | null>(null)
const isAchievementsDialogOpen = ref(false)
const achievements = ref<any[]>([])
const achievementsLoading = ref(false)
const activePlanTitle = ref('')

const categoryLabels: Record<string, string> = {
  life: 'الحياة',
  group_health: 'الصحي الجماعي',
  general_property: 'الممتلكات',
}

const yearOptions = Array.from({ length: 6 }, (_, i) => {
  const y = new Date().getFullYear() - i
  return { title: String(y), value: y }
})

const openAddPage = () => router.push('/apps/production-plans/add')

const openEditPage = (plan: ProductionPlan) =>
  router.push({ path: '/apps/production-plans/add', query: { id: plan.id } })

const { open: openConfirmDelete } = useConfirmDelete()

const deletePlan = (plan: ProductionPlan) => {
  openConfirmDelete({
    id: plan.id,
    name: plan.title,
    title: 'حذف خطة إنتاجية',
    confirmLabel: 'نعم، احذف الخطة',
    async onConfirm() {
      await $api(`/apps/production-plans/${plan.id}`, { method: 'DELETE' })
      fetchPlans()
    },
  })
}

const lockPlan = async (id: number) => {
  try {
    await $api(`/apps/production-plans/${id}/lock`, { method: 'POST' })
    fetchPlans()
  } catch (e) {
    showPermissionError(e)
  }
}

const viewAchievements = async (plan: ProductionPlan) => {
  activePlanTitle.value = plan.title
  achievementsLoading.value = true
  isAchievementsDialogOpen.value = true
  const data = await $api<any[]>(`/apps/production-plans/${plan.id}/achievements`)
  achievements.value = Array.isArray(data) ? data : []
  achievementsLoading.value = false
}

const formatCurrency = (v: number) =>
  new Intl.NumberFormat('ar-IQ', { style: 'decimal', maximumFractionDigits: 0 }).format(v) + ' د.ع'

const getProgressColor = (pct: number) => {
  if (pct >= 100) return 'success'
  if (pct >= 70) return 'warning'
  return 'error'
}

// Preview Targets
const isPreviewDialogOpen = ref(false)
const previewTargets = ref<any[]>([])

const PROPERTY_CATEGORIES = ['vehicle', 'fire_theft', 'transport_marine', 'engineering', 'personal_accident', 'cash']

const viewPlanTargets = (plan: ProductionPlan) => {
  activePlanTitle.value = plan.title

  const grouped: Record<number, any> = {}

  plan.branchTargets?.forEach(bt => {
    if (!grouped[bt.branchId]) {
      grouped[bt.branchId] = {
        branchId: bt.branchId,
        branchName: bt.branchName || `فرع ${bt.branchId}`,
        life: 0, life_achieved: 0,
        group_health: 0, group_health_achieved: 0,
        general_property: 0, general_property_achieved: 0,
        total: 0, total_achieved: 0,
      }
    }

    if (PROPERTY_CATEGORIES.includes(bt.category)) {
      // Sum all property sub-categories into general_property
      grouped[bt.branchId].general_property += bt.targetAmount
      grouped[bt.branchId].general_property_achieved += bt.achievedAmount ?? 0
    } else {
      grouped[bt.branchId][bt.category] = bt.targetAmount
      grouped[bt.branchId][`${bt.category}_achieved`] = bt.achievedAmount ?? 0
    }

    grouped[bt.branchId].total += bt.targetAmount
    grouped[bt.branchId].total_achieved += bt.achievedAmount ?? 0
  })

  previewTargets.value = Object.values(grouped)
  isPreviewDialogOpen.value = true
}

// 👉 Export
const isExportDialogVisible = ref(false)

const exportFields = [
  { key: 'year', title: 'السنة', default: true },
  { key: 'title', title: 'العنوان', default: true },
  { key: 'total_amount', title: 'إجمالي الخطة', default: true },
  { key: 'branches', title: 'تفاصيل الفروع المشمولة', default: true },
  { key: 'is_locked', title: 'الحالة', default: false },
]

const handleExport = (type: 'pdf' | 'excel', selectedFields: string[]) => {
  if (type === 'excel')
    exportToExcel(selectedFields)
  else
    exportToPDF(selectedFields)
}

const exportToExcel = (selectedFields: string[]) => {
  const allFieldsMap: Record<string, (p: any) => [string, string]> = {
    year: p => ['السنة', String(p.year)],
    title: p => ['العنوان', p.title || ''],
    total_amount: p => ['إجمالي الخطة', String(p.totalAmount ?? p.total_amount ?? 0)],
    is_locked: p => ['الحالة', p.isLocked || p.is_locked ? 'مقفلة' : 'مفتوحة'],
    branches: p => {
      const branchDetails = (p.branchTargets || [])
        .reduce((acc: any, bt: any) => {
          if (!acc[bt.branchName]) {
            acc[bt.branchName] = { total: 0, life: 0, health: 0, property: 0 }
          }
          acc[bt.branchName].total += bt.targetAmount
          if (bt.category === 'life') acc[bt.branchName].life += bt.targetAmount
          else if (bt.category === 'group_health') acc[bt.branchName].health += bt.targetAmount
          else acc[bt.branchName].property += bt.targetAmount
          return acc
        }, {})
      
      const summary = Object.entries(branchDetails)
        .map(([name, d]: [string, any]) => {
          return `${name}: ${new Intl.NumberFormat('ar-IQ').format(d.total)} (حياة: ${d.life} | صحي: ${d.health} | ممتلكات: ${d.property})`
        })
        .join(' \n ')
      
      return ['تفاصيل الفروع', summary]
    }
  }

  // Add branches field to export if not already there but requested by logic
  const fieldsToExport = [...selectedFields]
  if (!fieldsToExport.includes('branches')) fieldsToExport.push('branches')

  const data = plans.value.map(p => {
    const obj: Record<string, string> = {}
    selectedFields.forEach(key => {
      const [label, value] = allFieldsMap[key](p)
      obj[label] = value
    })
    return obj
  })

  const ws = XLSX.utils.json_to_sheet(data)
  const wb = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(wb, ws, 'الخطط الإنتاجية')
  XLSX.writeFile(wb, `الخطط_الإنتاجية_${new Date().toISOString().split('T')[0]}.xlsx`)
}

const exportToPDF = async (selectedFields: string[]) => {
  const baseUrl = import.meta.env.VITE_API_BASE_URL || '/api'
  const params = new URLSearchParams()
  if (selectedYear.value) params.append('year', String(selectedYear.value))
  params.append('fields', selectedFields.join(','))

  const accessToken = useCookie('accessToken').value
  const url = `${baseUrl}/apps/production-plans/export-pdf?${params.toString()}`

  try {
    const response = await fetch(url, {
      headers: accessToken ? { Authorization: `Bearer ${accessToken}` } : {},
    })

    if (!response.ok) {
      // showNotification('فشل تصدير PDF', 'error')
      return
    }

    const blob = await response.blob()
    const downloadUrl = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = downloadUrl
    a.download = `الخطط_الإنتاجية_${new Date().toISOString().split('T')[0]}.pdf`
    document.body.appendChild(a)
    a.click()
    a.remove()
    window.URL.revokeObjectURL(downloadUrl)
  }
  catch {
    // showNotification('فشل تصدير PDF', 'error')
  }
}
</script>

<template>
  <section>
    <!-- Widgets -->
    <VRow class="mb-6">
      <VCol cols="12" sm="6" lg="3">
        <VCard>
          <VCardText>
            <div class="d-flex justify-space-between">
              <div class="d-flex flex-column gap-y-1">
                <span class="text-body-1 text-high-emphasis">إجمالي الخطط</span>
                <h4 class="text-h4">{{ totalPlans }}</h4>
              </div>
              <VAvatar color="primary" variant="tonal" rounded size="42">
                <VIcon icon="tabler-clipboard-list" size="26" />
              </VAvatar>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Table Card -->
    <VCard>
      <VCardItem class="pb-4">
        <VCardTitle>الخطط الإنتاجية</VCardTitle>
      </VCardItem>

      <VCardText>
        <VRow>
          <VCol cols="12" sm="4">
            <AppSelect
              v-model="selectedYear"
              placeholder="فلتر: السنة"
              :items="yearOptions"
              clearable
              clear-icon="tabler-x"
            />
          </VCol>
        </VRow>
      </VCardText>

      <VDivider />

      <VCardText class="d-flex flex-wrap justify-end gap-4">
        <VBtn
          variant="tonal"
          color="secondary"
          prepend-icon="tabler-upload"
          @click="isExportDialogVisible = true"
        >
          تصدير
        </VBtn>
        <VBtn v-if="$can('create', 'ProductionPlan')" prepend-icon="tabler-plus" @click="openAddPage">إضافة خطة</VBtn>
      </VCardText>

      <VDivider />

      <VDataTable
        :items="plans"
        :headers="[
          { title: 'السنة', key: 'year' },
          { title: 'العنوان', key: 'title' },
          { title: 'إجمالي الخطة', key: 'totalAmount' },
          { title: 'الحالة', key: 'isLocked' },
          { title: 'الفئات', key: 'categories' },
          { title: 'الإجراءات', key: 'actions', sortable: false },
        ]"
        item-value="id"
        class="text-no-wrap"
      >
        <template #item.totalAmount="{ item }">
          <span class="font-weight-medium text-primary">{{ formatCurrency(item.totalAmount) }}</span>
        </template>

        <template #item.isLocked="{ item }">
          <VChip :color="item.isLocked ? 'error' : 'success'" size="small" label>
            <VIcon start :icon="item.isLocked ? 'tabler-lock' : 'tabler-lock-open'" size="14" />
            {{ item.isLocked ? 'مقفلة' : 'مفتوحة' }}
          </VChip>
        </template>

        <template #item.categories="{ item }">
          <div class="d-flex gap-2 flex-wrap">
            <VChip
              v-for="cat in item.categories"
              :key="cat.category"
              size="x-small"
              color="secondary"
              variant="tonal"
            >
              {{ categoryLabels[cat.category] }}: {{ formatCurrency(cat.targetAmount) }}
            </VChip>
          </div>
        </template>

        <template #item.actions="{ item }">
          <VBtn icon variant="text" color="medium-emphasis">
            <VIcon icon="tabler-dots-vertical" />
            <VMenu activator="parent">
              <VList>
                <VListItem @click="router.push(`/apps/production-plans/${item.id}`)">
                  <template #prepend><VIcon icon="tabler-file-description" color="primary" /></template>
                  <VListItemTitle>عرض تفاصيل الخطة</VListItemTitle>
                </VListItem>
                <VListItem @click="viewPlanTargets(item)">
                  <template #prepend><VIcon icon="tabler-eye" color="info" /></template>
                  <VListItemTitle>معاينة توزيع الأهداف</VListItemTitle>
                </VListItem>
                <VListItem @click="viewAchievements(item)">
                  <template #prepend><VIcon icon="tabler-chart-bar" /></template>
                  <VListItemTitle>عرض الإنجازات</VListItemTitle>
                </VListItem>
                <VListItem v-if="!item.isLocked && $can('update', 'ProductionPlan')" @click="openEditPage(item)">
                  <template #prepend><VIcon icon="tabler-pencil" /></template>
                  <VListItemTitle>تعديل</VListItemTitle>
                </VListItem>
                <VListItem v-if="!item.isLocked && $can('update', 'ProductionPlan')" @click="lockPlan(item.id)">
                  <template #prepend><VIcon icon="tabler-lock" color="warning" /></template>
                  <VListItemTitle>قفل الخطة</VListItemTitle>
                </VListItem>
                <VListItem v-if="!item.isLocked && $can('delete', 'ProductionPlan')" @click="deletePlan(item)">
                  <template #prepend><VIcon icon="tabler-trash" /></template>
                  <VListItemTitle>حذف</VListItemTitle>
                </VListItem>
              </VList>
            </VMenu>
          </VBtn>
        </template>
      </VDataTable>
    </VCard>

    <!-- Preview Targets Dialog -->
    <VDialog v-model="isPreviewDialogOpen" max-width="1000">
      <VCard>
        <VCardTitle class="pa-4 d-flex justify-space-between align-center bg-light-primary">
          <div class="d-flex align-center gap-2">
            <VIcon icon="tabler-eye" color="primary" />
            <span class="font-weight-bold">معاينة توزيع أهداف الفروع — {{ activePlanTitle }}</span>
          </div>
          <VBtn icon variant="text" @click="isPreviewDialogOpen = false">
            <VIcon icon="tabler-x" />
          </VBtn>
        </VCardTitle>
        <VDivider />
        <VCardText class="pa-0">
          <VTable density="comfortable" class="text-no-wrap">
            <thead>
              <tr class="bg-var-theme-background">
                <th class="font-weight-bold" style="min-width:140px">الفرع</th>
                <th class="text-center font-weight-bold text-success" style="min-width:180px">تأمين الحياة</th>
                <th class="text-center font-weight-bold text-info" style="min-width:180px">الصحي الجماعي</th>
                <th class="text-center font-weight-bold text-warning" style="min-width:180px">الممتلكات العامة</th>
                <th class="text-center font-weight-bold text-primary" style="min-width:180px">الإجمالي</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in previewTargets" :key="row.branchId">
                <td class="font-weight-bold py-3">{{ row.branchName }}</td>

                <!-- Life -->
                <td class="py-2 px-3">
                  <div class="text-caption text-medium-emphasis">المستهدف: {{ formatCurrency(row.life) }}</div>
                  <div class="text-caption text-success font-weight-medium">المنجز: {{ formatCurrency(row.life_achieved) }}</div>
                  <VProgressLinear
                    :model-value="row.life > 0 ? Math.min((row.life_achieved / row.life) * 100, 100) : 0"
                    :color="row.life_achieved >= row.life ? 'success' : row.life_achieved / row.life >= 0.7 ? 'warning' : 'error'"
                    rounded height="6" class="my-1"
                  />
                  <div class="text-caption font-weight-bold">
                    {{ row.life > 0 ? ((row.life_achieved / row.life) * 100).toFixed(1) : 0 }}%
                  </div>
                </td>

                <!-- Group Health -->
                <td class="py-2 px-3">
                  <div class="text-caption text-medium-emphasis">المستهدف: {{ formatCurrency(row.group_health) }}</div>
                  <div class="text-caption text-success font-weight-medium">المنجز: {{ formatCurrency(row.group_health_achieved) }}</div>
                  <VProgressLinear
                    :model-value="row.group_health > 0 ? Math.min((row.group_health_achieved / row.group_health) * 100, 100) : 0"
                    :color="row.group_health_achieved >= row.group_health ? 'success' : row.group_health_achieved / row.group_health >= 0.7 ? 'warning' : 'error'"
                    rounded height="6" class="my-1"
                  />
                  <div class="text-caption font-weight-bold">
                    {{ row.group_health > 0 ? ((row.group_health_achieved / row.group_health) * 100).toFixed(1) : 0 }}%
                  </div>
                </td>

                <!-- General Property -->
                <td class="py-2 px-3">
                  <div class="text-caption text-medium-emphasis">المستهدف: {{ formatCurrency(row.general_property) }}</div>
                  <div class="text-caption text-success font-weight-medium">المنجز: {{ formatCurrency(row.general_property_achieved) }}</div>
                  <VProgressLinear
                    :model-value="row.general_property > 0 ? Math.min((row.general_property_achieved / row.general_property) * 100, 100) : 0"
                    :color="row.general_property_achieved >= row.general_property ? 'success' : row.general_property_achieved / row.general_property >= 0.7 ? 'warning' : 'error'"
                    rounded height="6" class="my-1"
                  />
                  <div class="text-caption font-weight-bold">
                    {{ row.general_property > 0 ? ((row.general_property_achieved / row.general_property) * 100).toFixed(1) : 0 }}%
                  </div>
                </td>

                <!-- Total -->
                <td class="py-2 px-3 bg-light-primary">
                  <div class="text-caption text-medium-emphasis">المستهدف: {{ formatCurrency(row.total) }}</div>
                  <div class="text-caption text-success font-weight-medium">المنجز: {{ formatCurrency(row.total_achieved) }}</div>
                  <VProgressLinear
                    :model-value="row.total > 0 ? Math.min((row.total_achieved / row.total) * 100, 100) : 0"
                    :color="row.total_achieved >= row.total ? 'success' : row.total_achieved / row.total >= 0.7 ? 'warning' : 'error'"
                    rounded height="6" class="my-1"
                  />
                  <div class="text-caption font-weight-black text-primary">
                    {{ row.total > 0 ? ((row.total_achieved / row.total) * 100).toFixed(1) : 0 }}%
                  </div>
                </td>
              </tr>
            </tbody>

            <!-- Totals Footer -->
            <tfoot v-if="previewTargets.length > 0">
              <tr class="bg-var-theme-background">
                <td class="font-weight-black py-3">المجموع الكلي</td>
                <td class="py-2 px-3">
                  <div class="text-caption">{{ formatCurrency(previewTargets.reduce((a, b) => a + b.life, 0)) }}</div>
                  <div class="text-caption text-success">{{ formatCurrency(previewTargets.reduce((a, b) => a + b.life_achieved, 0)) }}</div>
                </td>
                <td class="py-2 px-3">
                  <div class="text-caption">{{ formatCurrency(previewTargets.reduce((a, b) => a + b.group_health, 0)) }}</div>
                  <div class="text-caption text-success">{{ formatCurrency(previewTargets.reduce((a, b) => a + b.group_health_achieved, 0)) }}</div>
                </td>
                <td class="py-2 px-3">
                  <div class="text-caption">{{ formatCurrency(previewTargets.reduce((a, b) => a + b.general_property, 0)) }}</div>
                  <div class="text-caption text-success">{{ formatCurrency(previewTargets.reduce((a, b) => a + b.general_property_achieved, 0)) }}</div>
                </td>
                <td class="py-2 px-3 bg-light-primary">
                  <div class="text-caption font-weight-black">{{ formatCurrency(previewTargets.reduce((a, b) => a + b.total, 0)) }}</div>
                  <div class="text-caption text-success font-weight-black">{{ formatCurrency(previewTargets.reduce((a, b) => a + b.total_achieved, 0)) }}</div>
                </td>
              </tr>
            </tfoot>
          </VTable>

          <div v-if="previewTargets.length === 0" class="text-center py-10 text-medium-emphasis">
            لا توجد مستهدفات موزعة لهذه الخطة
          </div>
        </VCardText>
        <VDivider />
        <VCardActions class="pa-4">
          <VSpacer />
          <VBtn color="secondary" variant="tonal" @click="isPreviewDialogOpen = false">إغلاق المعاينة</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Achievements Dialog -->
    <VDialog v-model="isAchievementsDialogOpen" max-width="900">
      <VCard>
        <VCardTitle class="pa-4 d-flex justify-space-between align-center">
          <span>إنجازات الفروع — {{ activePlanTitle }}</span>
          <VBtn icon variant="text" @click="isAchievementsDialogOpen = false">
            <VIcon icon="tabler-x" />
          </VBtn>
        </VCardTitle>
        <VDivider />
        <VCardText>
          <div v-if="achievementsLoading" class="d-flex justify-center pa-8">
            <VProgressCircular indeterminate color="primary" />
          </div>
          <div v-else-if="achievements.length === 0" class="text-center text-medium-emphasis pa-8">
            لا توجد بيانات إنجاز حتى الآن
          </div>
          <VTable v-else density="compact">
            <thead>
              <tr>
                <th>الفرع</th>
                <th v-for="cat in ['life','group_health','general_property']" :key="cat">
                  {{ categoryLabels[cat] }} (المستهدف / المحقق / %)
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in achievements" :key="row.branchId">
                <td class="font-weight-medium">{{ row.branchName }}</td>
                <td v-for="cat in ['life','group_health','general_property']" :key="cat">
                  <div class="d-flex flex-column gap-1" style="min-width:160px">
                    <span class="text-caption text-medium-emphasis">{{ formatCurrency(row.categories[cat]?.target ?? 0) }}</span>
                    <span class="text-caption text-success font-weight-medium">{{ formatCurrency(row.categories[cat]?.achieved ?? 0) }}</span>
                    <VProgressLinear
                      :model-value="row.categories[cat]?.percentage ?? 0"
                      :color="getProgressColor(row.categories[cat]?.percentage ?? 0)"
                      rounded
                      height="6"
                    />
                    <span class="text-caption font-weight-bold">{{ row.categories[cat]?.percentage ?? 0 }}%</span>
                  </div>
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCardText>
      </VCard>
    </VDialog>
    
    <ExportFieldsDialog
      v-model="isExportDialogVisible"
      :fields="exportFields"
      @export="handleExport"
    />

  </section>
</template>
