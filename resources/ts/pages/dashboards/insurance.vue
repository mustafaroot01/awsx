<script setup lang="ts">
definePage({
  meta: { action: 'read', subject: 'Auth' },
})

const selectedYear = ref(new Date().getFullYear())

const yearOptions = Array.from({ length: 5 }, (_, i) => {
  const y = new Date().getFullYear() - i
  return { title: String(y), value: y }
})

const userData = useCookie<any>('userData')
const isAdmin = computed(() => userData.value?.role === 'admin')
const branchId = computed(() => userData.value?.branch_id)
const isBranchManager = computed(() => !isAdmin.value && !!branchId.value)

// 👉 Fetch stats data
const { data: statsData } = await useApi<any>(createUrl('/apps/policies/stats', {
  query: { year: selectedYear },
}))

const { data: empData } = await useApi<any>(createUrl('/apps/employees', {
  query: { itemsPerPage: -1, branchId: branchId.value },
}))

const { data: branchData } = await useApi<any>(createUrl('/apps/branches', {
  query: { itemsPerPage: -1 },
}))

const { data: plansData } = await useApi<any>(createUrl('/apps/production-plans', {
  query: { year: selectedYear },
}))

const { data: activePeriodData } = await useApi<any>(createUrl('/apps/evaluation-periods/active'))
const hasActivePeriod = computed(() => !!activePeriodData.value?.activePeriod)
const activePeriodLabel = computed(() => activePeriodData.value?.activePeriod?.periodLabel || '')

// 👉 Fetch Analytical Data (Top Producers & Branch Comparison)
const { data: topProducers } = await useApi<any>(createUrl('/apps/employees/top-producers', {
  query: { year: selectedYear },
}))

const { data: branchComparison } = await useApi<any>(createUrl('/apps/branches/comparison', {
  query: { year: selectedYear },
}))

const { data: kpiData } = await useApi<any>('/apps/policies/kpis')

const categoryLabels: Record<string, string> = {
  vehicle: 'تأمين السيارات',
  fire_theft: 'الحريق والسرقة',
  group_health: 'الصحي الجماعي',
  transport_marine: 'النقل / البحري',
  engineering: 'التأمين الهندسي',
  life: 'تأمين الحياة',
  personal_accident: 'الحوادث الشخصية',
  cash: 'تأمين النقد',
}

const categoryColors: Record<string, string> = {
  vehicle: '#7367f0',
  fire_theft: '#ea5455',
  group_health: '#00cfe8',
  transport_marine: '#82868b',
  engineering: '#ff9f43',
  life: '#28c76f',
  personal_accident: '#ff5722',
  cash: '#9c27b0',
}

const totalEmployees = computed(() => empData.value?.totalEmployees ?? 0)
const totalBranches = computed(() => branchData.value?.totalBranches ?? 0)
const totalPolicies = computed(() => statsData.value?.totalCount ?? 0)
const totalAmount = computed(() => statsData.value?.totalAmount ?? 0)

const formatCurrency = (v: number) =>
  new Intl.NumberFormat('ar-IQ', { style: 'decimal', maximumFractionDigits: 0 }).format(v) + ' د.ع'

const kpiCards = computed(() => [
  { title: 'إجمالي الموظفين', value: totalEmployees.value, icon: 'tabler-users', color: 'primary', to: 'apps-employees-list' },
  { title: 'عدد الفروع', value: totalBranches.value, icon: 'tabler-building-community', color: 'info', to: 'apps-branches-list' },
  { title: `وثائق ${selectedYear.value}`, value: totalPolicies.value, icon: 'tabler-file-certificate', color: 'success', to: 'apps-policies-list' },
  { title: `إجمالي الأقساط ${selectedYear.value}`, value: formatCurrency(totalAmount.value), icon: 'tabler-cash', color: 'warning', to: 'apps-policies-list' },
])

const categorySeries = computed(() => {
  const stats = statsData.value?.stats ?? {}
  return Object.keys(categoryLabels).map(cat => ({
    label: categoryLabels[cat],
    count: stats[cat]?.count ?? 0,
    amount: stats[cat]?.amount ?? 0,
    color: categoryColors[cat],
  }))
})

const maxAmount = computed(() => Math.max(...categorySeries.value.map(c => c.amount), 1))

const plans = computed(() => plansData.value?.plans ?? [])
</script>

