<script setup lang="ts">
definePage({
  meta: { action: 'read', subject: 'Branch' },
})

const selectedYear = ref(new Date().getFullYear())
const { data: rankingData, loading } = await useApi<any>(createUrl('/apps/branches/comparison', {
  query: { year: selectedYear },
}))

const sortedRanking = computed(() => {
  return [...(rankingData.value ?? [])].sort((a, b) => b.totals.percentage - a.totals.percentage)
})

const formatCurrency = (v: number) =>
  new Intl.NumberFormat('ar-IQ', { style: 'decimal', maximumFractionDigits: 0 }).format(v) + ' د.ع'

const getRankColor = (index: number) => {
  if (index === 0) return 'warning' // Gold
  if (index === 1) return 'secondary' // Silver
  if (index === 2) return 'error' // Bronze
  return 'primary'
}
</script>

<template>
  <VRow>
    <VCol cols="12">
      <VCard class="mb-6">
        <VCardText>
          <div class="d-flex align-center justify-space-between flex-wrap gap-4">
            <div>
              <h4 class="text-h4 font-weight-bold mb-1">ترتيب فروع المؤسسة (Leaderboard)</h4>
              <p class="text-caption text-medium-emphasis mb-0">تصنيف الفروع حسب نسبة تحقيق الهدف السنوي</p>
            </div>
            <div style="inline-size: 150px">
              <AppSelect
                v-model="selectedYear"
                :items="[2024, 2025, 2026]"
                label="سنة التصنيف"
                density="compact"
              />
            </div>
          </div>
        </VCardText>
      </VCard>
    </VCol>

    <!-- Top 3 Podium -->
    <VCol cols="12" v-if="sortedRanking.length >= 3">
      <VRow class="justify-center align-end mb-6">
        <!-- 2nd Place -->
        <VCol cols="12" sm="4" class="text-center">
          <VCard variant="tonal" color="secondary" class="pa-4">
            <VAvatar size="64" color="secondary" variant="elevated" class="mb-2">
              <span class="text-h4">2</span>
            </VAvatar>
            <h5 class="text-h5 font-weight-bold">{{ sortedRanking[1].name }}</h5>
            <div class="text-h4 font-weight-black mt-2">{{ sortedRanking[1].totals.percentage }}%</div>
            <p class="text-caption">نسبة الإنجاز</p>
          </VCard>
        </VCol>

        <!-- 1st Place -->
        <VCol cols="12" sm="4" class="text-center">
          <VCard variant="elevated" color="warning" class="pa-6" elevation="8">
            <VIcon icon="tabler-crown" size="48" class="mb-2" />
            <VAvatar size="80" color="white" variant="elevated" class="mb-2">
               <span class="text-h3 text-warning">1</span>
            </VAvatar>
            <h4 class="text-h4 font-weight-bold">{{ sortedRanking[0].name }}</h4>
            <div class="text-h2 font-weight-black mt-2">{{ sortedRanking[0].totals.percentage }}%</div>
            <p class="text-body-1">بطل الإنتاج</p>
          </VCard>
        </VCol>

        <!-- 3rd Place -->
        <VCol cols="12" sm="4" class="text-center">
          <VCard variant="tonal" color="error" class="pa-4">
            <VAvatar size="64" color="error" variant="elevated" class="mb-2">
              <span class="text-h4">3</span>
            </VAvatar>
            <h5 class="text-h5 font-weight-bold">{{ sortedRanking[2].name }}</h5>
            <div class="text-h4 font-weight-black mt-2">{{ sortedRanking[2].totals.percentage }}%</div>
            <p class="text-caption">نسبة الإنجاز</p>
          </VCard>
        </VCol>
      </VRow>
    </VCol>

    <VCol cols="12">
      <VCard title="جدول التصنيف العام">
        <VDataTable
          :items="sortedRanking"
          :headers="[
            { title: 'الترتيب', key: 'rank' },
            { title: 'الفرع', key: 'name' },
            { title: 'المحافظة', key: 'governorate' },
            { title: 'المحقق', key: 'totals.actual' },
            { title: 'الهدف', key: 'totals.target' },
            { title: 'نسبة الإنجاز', key: 'totals.percentage' },
          ]"
          class="text-no-wrap"
          :loading="loading"
        >
          <template #item.rank="{ index }">
            <VChip :color="getRankColor(index)" size="small" class="font-weight-bold">
              #{{ index + 1 }}
            </VChip>
          </template>

          <template #item.totals.actual="{ item }">
            {{ formatCurrency(item.totals.actual) }}
          </template>
          
          <template #item.totals.target="{ item }">
            <span class="text-medium-emphasis">{{ formatCurrency(item.totals.target) }}</span>
          </template>

          <template #item.totals.percentage="{ item }">
            <div class="d-flex align-center gap-2">
              <VProgressLinear
                :model-value="item.totals.percentage"
                height="8"
                rounded
                color="primary"
                style="inline-size: 100px"
              />
              <span class="font-weight-bold">{{ item.totals.percentage }}%</span>
            </div>
          </template>
        </VDataTable>
      </VCard>
    </VCol>
  </VRow>
</template>
