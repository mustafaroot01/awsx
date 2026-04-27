<script setup lang="ts">
definePage({
  meta: { action: 'read', subject: 'Statistics' },
})

const selectedYear = ref(new Date().getFullYear())
const selectedBranch = ref<number | null>(null)
const branchOptions = ref<{ title: string; value: number }[]>([])

const yearOptions = Array.from({ length: 5 }, (_, i) => {
  const y = new Date().getFullYear() - i
  return { title: String(y), value: y }
})

onMounted(async () => {
  const r = await $api<any>('/apps/branches', { query: { itemsPerPage: -1 } })
  branchOptions.value = (r?.branches ?? []).map((b: any) => ({ title: b.name, value: b.id }))
})

// 👉 Fetch stats data
const { data: stats, loading, execute: fetchStats } = await useApi<any>(createUrl('/apps/policies/stats', {
  query: { 
    year: selectedYear,
    branchId: selectedBranch
  },
}))

watch([selectedYear, selectedBranch], fetchStats)

const formatCurrency = (v: number) =>
  new Intl.NumberFormat('ar-IQ', { style: 'decimal', maximumFractionDigits: 0 }).format(v) + ' د.ع'

const categoryNames: any = {
  vehicle: 'تأمين السيارات',
  fire_theft: 'الحريق والسرقة',
  group_health: 'الصحي الجماعي',
  transport_marine: 'النقل / البحري',
  engineering: 'التأمين الهندسي',
  life: 'تأمين الحياة',
  personal_accident: 'الحوادث الشخصية',
  cash: 'تأمين النقد',
}

const getCategoryName = (key: string) => categoryNames[key] || key

// 👉 Charts Config
const monthlyChartConfig = computed(() => ({
  series: [{
    name: 'الإنتاج الشهري',
    data: stats.value?.monthlyTrend?.map((i: any) => i.amount) ?? []
  }],
  chartOptions: {
    chart: { type: 'line', toolbar: { show: false } },
    stroke: { curve: 'smooth', width: 4 },
    colors: ['#7367f0'],
    xaxis: {
      categories: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر']
    },
    tooltip: { y: { formatter: (v: number) => formatCurrency(v) } }
  }
}))

// 👉 Plan Breakthroughs
const { data: breakthroughData, execute: fetchBreakthroughs } = await useApi<any>(
  createUrl('/apps/production-plans/breakthroughs', { query: { year: selectedYear } })
)
watch(selectedYear, fetchBreakthroughs)

const breakthroughs = computed(() => breakthroughData.value?.breakthroughs ?? [])

const categoryLabels: Record<string, string> = {
  life:             'تأمين الحياة',
  group_health:     'الصحي الجماعي',
  general_property: 'الممتلكات العامة',
}

const breakthroughsByBranch = computed(() => {
  const grouped: Record<number, { branchId: number; branchName: string; items: any[] }> = {}
  for (const bt of breakthroughs.value) {
    if (!grouped[bt.branchId]) {
      grouped[bt.branchId] = { branchId: bt.branchId, branchName: bt.branchName, items: [] }
    }
    grouped[bt.branchId].items.push(bt)
  }
  return Object.values(grouped)
})

const showAllBranches = ref(false)
const visibleBranches = computed(() =>
  showAllBranches.value ? breakthroughsByBranch.value : breakthroughsByBranch.value.slice(0, 4)
)

const categoryChartConfig = computed(() => {
  const data = stats.value?.stats ?? {}
  const labels = Object.keys(data).map(k => getCategoryName(k))
  const series = Object.values(data).map((v: any) => v.amount)

  return {
    series,
    chartOptions: {
      labels,
      chart: { type: 'donut' },
      legend: { position: 'bottom' },
      plotOptions: { pie: { donut: { size: '70%' } } }
    }
  }
})
</script>

