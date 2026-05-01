<script setup lang="ts">
const props = defineProps<{
  formData: any
}>()

const locationTypes = [
  { title: 'فرع بنك', value: 'bank_branch' },
  { title: 'مكتب شركة', value: 'office' },
  { title: 'محل تجاري', value: 'shop' },
  { title: 'مستودع', value: 'warehouse' },
  { title: 'أثناء النقل', value: 'in_transit' },
  { title: 'صرافة ATM', value: 'atm' },
]

const securityLevels = [
  { title: 'عالية (خزينة + كاميرات + حراس)', value: 'high' },
  { title: 'متوسطة (خزينة + كاميرات)', value: 'medium' },
  { title: 'عادية (خزينة فقط)', value: 'low' },
]
</script>

<template>
  <VRow>
    <VCol cols="12">
      <div class="text-subtitle-1 mb-4 font-weight-bold">بيانات تأمين النقد</div>
    </VCol>
    <VCol cols="12" md="6">
      <AppTextField
        v-model="formData.cashDetails.insuredName"
        label="اسم المؤمن له *"
        placeholder="اسم الشركة أو الجهة صاحبة النقد"
      />
    </VCol>
    <VCol cols="12" md="6">
      <AppSelect
        v-model="formData.cashDetails.locationType"
        label="نوع الموقع *"
        :items="locationTypes"
      />
    </VCol>
    <VCol cols="12" md="6">
      <AppTextField
        v-model="formData.cashDetails.locationAddress"
        label="عنوان الموقع *"
        placeholder="العنوان التفصيلي للموقع"
      />
    </VCol>
    <VCol cols="12" md="6">
      <AppSelect
        v-model="formData.cashDetails.securityLevel"
        label="مستوى الأمن والحراسة *"
        :items="securityLevels"
      />
    </VCol>
    <VCol cols="12" md="6">
      <AppTextField
        v-model="formData.cashDetails.maxCashOnHand"
        label="الحد الأقصى للنقد بالموقع (د.ع) *"
        type="number"
      />
    </VCol>
    <VCol cols="12" md="6">
      <AppTextField
        v-model="formData.cashDetails.maxCashInTransit"
        label="الحد الأقصى للنقد أثناء النقل (د.ع) *"
        type="number"
      />
    </VCol>
    <VCol cols="12" md="6">
      <AppTextField
        v-model="formData.cashDetails.transitFrequency"
        label="عدد مرات النقل شهرياً *"
        type="number"
        min="0"
      />
    </VCol>
    <VCol cols="12" md="6">
      <AppTextField
        v-model="formData.cashDetails.transitRoute"
        label="مسار النقل المعتاد"
        placeholder="مثال: من الفرع الرئيسي إلى البنك المركزي"
      />
    </VCol>
    <VCol cols="12" md="6">
      <AppSelect
        v-model="formData.cashDetails.hasGuard"
        label="يوجد حراس مسلحين؟"
        :items="[{title:'نعم', value:true}, {title:'لا', value:false}]"
      />
    </VCol>
    <VCol cols="12" md="6">
      <AppSelect
        v-model="formData.cashDetails.hasCCTV"
        label="يوجد كاميرات مراقبة؟"
        :items="[{title:'نعم', value:true}, {title:'لا', value:false}]"
      />
    </VCol>
    <VCol cols="12" md="6">
      <AppSelect
        v-model="formData.cashDetails.hasVault"
        label="يوجد خزينة حديدية؟ *"
        :items="[{title:'نعم', value:true}, {title:'لا', value:false}]"
      />
    </VCol>
    <VCol cols="12" md="6">
      <AppTextField
        v-model="formData.cashDetails.vaultType"
        label="نوع الخزينة"
        placeholder="مثال: خزينة درجة 1، خزينة إلكترونية..."
      />
    </VCol>
    <VCol cols="12">
      <AppTextarea
        v-model="formData.cashDetails.notes"
        label="ملاحظات وشروط خاصة"
        placeholder="أي ملاحظات أو شروط خاصة بالتغطية..."
        rows="2"
      />
    </VCol>
  </VRow>
</template>
