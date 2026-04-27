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

// 👉 Fetch Top Producers for details
const { data: topProducers, loading } = await useApi<any>(createUrl('/apps/employees/top-producers', {
  query: { 
    year: selectedYear,
    branchId: selectedBranch,
    limit: 50
  },
}))

const formatCurrency = (v: number) =>
  new Intl.NumberFormat('ar-IQ', { style: 'decimal', maximumFractionDigits: 0 }).format(v) + ' د.ع'

const chartConfig = computed(() => ({
  series: [{
    name: 'الإنتاج',
    data: topProducers.value?.slice(0, 10).map((i: any) => i.totalAmount) ?? []
  }],
  chartOptions: {
    chart: { type: 'bar', toolbar: { show: false } },
    plotOptions: { bar: { borderRadius: 4, horizontal: true } },
    colors: ['#28c76f'],
    xaxis: {
      categories: topProducers.value?.slice(0, 10).map((i: any) => i.fullName) ?? []
    },
    tooltip: { y: { formatter: (v: number) => formatCurrency(v) } }
  }
}))
</script>

<template>
  <VRow>
    <VCol cols="12">
      <VCard class="mb-6">
        <VCardText>
          <div class="d-flex align-center justify-space-between flex-wrap gap-4">
            <div>
              <h4 class="text-h4 font-weight-bold mb-1">تقارير الموظفين والإنتاجية</h4>
              <p class="text-caption text-medium-emphasis mb-0">تحليل أداء المنتجين وتصنيفهم حسب الإنتاج الفعلي</p>
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

    <VCol cols="12" md="8">
      <VCard title="أعلى 10 موظفين إنتاجاً">
        <VCardText>
          <VueApexCharts
            :options="chartConfig.chartOptions"
            :series="chartConfig.series"
            height="400"
          />
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12" md="4">
      <VCard class="h-100" title="ملخص القوى العاملة">
        <VCardText>
          <div class="d-flex flex-column gap-6">
            <div class="d-flex align-center gap-4">
              <VAvatar color="primary" variant="tonal" rounded size="48">
                <VIcon icon="tabler-users" size="24" />
              </VAvatar>
              <div>
                <h5 class="text-h5 font-weight-bold">{{ topProducers?.length ?? 0 }}</h5>
                <span class="text-caption">إجمالي الموظفين النشطين</span>
              </div>
            </div>
            
            <div class="d-flex align-center gap-4">
              <VAvatar color="success" variant="tonal" rounded size="48">
                <VIcon icon="tabler-award" size="24" />
              </VAvatar>
              <div>
                <h5 class="text-h5 font-weight-bold">{{ formatCurrency(topProducers?.[0]?.totalAmount ?? 0) }}</h5>
                <span class="text-caption">أعلى إنتاج فردي</span>
              </div>
            </div>
          </div>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12">
      <VCard title="جدول الإنتاجية التفصيلي">
        <VDataTable
          :items="topProducers ?? []"
          :headers="[
            { title: 'الموظف', key: 'fullName' },
            { title: 'الفرع', key: 'branch' },
            { title: 'إجمالي الإنتاج', key: 'totalAmount' },
            { title: 'عدد الوثائق', key: 'totalCount' },
            { title: 'المتوسط', key: 'avg' },
          ]"
          class="text-no-wrap"
          :loading="loading"
        >
          <template #item.totalAmount="{ item }">
            <span class="font-weight-bold text-primary">{{ formatCurrency(item.totalAmount) }}</span>
          </template>
          
          <template #item.avg="{ item }">
             <span class="text-caption">{{ formatCurrency(item.totalCount > 0 ? (item.totalAmount / item.totalCount) : 0) }}</span>
          </template>
        </VDataTable>
      </VCard>
    </VCol>
  </VRow>
</template>