<template>
  <VRow>

    <!-- 🏆 عبور الخطة -->
    <VCol cols="12" v-if="breakthroughs.length > 0">
      <VCard>
        <VCardItem>
          <template #prepend>
            <VAvatar color="success" variant="tonal" size="42">
              <VIcon icon="tabler-trophy" size="24" />
            </VAvatar>
          </template>
          <VCardTitle>عبور الخطة الإنتاجية — {{ selectedYear }}</VCardTitle>
          <VCardSubtitle>الفروع التي تجاوزت الهدف المخطط لها</VCardSubtitle>
          <template #append>
            <VChip color="success" variant="tonal" class="me-2">{{ breakthroughsByBranch.length }} فرع</VChip>
            <VChip color="primary" variant="tonal">{{ breakthroughs.length }} فئة</VChip>
          </template>
        </VCardItem>

        <VDivider />

        <VCardText>
          <VRow>
            <VCol
              v-for="branch in visibleBranches"
              :key="branch.branchId"
              cols="12" md="6" lg="3"
            >
              <VCard variant="tonal" color="success">
                <VCardItem class="pb-2">
                  <template #prepend>
                    <VAvatar color="success" variant="elevated" size="36">
                      <VIcon icon="tabler-building" size="20" />
                    </VAvatar>
                  </template>
                  <VCardTitle class="text-body-1">{{ branch.branchName }}</VCardTitle>
                  <VCardSubtitle>{{ branch.items.length }} فئة متجاوزة</VCardSubtitle>
                </VCardItem>

                <VCardText class="pt-0">
                  <div
                    v-for="item in branch.items"
                    :key="item.category"
                    class="mb-3"
                  >
                    <div class="d-flex align-center justify-space-between mb-1">
                      <span class="text-caption font-weight-medium">{{ categoryLabels[item.category] ?? item.category }}</span>
                      <VChip
                        :color="item.percentage >= 150 ? 'error' : item.percentage >= 120 ? 'warning' : 'success'"
                        size="x-small"
                        variant="elevated"
                      >
                        {{ item.percentage }}%
                      </VChip>
                    </div>
                    <VProgressLinear
                      :model-value="Math.min(item.percentage, 200)"
                      :color="item.percentage >= 150 ? 'error' : item.percentage >= 120 ? 'warning' : 'success'"
                      height="6"
                      rounded
                      class="mb-1"
                    />
                    <div class="d-flex justify-space-between">
                      <span class="text-caption text-medium-emphasis">الخطة: {{ formatCurrency(item.target) }}</span>
                      <span class="text-caption font-weight-bold">{{ formatCurrency(item.achieved) }}</span>
                    </div>
                    <div class="text-caption text-medium-emphasis">
                      فائض: +{{ formatCurrency(item.surplus) }}
                    </div>
                    <VDivider v-if="branch.items.indexOf(item) < branch.items.length - 1" class="mt-2" />
                  </div>
                </VCardText>
              </VCard>
            </VCol>
          </VRow>

          <div v-if="breakthroughsByBranch.length > 4" class="text-center mt-4">
            <VBtn
              variant="tonal"
              color="success"
              size="small"
              :append-icon="showAllBranches ? 'tabler-chevron-up' : 'tabler-chevron-down'"
              @click="showAllBranches = !showAllBranches"
            >
              {{ showAllBranches ? 'عرض أقل' : `عرض باقي الفروع (${breakthroughsByBranch.length - 4})` }}
            </VBtn>
          </div>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12">
      <VCard class="mb-6">
        <VCardText>
          <div class="d-flex align-center justify-space-between flex-wrap gap-4">
            <div>
              <h4 class="text-h4 font-weight-bold mb-1">إحصائيات الإنتاج التفصيلية</h4>
              <p class="text-caption text-medium-emphasis mb-0">تحليل معمق للأقساط، الوثائق، والاتجاهات الزمنية</p>
            </div>
            
            <div class="d-flex gap-3 align-center">
              <div style="inline-size: 150px">
                <AppSelect v-model="selectedYear" :items="yearOptions" label="السنة" density="compact" />
              </div>
              <div style="inline-size: 200px">
                <AppSelect v-model="selectedBranch" :items="branchOptions" label="الفرع" density="compact" clearable />
              </div>
            </div>
          </div>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12" md="3">
      <VCard class="h-100">
        <VCardText class="d-flex align-center gap-4">
          <VAvatar color="primary" variant="tonal" rounded size="52">
            <VIcon icon="tabler-currency-dollar" size="28" />
          </VAvatar>
          <div>
            <p class="text-caption mb-1">إجمالي الأقساط</p>
            <h5 class="text-h5 font-weight-black">{{ formatCurrency(stats?.totalAmount ?? 0) }}</h5>
          </div>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12" md="3">
      <VCard class="h-100">
        <VCardText class="d-flex align-center gap-4">
          <VAvatar color="success" variant="tonal" rounded size="52">
            <VIcon icon="tabler-file-analytics" size="28" />
          </VAvatar>
          <div>
            <p class="text-caption mb-1">عدد الوثائق</p>
            <h5 class="text-h5 font-weight-black">{{ stats?.totalCount ?? 0 }} وثيقة</h5>
          </div>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12" md="3">
      <VCard class="h-100">
        <VCardText class="d-flex align-center gap-4">
          <VAvatar color="info" variant="tonal" rounded size="52">
            <VIcon icon="tabler-chart-line" size="28" />
          </VAvatar>
          <div>
            <p class="text-caption mb-1">المتوسط</p>
            <h5 class="text-h5 font-weight-black">
              {{ formatCurrency(stats?.totalCount > 0 ? (stats.totalAmount / stats.totalCount) : 0) }}
            </h5>
          </div>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12" md="3">
      <VCard class="h-100">
        <VCardText class="d-flex align-center gap-4">
          <VAvatar 
            :color="(stats?.totalAmount >= stats?.previousYearAmount) ? 'success' : 'error'" 
            variant="tonal" 
            rounded size="52"
          >
            <VIcon :icon="(stats?.totalAmount >= stats?.previousYearAmount) ? 'tabler-trending-up' : 'tabler-trending-down'" size="28" />
          </VAvatar>
          <div>
            <p class="text-caption mb-1">النمو السنوي</p>
            <h5 class="text-h5 font-weight-black" :class="(stats?.totalAmount >= stats?.previousYearAmount) ? 'text-success' : 'text-error'">
              {{ stats?.previousYearAmount > 0 ? Math.round(((stats.totalAmount - stats.previousYearAmount) / stats.previousYearAmount) * 100) : 0 }}%
            </h5>
          </div>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12" md="8">
      <VCard title="اتجاه الإنتاج الشهري">
        <VCardText>
          <VueApexCharts
            :options="monthlyChartConfig.chartOptions"
            :series="monthlyChartConfig.series"
            height="350"
          />
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12" md="4">
      <VCard title="توزيع الإنتاج حسب الفئة">
        <VCardText>
          <VueApexCharts
            :options="categoryChartConfig.chartOptions"
            :series="categoryChartConfig.series"
            height="350"
          />
          
          <VList class="mt-4">
            <VListItem v-for="(val, key) in stats?.stats" :key="key" density="compact" class="px-0">
              <template #prepend>
                <div class="rounded-circle me-2" style="width:8px; height:8px; background: #7367f0" />
              </template>
              <VListItemTitle class="text-caption">{{ getCategoryName(key) }}</VListItemTitle>
              <template #append>
                <span class="text-caption font-weight-bold">{{ formatCurrency(val.amount) }}</span>
              </template>
            </VListItem>
          </VList>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>

