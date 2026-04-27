<script setup lang="ts">
definePage({
  meta: { action: 'read', subject: 'Statistics' },
})

const selectedYear = ref(new Date().getFullYear())
const selectedBranches = ref<number[]>([])
const branchOptions = ref<{ title: string; value: number }[]>([])
const isLoadingBranches = ref(false)
const isLoadingComparison = ref(false)

// 👉 Fetch all branches
const fetchBranches = async () => {
  isLoadingBranches.value = true
  try {
    const res = await $api<any>('/apps/branches', { query: { itemsPerPage: -1 } })
    branchOptions.value = (res?.branches ?? []).map((b: any) => ({ title: b.name, value: b.id }))
    
    // Select first two branches by default if available and none selected
    if (branchOptions.value.length >= 2 && selectedBranches.value.length === 0) {
      selectedBranches.value = [branchOptions.value[0].value, branchOptions.value[1].value]
    }
  } catch (e) {
    console.error('Failed to load branches', e)
  } finally {
    isLoadingBranches.value = false
  }
}

const yearOptions = Array.from({ length: 5 }, (_, i) => {
  const y = new Date().getFullYear() - i
  return { title: String(y), value: y }
})

// 👉 Comparison Data State
const comparisonData = ref<any[]>([])

const fetchComparison = async () => {
  if (selectedBranches.value.length === 0) {
    comparisonData.value = []
    return
  }
  
  isLoadingComparison.value = true
  try {
    const res = await $api<any>('/apps/branches/comparison', {
      query: { 
        year: selectedYear.value,
        branchIds: selectedBranches.value.join(',')
      },
    })
    comparisonData.value = res || []
  } catch (e) {
    console.error('Failed to load comparison', e)
  } finally {
    isLoadingComparison.value = false
  }
}

onMounted(() => {
  fetchBranches()
})

watch([selectedYear, selectedBranches], () => {
  fetchComparison()
}, { deep: true })

const formatCurrency = (v: number) =>
  new Intl.NumberFormat('ar-IQ', { style: 'decimal', maximumFractionDigits: 0 }).format(v) + ' د.ع'

const getCategoryColor = (cat: string) => {
  const colors: any = { 
    vehicle: 'primary', 
    fire_theft: 'error', 
    group_health: 'info', 
    transport_marine: 'warning', 
    engineering: 'secondary', 
    life: 'success', 
    personal_accident: 'error', 
    cash: 'info' 
  }
  return colors[cat] || 'secondary'
}

const leaderId = computed(() => {
  if (!comparisonData.value || comparisonData.value.length < 2) return null
  
  const sorted = [...comparisonData.value].sort((a, b) => {
    // Primary sort: Percentage
    if (b.totals.percentage !== a.totals.percentage) {
      return b.totals.percentage - a.totals.percentage
    }
    // Tie-breaker: Actual amount
    return b.totals.actual - a.totals.actual
  })
  
  // Only show leader if the top one actually has some achievement (either % > 0 or actual > 0)
  if (sorted[0].totals.percentage > 0 || sorted[0].totals.actual > 0) {
    return sorted[0].id
  }
  
  return null
})

const allBranchesSelected = computed(() => {
  return selectedBranches.value.length === branchOptions.value.length && branchOptions.value.length > 0
})

const toggleAllBranches = () => {
  if (allBranchesSelected.value) {
    selectedBranches.value = []
  } else {
    selectedBranches.value = branchOptions.value.map(b => b.value)
  }
}
</script>

