<script setup lang="ts">
interface Props { formData: any }
const props = defineProps<Props>()
const ed = props.formData.engineeringDetails
</script>

<template>
  <VRow>
    <VCol cols="12">
      <div class="d-flex align-center gap-2 mb-4">
        <VIcon icon="tabler-building-arch" color="warning" />
        <h6 class="text-h6 mb-0">استمارة طلب تأمين المقاولات ضد كافة الأخطار (أعمال هندسية مدنية)</h6>
      </div>
    </VCol>

    <!-- ١. معلومات الأطراف -->
    <VCol cols="12">
      <VCard variant="outlined" class="pa-4">
        <div class="text-overline mb-3 text-warning">معلومات الأطراف</div>
        <VRow>
          <VCol cols="12" md="6">
            <AppTextField v-model="ed.applicant_name"    label="استمارة طالب التأمين *" />
          </VCol>
          <VCol cols="12" md="6">
            <AppTextField v-model="ed.applicant_address" label="عنوانه البريدي" />
          </VCol>
          <VCol cols="12" md="4">
            <AppTextField v-model="ed.local_agent"       label="المواطن المختار" />
          </VCol>
          <VCol cols="12" md="4">
            <AppTextField v-model="ed.project_owner"     label="اسم صاحب المشروع" />
          </VCol>
          <VCol cols="12" md="4">
            <AppTextField v-model="ed.project_location"  label="موقع المشروع" />
          </VCol>
          <VCol cols="12" md="6">
            <AppTextField v-model="ed.subcontractor"     label="اسم وعنوان المقاول الثانوي" />
          </VCol>
          <VCol cols="12" md="6">
            <AppTextField v-model="ed.consultant"        label="اسم وعنوان المهندس الاستشاري" />
          </VCol>
        </VRow>
      </VCard>
    </VCol>

    <!-- أ. رقم المقاولة واسمها -->
    <VCol cols="12">
      <VCard variant="outlined" class="pa-4">
        <div class="text-overline mb-3 text-warning">أ- رقم المقاولة واسمها</div>
        <VRow>
          <VCol cols="12" md="4">
            <AppTextField v-model="ed.contract_no"   label="رقم المقاولة" />
          </VCol>
          <VCol cols="12" md="8">
            <AppTextField v-model="ed.contract_name" label="اسم المقاولة / المشروع *" />
          </VCol>
          <VCol cols="12">
            <AppTextarea v-model="ed.contract_phases" label="المراحل المطلوب تأمينها (إذا كان التنفيذ على مراحل)" rows="2" />
          </VCol>
        </VRow>
      </VCard>
    </VCol>

    <!-- ٢. مدة المقاولة -->
    <VCol cols="12">
      <VCard variant="outlined" class="pa-4">
        <div class="text-overline mb-3 text-warning">٢- مدة المقاولة</div>
        <VRow>
          <VCol cols="12">
            <div class="text-subtitle-2 mb-2">مدة الإنشاء</div>
          </VCol>
          <VCol cols="12" md="3">
            <AppTextField v-model="ed.construction_months" label="مدة الإنشاء (شهراً)" type="number" />
          </VCol>
          <VCol cols="12" md="3">
            <AppTextField v-model="ed.construction_start"  label="تبدأ من" type="date" />
          </VCol>
          <VCol cols="12" md="3">
            <AppTextField v-model="ed.construction_end"    label="وتنتهي في" type="date" />
          </VCol>
          <VDivider class="my-2" />
          <VCol cols="12">
            <div class="text-subtitle-2 mb-2">مدة الصيانة (إذا أريد شمولها بالتأمين)</div>
          </VCol>
          <VCol cols="12" md="3">
            <AppTextField v-model="ed.maintenance_months" label="مدة الصيانة (شهراً)" type="number" />
          </VCol>
          <VCol cols="12" md="3">
            <AppTextField v-model="ed.maintenance_start"  label="تبدأ من" type="date" />
          </VCol>
          <VCol cols="12" md="3">
            <AppTextField v-model="ed.maintenance_end"    label="وتنتهي في" type="date" />
          </VCol>
        </VRow>
      </VCard>
    </VCol>

    <!-- ٣. وصف عام للأعمال -->
    <VCol cols="12">
      <VCard variant="outlined" class="pa-4">
        <div class="text-overline mb-3 text-warning">٣- وصف عام للأعمال المطلوبة</div>
        <AppTextarea
          v-model="ed.works_description"
          label="وصف كافة الأعمال الدائمية والمؤقتة مع المواد المستعملة في إنشائها"
          rows="3"
        />
      </VCard>
    </VCol>

    <!-- ٤. مبلغ المقاولة -->
    <VCol cols="12">
      <VCard variant="outlined" class="pa-4">
        <div class="text-overline mb-3 text-warning">٤- مبلغ المقاولة</div>
        <VRow>
          <VCol cols="12">
            <div class="text-subtitle-2 mb-1 font-weight-bold">أ- مبلغ المواد المجهزة من قبل صاحب المقاولة</div>
          </VCol>
          <VCol cols="12" md="5">
            <AppTextField v-model="ed.owner_materials_value" label="القيمة (د.ع)" type="number" />
          </VCol>
          <VCol cols="12" md="7">
            <AppTextField v-model="ed.owner_materials_desc"  label="ماهيتها / وصفها" />
          </VCol>

          <VCol cols="12" class="mt-2">
            <div class="text-subtitle-2 mb-1 font-weight-bold">ب- أدوات ومعدات الإنشاء (سكلات، جسور، قوالب، أجهزة توليد طاقة، مكاتب مؤقتة...)</div>
          </VCol>
          <VCol cols="12" md="5">
            <AppTextField v-model="ed.equipment_value" label="القيمة الإجمالية (د.ع)" type="number" />
          </VCol>
          <VCol cols="12" md="7">
            <AppTextarea v-model="ed.equipment_desc"  label="وصف المعدات والأعمال المؤقتة" rows="2" />
          </VCol>

          <VCol cols="12" class="mt-2">
            <div class="text-subtitle-2 mb-1 font-weight-bold">ج- قيمة المكائن المستعملة في الإنشاء</div>
          </VCol>
          <VCol cols="12" md="5">
            <AppTextField v-model="ed.machinery_value" label="تقدير كلفة الاستبدال (د.ع)" type="number" />
          </VCol>
          <VCol cols="12" md="7">
            <AppTextField v-model="ed.machinery_list_attached" label="هل أُرفقت قائمة المكائن؟ (نعم / لا)" />
          </VCol>
        </VRow>
      </VCard>
    </VCol>

    <!-- هـ. أعمال المقاول الثانوي -->
    <VCol cols="12">
      <VCard variant="outlined" class="pa-4">
        <div class="text-overline mb-3 text-warning">هـ- الأعمال التي يقوم بها المقاول الثانوي (إن وجد)</div>
        <AppTextarea v-model="ed.subcontractor_works" label="وصف الأعمال" rows="2" />
      </VCard>
    </VCol>

    <!-- ٦. معلومات الموقع -->
    <VCol cols="12">
      <VCard variant="outlined" class="pa-4">
        <div class="text-overline mb-3 text-warning">٦- معلومات الموقع والبيئة</div>
        <VRow>
          <VCol cols="12" md="6">
            <AppTextField v-model="ed.seismic_risk"        label="أ) خطر الزلازل" />
          </VCol>
          <VCol cols="12" md="6">
            <AppTextField v-model="ed.soil_nature"         label="ب) طبيعة الأرض والتربة" />
          </VCol>
          <VCol cols="12" md="6">
            <AppTextField v-model="ed.water_level_surface" label="ج) مستوى المياه بالنسبة لسطح الأرض" />
          </VCol>
          <VCol cols="12" md="6">
            <AppTextField v-model="ed.nearest_water_body"  label="د) اسم وبعد أقرب نهر أو بحيرة أو بحر" />
          </VCol>
          <VCol cols="12">
            <div class="text-subtitle-2 mb-2">هـ) مستوى سطح الماء في الحالات التالية:</div>
          </VCol>
          <VCol cols="12" md="4">
            <AppTextField v-model="ed.water_level_min" label="١- أدنى مستوى" />
          </VCol>
          <VCol cols="12" md="4">
            <AppTextField v-model="ed.water_level_avg" label="٢- متوسط المستوى" />
          </VCol>
          <VCol cols="12" md="4">
            <AppTextField v-model="ed.water_level_max" label="٣- أعلى مستوى" />
          </VCol>
          <VCol cols="12" md="6">
            <AppTextField v-model="ed.flood_risk"          label="و) هل المنطقة معرضة لخطر الفيضان؟ وما هي الاحتياطات؟" />
          </VCol>
          <VCol cols="12" md="6">
            <AppTextField v-model="ed.weather_conditions"  label="ز) الأحوال الجوية (موسم الأمطار، العواصف، معدل هطول الأمطار)" />
          </VCol>
        </VRow>
      </VCard>
    </VCol>

    <!-- ٧-٩: أسئلة التعويض -->
    <VCol cols="12">
      <VCard variant="outlined" class="pa-4">
        <div class="text-overline mb-3 text-warning">٧- ١٢: أسئلة التغطية والتعويض</div>
        <VRow>
          <VCol cols="12">
            <div class="text-subtitle-2 font-weight-bold mb-1">٧- هل ترغب في تعويض أجور النقل السريع / الساعات الإضافية / العمل الرسمي؟</div>
            <VRadioGroup v-model="ed.express_freight_coverage" inline hide-details>
              <VRadio label="نعم" value="yes" />
              <VRadio label="لا"  value="no" />
            </VRadioGroup>
          </VCol>
          <VCol cols="12" md="6">
            <AppTextField v-model="ed.max_loss_per_incident"  label="٨- مدى الخسارة الكلية أو الجزئية من جراء حادث واحد (د.ع)" type="number" />
          </VCol>
          <VCol cols="12" md="6">
            <AppTextField v-model="ed.debris_removal_limit"   label="٩- حدود تعويض رفع الأنقاض عند وقوع حادث (د.ع)" type="number" />
          </VCol>

          <VCol cols="12" class="mt-2">
            <div class="text-subtitle-2 font-weight-bold mb-1">١٠- التأمين ضد خسائر تصيب منشآت قائمة في موقع العمل أو مجاورة له</div>
            <VRadioGroup v-model="ed.existing_structures_coverage" inline hide-details class="mb-2">
              <VRadio label="نعم" value="yes" />
              <VRadio label="لا"  value="no" />
            </VRadioGroup>
            <AppTextField v-if="ed.existing_structures_coverage === 'yes'" v-model="ed.existing_structures_value" label="مبلغ التأمين مع تفاصيل الأبنية (د.ع)" type="number" />
          </VCol>

          <VCol cols="12" class="mt-2">
            <div class="text-subtitle-2 font-weight-bold mb-2">١١- حدود التعويض عن المسؤولية المدنية</div>
          </VCol>
          <VCol cols="12" md="4">
            <AppTextField v-model="ed.civil_liability_per_event"    label="أ) الحد الأعلى للحادث الواحد / سلسلة حوادث (د.ع)" type="number" />
          </VCol>
          <VCol cols="12" md="4">
            <AppTextField v-model="ed.civil_liability_bodily_injury" label="ب) عن الإصابات البدنية (د.ع)" type="number" />
          </VCol>
          <VCol cols="12" md="4">
            <AppTextField v-model="ed.civil_liability_property"      label="ج) عن أضرار الممتلكات (د.ع)" type="number" />
          </VCol>

          <VCol cols="12" class="mt-2">
            <div class="text-subtitle-2 font-weight-bold mb-1">١٢- وصف المنطقة المجاورة والممتلكات المحيطة بالعمل</div>
            <AppTextarea v-model="ed.surrounding_area_desc" label="(الحفريات، المسانيد، الركائز، أعمال وضع المياه الجوفية...)" rows="3" />
          </VCol>
        </VRow>
      </VCard>
    </VCol>

    <!-- د. وصف تفصيلي لأعمال المقاولة -->
    <VCol cols="12">
      <VCard variant="outlined" class="pa-4">
        <div class="text-overline mb-3 text-warning">د- وصف تفصيلي لأعمال المقاولة</div>
        <VRow>
          <VCol cols="12" md="6">
            <AppTextField v-model="ed.detail_region"    label="المنطقة" />
          </VCol>
          <VCol cols="12" md="6">
            <AppTextField v-model="ed.detail_dimensions" label="الأبعاد: الطول، الارتفاع، العمق، عدد الطوابق...إلخ" />
          </VCol>
          <VCol cols="12" md="6">
            <AppTextField v-model="ed.detail_foundation" label="الأساس: طريقة العمل، أعمق نقطة في الحفر" />
          </VCol>
          <VCol cols="12" md="6">
            <AppTextField v-model="ed.detail_construction_method" label="طريقة الإنشاء" />
          </VCol>
          <VCol cols="12" md="6">
            <AppTextField v-model="ed.detail_materials"  label="المواد الإنشائية" />
          </VCol>
          <VCol cols="12" md="6">
            <AppTextField v-model="ed.detail_demolition" label="الهدم والتفجير (إن وجد)" />
          </VCol>
          <VCol cols="12">
            <AppTextarea v-model="ed.detail_notes" label="ملاحظات إضافية" rows="2" />
          </VCol>
        </VRow>
      </VCard>
    </VCol>

  </VRow>
</template>
