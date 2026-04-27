<script setup lang="ts">
interface Props {
  formData: any
}

const props = defineProps<Props>()

const activityOptions = [
  { title: 'صناعي', value: 'industrial' },
  { title: 'تجاري', value: 'commercial' },
  { title: 'زراعي', value: 'agricultural' },
  { title: 'خدمي', value: 'service' },
  { title: 'خيري', value: 'charity' },
  { title: 'لا يوجد', value: 'none' },
]

const incomeOptions = [
  { title: 'أقل من مليون دينار', value: 'under_1m' },
  { title: '1-5 مليون دينار', value: '1m_5m' },
  { title: '5-10 مليون دينار', value: '5m_10m' },
  { title: '10-25 مليون دينار', value: '10m_25m' },
  { title: 'أكثر من 25 مليون دينار', value: 'above_25m' },
]
</script>

<template>
  <VRow>
    <VCol cols="12">
      <div class="d-flex align-center gap-2 mb-4">
        <VIcon icon="tabler-shield-check" color="success" />
        <h6 class="text-h6 mb-0">مكافحة غسل الأموال ومعلومات الشركة (AML/KYB)</h6>
      </div>
    </VCol>

    <!-- 1. معلومات الشركة / الكيان -->
    <VCol cols="12">
      <VCard variant="tonal" class="pa-4 mb-4">
        <div class="text-subtitle-2 mb-3">بيانات الشركة (إن وجد)</div>
        <VRow>
          <VCol cols="12" md="6">
            <AppTextField v-model="props.formData.companyDetails.authorized_name" label="المفوض بإدارة الشركة" />
          </VCol>
          <VCol cols="12" md="6">
            <AppSelect v-model="props.formData.companyDetails.activity_type" :items="activityOptions" label="نوع نشاط الشركة" />
          </VCol>
          <VCol cols="12" md="4">
            <AppDateTimePicker v-model="props.formData.companyDetails.founding_date" label="تاريخ التأسيس" :config="{ dateFormat: 'Y-m-d' }" />
          </VCol>
          <VCol cols="12" md="4">
            <AppTextField v-model="props.formData.companyDetails.capital" label="رأس المال المدفوع" type="number" />
          </VCol>
          <VCol cols="12" md="4">
            <AppTextField v-model="props.formData.companyDetails.founding_place" label="مكان التأسيس" />
          </VCol>
        </VRow>
      </VCard>
    </VCol>

    <!-- 2. مصدر الأموال -->
    <VCol cols="12" md="6">
      <VCard variant="outlined" class="pa-4 h-100">
        <div class="text-subtitle-2 mb-2">مصدر الأموال *:</div>
        <div class="d-flex flex-wrap gap-x-6">
          <VCheckbox v-model="props.formData.source_of_funds" label="الراتب" value="salary" />
          <VCheckbox v-model="props.formData.source_of_funds" label="عوائد تجارية" value="profits" />
          <VCheckbox v-model="props.formData.source_of_funds" label="استثمارات" value="investments" />
          <VCheckbox v-model="props.formData.source_of_funds" label="ادخارات" value="savings" />
        </div>
      </VCard>
    </VCol>

    <!-- 3. الدخل والمسؤولين -->
    <VCol cols="12" md="6">
      <VCard variant="outlined" class="pa-4 h-100">
        <VRow>
          <VCol cols="12">
            <AppSelect v-model="props.formData.monthly_income" :items="incomeOptions" label="الدخل الشهري الإجمالي *" />
          </VCol>
          <VCol cols="12">
            <AppTextField v-model="props.formData.aml_officer_name" label="اسم مسؤول الإبلاغ (للمصادقة) *" placeholder="مثال: أوس محمد" />
          </VCol>
        </VRow>
      </VCard>
    </VCol>
  </VRow>
</template>
