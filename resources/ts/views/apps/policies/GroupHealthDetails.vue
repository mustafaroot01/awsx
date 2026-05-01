<script setup lang="ts">
const props = defineProps<{
  formData: any
}>()

const coverageTypes = [
  { title: 'أساسي', value: 'basic' },
  { title: 'شامل', value: 'comprehensive' },
  { title: 'VIP', value: 'vip' },
]

const maritalStatuses = [
  { title: 'أعزب/عزباء', value: 'single' },
  { title: 'متزوج/ة', value: 'married' },
]
</script>

<template>
  <VRow>
    <VCol cols="12">
      <div class="text-subtitle-1 mb-4 font-weight-bold">بيانات التغطية الصحية الجماعية</div>
    </VCol>
    <VCol cols="12" md="6">
      <AppTextField
        v-model="formData.groupHealthDetails.companyName"
        label="اسم الشركة/الجهة *"
        placeholder="اسم الجهة المؤمنة"
      />
    </VCol>
    <VCol cols="12" md="6">
      <AppTextField
        v-model="formData.groupHealthDetails.employeeCount"
        label="عدد المشتركين *"
        type="number"
        min="1"
      />
    </VCol>
    <VCol cols="12" md="6">
      <AppSelect
        v-model="formData.groupHealthDetails.coverageType"
        label="نوع التغطية *"
        :items="coverageTypes"
      />
    </VCol>
    <VCol cols="12" md="6">
      <AppTextField
        v-model="formData.groupHealthDetails.coverageLimit"
        label="حد التغطية السنوي (د.ع) *"
        type="number"
      />
    </VCol>
    <VCol cols="12" md="6">
      <AppTextField
        v-model="formData.groupHealthDetails.deductible"
        label="نسبة المشترك (deductible %) *"
        type="number"
        min="0"
        max="100"
      />
    </VCol>
    <VCol cols="12" md="6">
      <AppSelect
        v-model="formData.groupHealthDetails.includesFamily"
        label="يشمل العائلة؟"
        :items="[{title:'نعم', value:true}, {title:'لا', value:false}]"
      />
    </VCol>
    <VCol cols="12">
      <AppTextarea
        v-model="formData.groupHealthDetails.exclusions"
        label="الاستثناءات والأمراض المزمنة"
        placeholder="أي استثناءات أو أمراض مزمنة غير مغطاة..."
        rows="3"
      />
    </VCol>
    <VCol cols="12">
      <AppTextarea
        v-model="formData.groupHealthDetails.notes"
        label="ملاحظات إضافية"
        placeholder="أي ملاحظات إضافية..."
        rows="2"
      />
    </VCol>
  </VRow>
</template>
