<script setup lang="ts">
definePage({
  meta: { action: 'read', subject: 'Policy' },
})

const days = ref(30)
const { data: policies, loading, execute: fetchPolicies } = await useApi<any>(createUrl('/apps/policies/expiring-soon', {
  query: { days },
}))

watch(days, fetchPolicies)

const headers = [
  { title: 'رقم الوثيقة', key: 'policyNo' },
  { title: 'الزبون', key: 'clientName' },
  { title: 'تاريخ الانتهاء', key: 'expiryDate' },
  { title: 'الفرع', key: 'branchName' },
  { title: 'الموظف المنتج', key: 'employeeName' },
  { title: 'الأيام المتبقية', key: 'daysLeft' },
]

const formatCurrency = (v: number) =>
  new Intl.NumberFormat('ar-IQ', { style: 'decimal', maximumFractionDigits: 0 }).format(v) + ' د.ع'

const getDaysLeft = (date: string) => {
  const diff = new Date(date).getTime() - new Date().getTime()
  return Math.ceil(diff / (1000 * 60 * 60 * 24))
}
</script>

<template>
  <VRow>
    <VCol cols="12">
      <VCard class="mb-6">
        <VCardText>
          <div class="d-flex align-center justify-space-between flex-wrap gap-4">
            <div>
              <h4 class="text-h4 font-weight-bold mb-1">الوثائق المنتهية قريباً</h4>
              <p class="text-caption text-medium-emphasis mb-0">متابعة بوالص التأمين التي تقترب من تاريخ الانتهاء لضمان التجديد</p>
            </div>
            
            <div style="inline-size: 200px">
              <AppSelect
                v-model="days"
                label="عرض الوثائق التي ستنتهي خلال"
                :items="[
                  { title: '15 يوم القادمة', value: 15 },
                  { title: '30 يوم القادمة', value: 30 },
                  { title: '60 يوم القادمة', value: 60 },
                  { title: '90 يوم القادمة', value: 90 },
                ]"
                density="compact"
              />
            </div>
          </div>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12">
      <VCard>
        <VDataTable
          :items="policies ?? []"
          :headers="headers"
          class="text-no-wrap"
          :loading="loading"
        >
          <template #item.expiryDate="{ item }">
            <span class="font-weight-bold text-error">{{ item.expiryDate }}</span>
          </template>

          <template #item.daysLeft="{ item }">
            <VChip 
              :color="getDaysLeft(item.expiryDate) <= 7 ? 'error' : 'warning'" 
              size="small" 
              class="font-weight-bold"
            >
              {{ getDaysLeft(item.expiryDate) }} يوم
            </VChip>
          </template>

          <template #item.branchName="{ item }">
            {{ item.branch?.name }}
          </template>

          <template #item.employeeName="{ item }">
            {{ item.employee?.fullName }}
          </template>
        </VDataTable>
      </VCard>
    </VCol>
  </VRow>
</template>
