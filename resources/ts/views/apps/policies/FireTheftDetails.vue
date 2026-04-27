<script setup lang="ts">
interface Props {
  formData: any
}

const props = defineProps<Props>()

const categories = [
  { title: 'البناء (عن الأرض والأسس، تأسيسات، تكييف)', value: 'building' },
  { title: 'البضائع (جاهزة، تحت الصنع، أولية، احتياطية)', value: 'goods' },
  { title: 'الآلات والمكائن وملحقاتها', value: 'machinery' },
  { title: 'الأثاث', value: 'furniture' },
  { title: 'أموال أخرى', value: 'others' },
]

const yesNoOptions = [
  { title: 'نعم', value: true },
  { title: 'لا', value: false },
]
</script>

<template>
  <VRow>
    <VCol cols="12">
      <div class="d-flex align-center gap-2 mb-4">
        <VIcon icon="tabler-building-store" color="primary" />
        <h6 class="text-h6 mb-0">استمارة طلب تأمين الحريق والسرقة للمحلات</h6>
      </div>
    </VCol>

    <!-- 1. المعلومات الأساسية للمحل -->
    <VCol cols="12">
      <VCard variant="outlined" class="pa-4 border-dashed">
        <div class="text-overline mb-3 text-primary">المعلومات التعريفية للمحل</div>
        <VRow>
          <VCol cols="12" md="6">
            <AppTextField v-model="props.formData.trade_name" label="الاسم التجاري للمحل" />
          </VCol>
          <VCol cols="12" md="6">
            <AppTextField v-model="props.formData.permanent_address" label="العنوان الدائم لطالب التأمين" />
          </VCol>
          <VCol cols="12" md="4">
            <AppTextField v-model="props.formData.district" label="الحي / المنطقة" />
          </VCol>
          <VCol cols="12" md="4">
            <AppTextField v-model="props.formData.street_region" label="اسم الشارع" />
          </VCol>
          <VCol cols="12" md="4">
            <AppTextField v-model="props.formData.shop_phone" label="رقم هاتف المحل" />
          </VCol>
        </VRow>
      </VCard>
    </VCol>

    <!-- 2. الاستبيان الفني (17 سؤال) -->
    <VCol cols="12">
      <VExpansionPanels variant="accordion">
        <VExpansionPanel>
          <VExpansionPanelTitle>الاستبيان الفني وتفاصيل الأخطار (17 فقرة)</VExpansionPanelTitle>
          <VExpansionPanelText>
            <VRow class="mt-2">
              <VCol cols="12" md="6">
                <AppSelect v-model="props.formData.fireTheftDetails.is_owner" :items="[{title:'نعم', value:true}, {title:'لا', value:false}]" label="هل أنت مالك الأموال أو مسؤؤل عنها؟ *" />
              </VCol>
              <VCol cols="12" md="6">
                <AppSelect v-model="props.formData.fireTheftDetails.has_accounting_records" :items="[{title:'نعم', value:true}, {title:'لا', value:false}]" label="هل توجد سجلات محاسبية وجرد منتظم؟ *" />
              </VCol>
              <VCol cols="12" md="6">
                <AppTextField v-model="props.formData.fireTheftDetails.jewelry_storage" label="أين تحفظ المصوغات والذهب؟ *" />
              </VCol>
              <VCol cols="12" md="6">
                <AppSelect v-model="props.formData.fireTheftDetails.is_insured_amount_real" :items="[{title:'نعم', value:true}, {title:'لا', value:false}]" label="هل مبلغ التأمين يمثل القيمة الحقيقية؟ *" />
              </VCol>
              <VCol cols="12" md="6">
                <AppTextField v-model="props.formData.fireTheftDetails.closing_duration" label="ما هي مدة إغلاق المحل؟ *" />
              </VCol>
              <VCol cols="12" md="6">
                <AppTextField v-model="props.formData.fireTheftDetails.guarding_nature" label="ما هي طبيعة الحراسة؟ *" />
              </VCol>
              <VCol cols="12" md="6">
                <AppTextField v-model="props.formData.fireTheftDetails.previous_incidents" label="هل سبق وقوع حوادث سرقة أو حريق في المحل؟" />
              </VCol>
              <VCol cols="12" md="6">
                <AppTextField v-model="props.formData.fireTheftDetails.neighbors_incidents" label="هل تعرّض الجيران لحوادث مماثلة؟" />
              </VCol>
              <VCol cols="12" md="6">
                <AppTextField v-model="props.formData.fireTheftDetails.hazardous_materials" label="هل تُخزّن مواد قابلة للاشتعال أو خطرة؟" />
              </VCol>
              <VCol cols="12" md="6">
                <AppTextField v-model="props.formData.fireTheftDetails.previous_insurance_history" label="تاريخ التأمينات السابقة (الشركة / رقم الوثيقة)" />
              </VCol>

              <VCol cols="12">
                <VDivider class="my-2" />
                <div class="text-subtitle-2 mb-2 font-weight-bold">الأخطار الإضافية المطلوب تغطيتها:</div>
                <div class="d-flex flex-wrap gap-4">
                  <VCheckbox v-model="props.formData.fireTheftDetails.peril_explosion" label="الانفجار" />
                  <VCheckbox v-model="props.formData.fireTheftDetails.peril_flood" label="الفيضان" />
                  <VCheckbox v-model="props.formData.fireTheftDetails.peril_storm" label="الزوابع والعواصف" />
                  <VCheckbox v-model="props.formData.fireTheftDetails.peril_riot" label="الشغب والاضطرابات المدنية" />
                  <VCheckbox v-model="props.formData.fireTheftDetails.peril_tank_overflow" label="فيضان خزانات المياه" />
                  <VCheckbox v-model="props.formData.fireTheftDetails.peril_self_combustion" label="الاشتعال الذاتي" />
                  <VCheckbox v-model="props.formData.fireTheftDetails.peril_aircraft_impact" label="اصطدام الطائرات" />
                  <VCheckbox v-model="props.formData.fireTheftDetails.peril_earthquake" label="الزلازل" />
                </div>
              </VCol>
            </VRow>
          </VExpansionPanelText>
        </VExpansionPanel>
      </VExpansionPanels>
    </VCol>

    <!-- 3. تقرير الكشف الفني المطور -->
    <VCol cols="12">
      <VCard variant="tonal" class="pa-4 bg-light">
        <h6 class="text-subtitle-1 mb-4 d-flex align-center">
          <VIcon icon="tabler-report-search" class="me-2" />
          تقرير الكشف الفني على البناء
        </h6>
        <VRow>
          <VCol cols="12" md="4">
            <AppTextField v-model="props.formData.inspection.wall_material" label="مادة الجدران" />
          </VCol>
          <VCol cols="12" md="4">
            <AppTextField v-model="props.formData.inspection.roof_material" label="مادة السقوف" />
          </VCol>
          <VCol cols="12" md="4">
            <AppTextField v-model="props.formData.inspection.doors_locks_type" label="مادة الأبواب ونوع الأقفال" />
          </VCol>
          <VCol cols="12" md="6">
            <AppSelect v-model="props.formData.inspection.window_grids" :items="yesNoOptions" label="هل الشبابيك مزودة بكتائب حديدية؟" />
          </VCol>
          <VCol cols="12" md="6">
            <AppTextField v-model="props.formData.inspection.extinguishers" label="أدوات الإطفاء ومواقعها" />
          </VCol>
          <VCol cols="12">
            <AppTextarea v-model="props.formData.inspection.construction_description" label="وصف كامل للبناء (عدد الطوابق، الجيران...)" rows="2" />
          </VCol>
        </VRow>
      </VCard>
    </VCol>

    <!-- 4. جدول تفاصيل الأموال -->
    <VCol cols="12">
      <h6 class="text-subtitle-1 mb-2 font-weight-bold">جدول تفاصيل الأموال المطلوب التأمين عليها</h6>
      <VTable density="compact" class="border rounded">
        <thead class="bg-var-theme-background">
          <tr>
            <th style="width: 40%">الصنف / التفاصيل</th>
            <th style="width: 20%">المبلغ المطلوب</th>
            <th style="width: 40%">ملاحظات إضافية</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="cat in categories" :key="cat.value">
            <td class="text-body-2 py-2">{{ cat.title }}</td>
            <td>
              <AppTextField v-model="props.formData.funds[cat.value].value" type="number" density="compact" hide-details variant="plain" />
            </td>
            <td>
              <AppTextField v-model="props.formData.funds[cat.value].description" density="compact" hide-details variant="plain" />
            </td>
          </tr>
        </tbody>
      </VTable>
    </VCol>
  </VRow>
</template>

<style scoped>
.border-dashed {
  border-style: dashed !important;
}
</style>
