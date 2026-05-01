<script setup lang="ts">
const props = defineProps<{
  formData: any
}>()

const transportModes = [
  { title: 'بحري', value: 'sea' },
  { title: 'جوي', value: 'air' },
  { title: 'بري', value: 'land' },
  { title: 'متعدد', value: 'multimodal' },
]

const cargoTypes = [
  { title: 'بضائع عامة', value: 'general' },
  { title: 'مواد خطرة', value: 'hazardous' },
  { title: 'أغذية', value: 'food' },
  { title: 'معدات', value: 'equipment' },
  { title: 'سيارات', value: 'vehicles' },
  { title: 'حيوانات', value: 'livestock' },
]
</script>

<template>
  <VRow>
    <VCol cols="12">
      <div class="text-subtitle-1 mb-4 font-weight-bold">بيانات شحن النقل والبحري</div>
    </VCol>
    <VCol cols="12" md="6">
      <AppTextField
        v-model="formData.transportMarineDetails.shipperName"
        label="اسم الشاحن *"
        placeholder="اسم شركة الشحن أو المرسل"
      />
    </VCol>
    <VCol cols="12" md="6">
      <AppTextField
        v-model="formData.transportMarineDetails.consigneeName"
        label="اسم المستلم *"
        placeholder="اسم المستلم أو الشركة المستلمة"
      />
    </VCol>
    <VCol cols="12" md="6">
      <AppSelect
        v-model="formData.transportMarineDetails.transportMode"
        label="وسيلة النقل *"
        :items="transportModes"
      />
    </VCol>
    <VCol cols="12" md="6">
      <AppSelect
        v-model="formData.transportMarineDetails.cargoType"
        label="نوع البضاعة *"
        :items="cargoTypes"
      />
    </VCol>
    <VCol cols="12" md="4">
      <AppTextField
        v-model="formData.transportMarineDetails.origin"
        label="ميناء/مدينة الشحن *"
        placeholder="مثال: بندر عباس"
      />
    </VCol>
    <VCol cols="12" md="4">
      <AppTextField
        v-model="formData.transportMarineDetails.destination"
        label="ميناء/مدينة الوصول *"
        placeholder="مثال: أم قصر"
      />
    </VCol>
    <VCol cols="12" md="4">
      <AppTextField
        v-model="formData.transportMarineDetails.vesselName"
        label="اسم الناقلة/المركبة"
        placeholder="اسم السفينة أو السيارة أو الطائرة"
      />
    </VCol>
    <VCol cols="12" md="6">
      <AppTextField
        v-model="formData.transportMarineDetails.billOfLadingNo"
        label="رقم بوليصة الشحن"
        placeholder="B/L number"
      />
    </VCol>
    <VCol cols="12" md="6">
      <AppTextField
        v-model="formData.transportMarineDetails.containerCount"
        label="عدد الحاويات"
        type="number"
        min="0"
      />
    </VCol>
    <VCol cols="12" md="6">
      <AppTextField
        v-model="formData.transportMarineDetails.cargoValue"
        label="قيمة البضاعة المؤمنة (د.ع) *"
        type="number"
      />
    </VCol>
    <VCol cols="12" md="6">
      <AppTextField
        v-model="formData.transportMarineDetails.invoiceNo"
        label="رقم الفاتورة التجارية"
      />
    </VCol>
    <VCol cols="12">
      <AppTextarea
        v-model="formData.transportMarineDetails.cargoDescription"
        label="وصف البضاعة *"
        placeholder="وصف تفصيلي للبضاعة المؤمن عليها..."
        rows="3"
      />
    </VCol>
    <VCol cols="12">
      <AppTextarea
        v-model="formData.transportMarineDetails.specialConditions"
        label="شروط خاصة"
        placeholder="أي شروط خاصة بالتغطية..."
        rows="2"
      />
    </VCol>
  </VRow>
</template>
