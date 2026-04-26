<script setup lang="ts">
definePage({
  meta: { action: 'read', subject: 'Employee' },
})

const selectedYear = ref(new Date().getFullYear())
const selectedMonth = ref<number | null>(null)
const selectedBranch = ref<number | null>(null)
const branchOptions = ref<{ title: string; value: number }[]>([])

const months = [
  { title: 'يناير', value: 1 }, { title: 'فبراير', value: 2 }, { title: 'مارس', value: 3 },
  { title: 'أبريل', value: 4 }, { title: 'مايو', value: 5 }, { title: 'يونيو', value: 6 },
  { title: 'يوليو', value: 7 }, { title: 'أغسطس', value: 8 }, { title: 'سبتمبر', value: 9 },
  { title: 'أكتوبر', value: 10 }, { title: 'نوفمبر', value: 11 }, { title: 'ديسمبر', value: 12 },
]

onMounted(async () => {
  const r = await $api<any>('/apps/branches', { query: { itemsPerPage: -1 } })
  branchOptions.value = (r?.branches ?? []).map((b: any) => ({ title: b.name, value: b.id }))
})

const { data: history, loading, execute: fetchHistory } = await useApi<any>(createUrl('/apps/employees/incentives', {
  query: { 
    year: selectedYear,
    month: selectedMonth,
    branchId: selectedBranch,
  },
}))

watch([selectedYear, selectedMonth, selectedBranch], fetchHistory)

const headers = [
  { title: 'الموظف', key: 'employeeName' },
  { title: 'الفرع', key: 'branch' },
  { title: 'الشهر', key: 'month' },
  { title: 'مجموع النقاط', key: 'totalPoints' },
  { title: 'أيام العمل الصافية', key: 'netWorkingDays' },
  { title: 'تاريخ الحفظ', key: 'createdAt' },
]

const resolveMonth = (m: number) => months.find(item => item.value === m)?.title || m
</script>

<template>
  <VRow>
    <VCol cols="12">
      <VCard class="mb-6">
        <VCardText>
          <div class="d-flex align-center justify-space-between flex-wrap gap-4">
            <div>
              <h4 class="text-h4 font-weight-bold mb-1">سجل الحوافز الرسمي</h4>
              <p class="text-caption text-medium-emphasis mb-0">عرض ومراجعة كافة الحوافز التي تم اعتمادها وحفظها للموظفين</p>
            </div>
            
            <div class="d-flex gap-3 align-center">
              <div style="inline-size: 150px">
                <AppSelect v-model="selectedYear" :items="[2024, 2025, 2026]" label="السنة" density="compact" />
              </div>
              <div style="inline-size: 150px">
                <AppSelect v-model="selectedMonth" :items="months" label="الشهر" density="compact" clearable />
              </div>
              <div style="inline-size: 200px">
                <AppSelect v-model="selectedBranch" :items="branchOptions" label="الفرع" density="compact" clearable />
              </div>
            </div>
          </div>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12">
      <VCard>
        <VDataTable
          :items="history ?? []"
          :headers="headers"
          class="text-no-wrap"
          :loading="loading"
        >
          <template #item.month="{ item }">
            {{ resolveMonth(item.month) }} - {{ item.year }}
          </template>

          <template #item.totalPoints="{ item }">
            <VChip :color="item.totalPoints > 0 ? 'success' : 'error'" size="small" class="font-weight-bold">
              {{ item.totalPoints }} نقطة
            </VChip>
          </template>

          <template #item.netWorkingDays="{ item }">
            <span class="text-primary font-weight-medium">{{ item.netWorkingDays }} يوم</span>
          </template>

          <template #item.createdAt="{ item }">
            <span class="text-caption">{{ new Date(item.createdAt).toLocaleDateString('ar-IQ') }}</span>
          </template>
        </VDataTable>
      </VCard>
    </VCol>
  </VRow>
</template>
