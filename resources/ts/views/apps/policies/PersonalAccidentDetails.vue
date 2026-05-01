<script setup lang="ts">
const props = defineProps<{
  formData: any
}>()

const occupationClasses = [
  { title: 'مكتبي (خطر منخفض)', value: 'class_1' },
  { title: 'مهني/حرفي (خطر متوسط)', value: 'class_2' },
  { title: 'صناعي/بناء (خطر عالٍ)', value: 'class_3' },
  { title: 'مناجم/كهرباء (خطر شديد)', value: 'class_4' },
]

const coverTypes = [
  { title: 'الوفاة فقط', value: 'death_only' },
  { title: 'الوفاة + العجز الكلي', value: 'death_tpd' },
  { title: 'شامل (وفاة + عجز كلي + جزئي + مصاريف علاج)', value: 'comprehensive' },
]
</script>

<template>
  <VRow>
    <VCol cols="12">
      <div class="text-subtitle-1 mb-4 font-weight-bold">بيانات تأمين الحوادث الشخصية</div>
    </VCol>
    <VCol cols="12" md="6">
      <AppTextField
        v-model="formData.personalAccidentDetails.insuredName"
        label="اسم المؤمن عليه *"
        placeholder="الاسم الكامل كما في الهوية"
      />
    </VCol>
    <VCol cols="12" md="6">
      <AppTextField
        v-model="formData.personalAccidentDetails.idNumber"
        label="رقم الهوية/الجواز *"
      />
    </VCol>
    <VCol cols="12" md="6">
      <AppSelect
        v-model="formData.personalAccidentDetails.occupationClass"
        label="فئة المهنة *"
        :items="occupationClasses"
      />
    </VCol>
    <VCol cols="12" md="6">
      <AppTextField
        v-model="formData.personalAccidentDetails.occupation"
        label="المهنة بالتفصيل *"
        placeholder="مثال: مهندس مدني، محاسب..."
      />
    </VCol>
    <VCol cols="12" md="6">
      <AppSelect
        v-model="formData.personalAccidentDetails.coverType"
        label="نطاق التغطية *"
        :items="coverTypes"
      />
    </VCol>
    <VCol cols="12" md="6">
      <AppTextField
        v-model="formData.personalAccidentDetails.sumInsured"
        label="مبلغ التأمين (د.ع) *"
        type="number"
      />
    </VCol>
    <VCol cols="12" md="6">
      <AppTextField
        v-model="formData.personalAccidentDetails.age"
        label="العمر *"
        type="number"
        min="16"
        max="70"
      />
    </VCol>
    <VCol cols="12" md="6">
      <AppSelect
        v-model="formData.personalAccidentDetails.includesMedical"
        label="يشمل المصاريف الطبية؟"
        :items="[{title:'نعم', value:true}, {title:'لا', value:false}]"
      />
    </VCol>
    <VCol cols="12" md="6">
      <AppTextField
        v-model="formData.personalAccidentDetails.medicalLimit"
        label="حد المصاريف الطبية (د.ع)"
        type="number"
      />
    </VCol>
    <VCol cols="12" md="6">
      <AppTextField
        v-model="formData.personalAccidentDetails.beneficiaryName"
        label="اسم المستفيد في حال الوفاة *"
        placeholder="الاسم الكامل للمستفيد"
      />
    </VCol>
    <VCol cols="12" md="6">
      <AppSelect
        v-model="formData.personalAccidentDetails.beneficiaryRelation"
        label="صلة القرابة *"
        :items="[
          {title:'زوج/زوجة', value:'spouse'},
          {title:'ابن/ابنة', value:'child'},
          {title:'والد/والدة', value:'parent'},
          {title:'أخ/أخت', value:'sibling'},
          {title:'غير ذلك', value:'other'},
        ]"
      />
    </VCol>
    <VCol cols="12">
      <AppTextarea
        v-model="formData.personalAccidentDetails.exclusions"
        label="استثناءات وملاحظات"
        placeholder="أي استثناءات أو ملاحظات خاصة..."
        rows="2"
      />
    </VCol>
  </VRow>
</template>
