<script setup lang="ts">
definePage({
  meta: { action: 'read', subject: 'Auth' },
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