<template>
  <section>
    <!-- Year Selector & Welcome -->
    <div class="d-flex align-center justify-space-between mb-6 flex-wrap gap-4">
      <div>
        <h3 class="text-h3 mb-1">أهلاً بك، {{ userData?.fullName }} 👋</h3>
        <p class="text-body-1 text-medium-emphasis">
          {{ isBranchManager ? 'نظرة عامة على أداء فرعك لهذا اليوم' : 'نظرة عامة على أداء الشركة بالكامل' }}
        </p>
      </div>
      <div style="inline-size:180px">
        <AppSelect
          v-model="selectedYear"
          :items="yearOptions"
          label="السنة"
          density="compact"
        />
      </div>
    </div>


    <!-- KPI Cards -->
    <VRow class="mb-6">
      <VCol
        v-for="(card, i) in kpiCards"
        :key="i"
        cols="12"
        sm="6"
        lg="3"
      >
        <VCard :to="{ name: card.to }">
          <VCardText>
            <div class="d-flex justify-space-between align-center">
              <div>
                <p class="text-body-1 text-medium-emphasis mb-1">{{ card.title }}</p>
                <h4 class="text-h4">{{ card.value }}</h4>
              </div>
              <VAvatar :color="card.color" variant="tonal" rounded size="52">
                <VIcon :icon="card.icon" size="28" />
              </VAvatar>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <VRow>
      <!-- Branch Comparison -->
      <VCol cols="12" :md="isBranchManager ? 12 : 8">
        <VCard height="100%">
          <VCardItem class="pb-4">
            <VCardTitle>{{ isBranchManager ? 'تفاصيل أداء فرعك — الهدف مقابل المتحقق' : 'أداء الفروع — مقارنة الهدف بالمتحقق' }} ({{ selectedYear }})</VCardTitle>
          </VCardItem>
          <VCardText>
            <div v-if="!branchComparison || branchComparison.length === 0" class="text-center py-8 text-medium-emphasis">
              لا توجد بيانات مقارنة متاحة
            </div>
            <div v-else v-for="branch in branchComparison" :key="branch.id" class="mb-5">
              <div v-if="!isBranchManager || Number(branch.id) === Number(branchId)" class="mb-6">
                <div class="d-flex justify-space-between align-center mb-1">
                  <div class="d-flex align-center gap-2">
                    <VIcon icon="tabler-building" size="18" class="text-primary" />
                    <span class="font-weight-medium">{{ branch.name }}</span>
                  </div>
                  <div class="text-caption">
                    <span class="text-medium-emphasis">الهدف: {{ formatCurrency(branch.totals.target) }}</span>
                    <span class="mx-2">|</span>
                    <span class="font-weight-bold text-success">المحقق: {{ formatCurrency(branch.totals.actual) }}</span>
                  </div>
                </div>
                <div class="d-flex align-center gap-3">
                  <VProgressLinear
                    :model-value="branch.totals.percentage"
                    height="12"
                    rounded
                    color="primary"
                    striped
                  />
                  <span class="text-h6" style="min-inline-size: 50px;">{{ branch.totals.percentage }}%</span>
                </div>
              </div>
            </div>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Top 10 Producers -->
      <VCol cols="12" md="4">
        <VCard height="100%">
          <VCardItem class="pb-4">
            <VCardTitle>أفضل 10 منتجين ({{ selectedYear }})</VCardTitle>
          </VCardItem>
          <VDivider />
          <VCardText class="pa-0">
             <VList lines="two">
              <VListItem
                v-for="(emp, index) in topProducers"
                :key="emp.id"
              >
                <template #prepend>
                  <VAvatar color="primary" variant="tonal" rounded size="40">
                    <span class="text-h6">{{ index + 1 }}</span>
                  </VAvatar>
                </template>
                <VListItemTitle class="font-weight-bold">{{ emp.fullName }}</VListItemTitle>
                <VListItemSubtitle>{{ emp.branch }}</VListItemSubtitle>
                <template #append>
                  <div class="text-end">
                    <div class="text-body-2 font-weight-bold text-primary">{{ formatCurrency(emp.totalAmount) }}</div>
                    <div class="text-caption">إجمالي الإنتاج</div>
                  </div>
                </template>
              </VListItem>
            </VList>
            <div v-if="!topProducers || topProducers.length === 0" class="text-center py-8 text-medium-emphasis">
              لا توجد بيانات إنتاج لهذا العام
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <VRow class="mt-6">
      <!-- Category Breakdown -->
      <VCol cols="12" md="8">
        <VCard height="100%">
          <VCardItem class="pb-4">
            <VCardTitle>توزيع الإنتاج حسب الفئة — {{ selectedYear }}</VCardTitle>
          </VCardItem>
          <VCardText>
            <div
              v-for="cat in categorySeries"
              :key="cat.label"
              class="mb-4"
            >
              <div class="d-flex justify-space-between align-center mb-1">
                <div class="d-flex align-center gap-2">
                  <span
                    class="rounded-circle d-inline-block"
                    :style="`background:${cat.color};width:10px;height:10px`"
                  />
                  <span class="text-body-2">{{ cat.label }}</span>
                </div>
                <div class="d-flex gap-4 text-caption text-medium-emphasis">
                  <span>{{ cat.count }} وثيقة</span>
                  <span class="font-weight-bold">{{ formatCurrency(cat.amount) }}</span>
                </div>
              </div>
              <VProgressLinear
                :model-value="maxAmount > 0 ? (cat.amount / maxAmount) * 100 : 0"
                :color="cat.color"
                rounded
                height="8"
                bg-color="rgba(0,0,0,0.05)"
              />
            </div>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Active Plans -->
      <VCol cols="12" md="4">
        <VCard height="100%">
          <VCardItem class="pb-4">
            <VCardTitle>الخطط الإنتاجية — {{ selectedYear }}</VCardTitle>
            <template #append>
              <VBtn size="small" variant="text" :to="{ name: 'apps-production-plans-list' }">
                عرض الكل
              </VBtn>
            </template>
          </VCardItem>

          <VDivider />

          <VCardText v-if="plans.length === 0" class="text-center text-medium-emphasis py-8">
            لا توجد خطط لهذا العام
          </VCardText>

          <VList v-else lines="two">
            <VListItem
              v-for="plan in plans"
              :key="plan.id"
              :to="{ name: 'apps-production-plans-list' }"
            >
              <template #prepend>
                <VAvatar :color="plan.isLocked ? 'error' : 'success'" variant="tonal" rounded>
                  <VIcon :icon="plan.isLocked ? 'tabler-lock' : 'tabler-clipboard-list'" size="20" />
                </VAvatar>
              </template>
              <VListItemTitle class="font-weight-medium">{{ plan.title }}</VListItemTitle>
              <VListItemSubtitle>{{ formatCurrency(plan.totalAmount) }}</VListItemSubtitle>
              <template #append>
                <VChip :color="plan.isLocked ? 'error' : 'success'" size="x-small">
                  {{ plan.isLocked ? 'مقفلة' : 'نشطة' }}
                </VChip>
              </template>
            </VListItem>
          </VList>
        </VCard>
      </VCol>
    </VRow>
  </section>
</template>