<template>
  <VRow>
    <VCol cols="12">
      <VCard title="مقارنة أداء الفروع">
        <VCardText class="pb-2">
          <VRow>
            <!-- Year Selector -->
            <VCol cols="12" md="3">
              <AppSelect
                v-model="selectedYear"
                :items="yearOptions"
                label="سنة المقارنة"
                density="compact"
              />
            </VCol>

            <!-- Branch Selector -->
            <VCol cols="12" md="9">
                <AppSelect
                  v-model="selectedBranches"
                  :items="branchOptions"
                  label="اختر الفروع"
                  placeholder="اختر فرعين أو أكثر"
                  :loading="isLoadingBranches"
                  multiple
                  chips
                  closable-chips
                  clearable
                  density="compact"
                >
                  <template #prepend-item>
                    <VListItem
                      title="كل الفروع"
                      @click="toggleAllBranches"
                    >
                      <template #prepend>
                        <VCheckboxBtn :model-value="allBranchesSelected" />
                      </template>
                    </VListItem>
                    <VDivider class="mt-2" />
                  </template>
                </AppSelect>
            </VCol>
          </VRow>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12" v-if="isLoadingComparison" class="text-center py-8">
      <VProgressCircular indeterminate color="primary" />
      <p class="mt-4 text-caption">جاري تحليل البيانات...</p>
    </VCol>

    <VCol cols="12" v-else-if="selectedBranches.length > 0">
      <VRow v-if="comparisonData && comparisonData.length > 0">
        <!-- Comparison Cards -->
        <VCol 
          v-for="branch in comparisonData" 
          :key="branch.id"
          cols="12" 
          :md="comparisonData.length > 1 ? 6 : 12"
          lg="4"
        >
          <VCard 
            class="rounded border shadow-sm position-relative overflow-hidden" 
            :class="branch.id === leaderId ? 'border-primary border-2' : ''"
            height="100%"
          >
            <!-- Leader Badge -->
            <div 
              v-if="branch.id === leaderId" 
              class="position-absolute bg-primary text-white text-tiny px-3 py-1 font-weight-bold" 
              style="top: 0; left: 0; border-bottom-right-radius: 8px; z-index: 2"
            >
              <VIcon icon="tabler-award" size="14" class="me-1" />
              الفرع المتصدر
            </div>

            <div class="pa-3 d-flex align-center gap-2 mt-2">
              <VAvatar color="primary" variant="tonal" rounded size="32">
                <VIcon icon="tabler-building" size="20" />
              </VAvatar>
              <div class="flex-grow-1">
                <h6 class="text-h6 font-weight-bold mb-0 d-flex align-center gap-2">
                  {{ branch.name }}
                  <VChip v-if="branch.totals.percentage >= 100" color="success" size="x-small" label>تم تحقيق الخطة</VChip>
                </h6>
                <span class="text-tiny text-medium-emphasis">{{ branch.governorate }}</span>
              </div>
            </div>

            <VDivider />

            <VCardText class="pa-3">
              <!-- Total Achievement Section -->
              <div class="bg-light-primary rounded pa-3 mb-4 border-s-4 border-primary">
                <div class="d-flex justify-space-between align-center mb-1">
                  <span class="text-caption font-weight-bold text-primary">إجمالي الإنجاز</span>
                  <span class="text-h6 font-weight-black text-primary">{{ branch.totals.percentage }}%</span>
                </div>
                <VProgressLinear
                  :model-value="branch.totals.percentage"
                  height="6"
                  rounded
                  color="primary"
                  bg-opacity="0.1"
                />
                <div class="d-flex justify-space-between mt-2 text-tiny">
                  <span>المحقق: {{ formatCurrency(branch.totals.actual) }}</span>
                  <span class="text-medium-emphasis">الهدف: {{ formatCurrency(branch.totals.target) }}</span>
                </div>
              </div>

              <!-- Categories Breakdown -->
              <div class="d-flex flex-column gap-2">
                <div 
                  v-for="(stats, cat) in branch.categories" 
                  :key="cat" 
                  class="pa-2 rounded border bg-surface d-flex flex-column gap-1"
                >
                  <div class="d-flex justify-space-between align-center">
                    <div class="d-flex align-center gap-1">
                      <div class="rounded-circle" :style="`width:6px; height:6px; background-color: var(--v-theme-${getCategoryColor(cat)})`" />
                      <span class="text-tiny font-weight-medium">{{ stats.name }}</span>
                    </div>
                    <span class="text-tiny font-weight-bold" :class="`text-${getCategoryColor(cat)}`">{{ stats.pct }}%</span>
                  </div>
                  <VProgressLinear
                    :model-value="stats.pct"
                    height="4"
                    rounded
                    :color="getCategoryColor(cat)"
                    bg-opacity="0.05"
                  />
                </div>
              </div>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>
    </VCol>
  </VRow>
</template>

<style lang="scss" scoped>
.text-tiny {
  font-size: 0.75rem;
  line-height: 1rem;
}
</style>
