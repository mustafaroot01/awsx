<script setup lang="ts">
definePage({
  meta: { action: 'read', subject: 'Statistics' },
})

const selectedYear = ref(new Date().getFullYear())
const { data: roiData, loading, execute: fetchRoi } = await useApi<any>(createUrl('/apps/branches/roi-analysis', {
  query: { year: selectedYear },
}))

watch(selectedYear, fetchRoi)

const formatCurrency = (v: number) =>
  new Intl.NumberFormat('ar-IQ', { style: 'decimal', maximumFractionDigits: 0 }).format(v) + ' د.ع'

// Chart Data
const chartConfig = computed(() => {
  const categories = roiData.value?.map((i: any) => i.name) ?? []
  const production = roiData.value?.map((i: any) => i.production) ?? []
  const incentives = roiData.value?.map((i: any) => i.incentivePoints) ?? [] // We use points as a proxy for cost here

  return {
    series: [
      { name: 'الإنتاج الكلي', data: production },
      { name: 'إجمالي الحوافز (نقاط)', data: incentives },
    ],
    chartOptions: {
      chart: { type: 'bar', height: 400, toolbar: { show: false } },
      plotOptions: { bar: { horizontal: false, columnWidth: '55%', borderRadius: 4 } },
      dataLabels: { enabled: false },
      stroke: { show: true, width: 2, colors: ['transparent'] },
      xaxis: { categories },
      yaxis: { title: { text: 'القيمة' } },
      fill: { opacity: 1 },
      colors: ['#7367f0', '#ff9f43'],
      tooltip: { y: { formatter: (val: number) => formatCurrency(val) } }
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
              <h4 class="text-h4 font-weight-bold mb-1">تحليل التكلفة مقابل الإنجاز (ROI)</h4>
              <p class="text-caption text-medium-emphasis mb-0">مقارنة حجم الإنتاج الفعلي مع تكلفة الحوافز الممنوحة للفروع</p>
            </div>
            
            <div style="inline-size: 150px">
              <AppSelect v-model="selectedYear" :items="[2024, 2025, 2026]" label="السنة" density="compact" />
            </div>
          </div>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12" md="8">
      <VCard title="مقارنة الإنتاج والحوافز لكل فرع">
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
      <VCard class="h-100" title="ملخص الكفاءة المالية">
        <VCardText>
          <p class="text-caption mb-6">توضح هذه النسب المئوية مقدار الحوافز الممنوحة مقارنة بحجم الإنتاج الكلي لكل فرع.</p>
          
          <div v-for="branch in roiData" :key="branch.id" class="mb-6">
            <div class="d-flex justify-space-between mb-1">
              <span class="text-body-2 font-weight-medium">{{ branch.name }}</span>
              <span class="text-body-2 font-weight-bold">{{ branch.ratio }}%</span>
            </div>
            <VProgressLinear
              :model-value="branch.ratio"
              height="8"
              rounded
              :color="branch.ratio > 10 ? 'error' : 'success'"
            />
          </div>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12">
      <VCard title="الجدول التحليلي المفصل">
        <VDataTable
          :items="roiData ?? []"
          :headers="[
            { title: 'الفرع', key: 'name' },
            { title: 'حجم الإنتاج الكلي', key: 'production' },
            { title: 'إجمالي الحوافز (نقاط)', key: 'incentivePoints' },
            { title: 'نسبة التكلفة للإنتاج', key: 'ratio' },
          ]"
          class="text-no-wrap"
          :loading="loading"
        >
          <template #item.production="{ item }">
            {{ formatCurrency(item.production) }}
          </template>

          <template #item.ratio="{ item }">
            <VChip :color="item.ratio > 10 ? 'error' : 'success'" size="small" label>
              {{ item.ratio }}%
            </VChip>
          </template>
        </VDataTable>
      </VCard>
    </VCol>
  </VRow>
</template>
