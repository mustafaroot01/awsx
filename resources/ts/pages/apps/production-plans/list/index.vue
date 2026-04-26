<script setup lang="ts">
import AddProductionPlanDrawer from '@/views/apps/production-plans/list/AddProductionPlanDrawer.vue'
import type { ProductionPlan } from '@db/apps/production-plans/types'

definePage({
  meta: { action: 'read', subject: 'Auth' },
})

const selectedYear = ref<number | undefined>(undefined)

const { data: plansData, execute: fetchPlans } = await useApi<any>(createUrl('/apps/production-plans', {
  query: { year: selectedYear },
}))

const plans = computed((): ProductionPlan[] => plansData.value?.plans ?? [])
const totalPlans = computed(() => plansData.value?.totalPlans ?? 0)

const isDrawerOpen = ref(false)
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

const openAddDrawer = () => {
  selectedPlan.value = null
  isDrawerOpen.value = true
}

const openEditDrawer = (plan: ProductionPlan) => {
  selectedPlan.value = plan
  isDrawerOpen.value = true
}

const savePlan = async (planData: any) => {
  if (planData.id) {
    await $api(`/apps/production-plans/${planData.id}`, { method: 'PUT', body: planData })
  } else {
    await $api('/apps/production-plans', { method: 'POST', body: planData })
  }
  fetchPlans()
}

const deletePlan = async (id: number) => {
  await $api(`/apps/production-plans/${id}`, { method: 'DELETE' })
  fetchPlans()
}

const lockPlan = async (id: number) => {
  await $api(`/apps/production-plans/${id}/lock`, { method: 'POST' })
  fetchPlans()
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

const viewPlanTargets = (plan: ProductionPlan) => {
  activePlanTitle.value = plan.title
  
  // Format branch targets into a readable table format
  // Group by branchId
  const grouped: Record<number, any> = {}
  
  plan.branchTargets?.forEach(bt => {
    if (!grouped[bt.branchId]) {
      grouped[bt.branchId] = {
        branchId: bt.branchId,
        branchName: bt.branchName || `فرع ${bt.branchId}`,
        life: 0,
        group_health: 0,
        general_property: 0,
        total: 0,
      }
    }
    grouped[bt.branchId][bt.category] = bt.targetAmount
    grouped[bt.branchId].total += bt.targetAmount
  })
  
  previewTargets.value = Object.values(grouped)
  isPreviewDialogOpen.value = true
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
        <VBtn prepend-icon="tabler-plus" @click="openAddDrawer">إضافة خطة</VBtn>
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
                <VListItem @click="viewPlanTargets(item)">
                  <template #prepend><VIcon icon="tabler-eye" color="info" /></template>
                  <VListItemTitle>معاينة توزيع الأهداف</VListItemTitle>
                </VListItem>
                <VListItem @click="viewAchievements(item)">
                  <template #prepend><VIcon icon="tabler-chart-bar" /></template>
                  <VListItemTitle>عرض الإنجازات</VListItemTitle>
                </VListItem>
                <VListItem v-if="!item.isLocked" @click="openEditDrawer(item)">
                  <template #prepend><VIcon icon="tabler-pencil" /></template>
                  <VListItemTitle>تعديل</VListItemTitle>
                </VListItem>
                <VListItem v-if="!item.isLocked" @click="lockPlan(item.id)">
                  <template #prepend><VIcon icon="tabler-lock" color="warning" /></template>
                  <VListItemTitle>قفل الخطة</VListItemTitle>
                </VListItem>
                <VListItem v-if="!item.isLocked" @click="deletePlan(item.id)">
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
          <VTable density="compact" class="text-no-wrap">
            <thead>
              <tr class="bg-var-theme-background">
                <th class="font-weight-bold">الفرع</th>
                <th class="text-center font-weight-bold">تأمين الحياة</th>
                <th class="text-center font-weight-bold">الصحي الجماعي</th>
                <th class="text-center font-weight-bold">الممتلكات العامة</th>
                <th class="text-center font-weight-bold text-primary">إجمالي المستهدف</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in previewTargets" :key="row.branchId">
                <td class="font-weight-bold">{{ row.branchName }}</td>
                <td class="text-center">{{ formatCurrency(row.life) }}</td>
                <td class="text-center">{{ formatCurrency(row.group_health) }}</td>
                <td class="text-center">{{ formatCurrency(row.general_property) }}</td>
                <td class="text-center font-weight-black text-primary bg-light-primary">
                  {{ formatCurrency(row.total) }}
                </td>
              </tr>
            </tbody>
            <tfoot v-if="previewTargets.length > 0">
              <tr class="bg-var-theme-background">
                <td class="font-weight-black">المجموع الكلي</td>
                <td class="text-center font-weight-black">{{ formatCurrency(previewTargets.reduce((a, b) => a + b.life, 0)) }}</td>
                <td class="text-center font-weight-black">{{ formatCurrency(previewTargets.reduce((a, b) => a + b.group_health, 0)) }}</td>
                <td class="text-center font-weight-black">{{ formatCurrency(previewTargets.reduce((a, b) => a + b.general_property, 0)) }}</td>
                <td class="text-center font-weight-black text-primary">{{ formatCurrency(previewTargets.reduce((a, b) => a + b.total, 0)) }}</td>
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

    <!-- Drawer -->
    <AddProductionPlanDrawer
      v-model:is-drawer-open="isDrawerOpen"
      :plan-to-edit="selectedPlan"
      @plan-data="savePlan"
    />
  </section>
</template>
