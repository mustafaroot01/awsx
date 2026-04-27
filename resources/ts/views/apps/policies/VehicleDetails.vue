<script setup lang="ts">
interface Props { formData: any }
const props = defineProps<Props>()

const YN = [{ title: 'نعم', value: 'yes' }, { title: 'لا', value: 'no' }]

const coverageOptions = [
  { title: 'أ) كافة الأخطار (شامل)', value: 'comprehensive' },
  { title: 'ب) الشخص الثالث المدني + الحرائق والسرقة', value: 'third_fire_theft' },
  { title: 'ج) الشخص الثالث المدني فقط', value: 'third_only' },
]

const statusOptions = [
  { title: 'سالمة', value: 'ok' },
  { title: 'ناقصة', value: 'missing' },
  { title: 'متضررة', value: 'damaged' },
]

const vd = props.formData.vehicleDetails

// Inspection sections
const extItems = [
  { key: 'ext_body',    label: 'الهيكل المعدني' },
  { key: 'ext_paint',   label: 'الصبغ' },
  { key: 'ext_chrome',  label: 'الأجزاء الكروم أو النيكل' },
  { key: 'ext_emblem',  label: 'علامة السيارة' },
  { key: 'ext_wipers',  label: 'ماسحات المطر' },
  { key: 'ext_antenna', label: 'هوائي الراديو ونوعه' },
]
const intItems = [
  { key: 'int_seats',    label: 'المقاعد' },
  { key: 'int_floor',    label: 'غطاء الأرضية' },
  { key: 'int_radio',    label: 'الراديو ولوحته' },
  { key: 'int_ac',       label: 'مكيفات الهواء وأنواعها' },
  { key: 'int_recorder', label: 'مسجل الصوت ونوعيته' },
]
const glassItems = [
  { key: 'glass_front',   label: 'الزجاجة الأمامية' },
  { key: 'glass_rear',    label: 'الزجاجة الخلفية' },
  { key: 'glass_side',    label: 'الزجاجات الجانبية' },
  { key: 'light_main',    label: 'المصابيح الأصلية' },
  { key: 'light_signal',  label: 'مصابيح الإشارة' },
  { key: 'light_spare',   label: 'المصابيح الاحتياطية (بيروجكتور)' },
  { key: 'light_mirror',  label: 'مرآة جانبية' },
]
const tireItems = [
  { key: 'tire_front', label: 'الإطارات الأمامية' },
  { key: 'tire_rear',  label: 'الإطارات الخلفية' },
  { key: 'tire_spare', label: 'الإطار الاحتياطي' },
  { key: 'tire_caps',  label: 'أغطية الإطارات (كيبات)' },
  { key: 'tools',      label: 'الأدوات الاحتياطية' },
]
</script>

<template>
  <VRow>
    <VCol cols="12">
      <div class="d-flex align-center gap-2 mb-4">
        <VIcon icon="tabler-car" color="primary" />
        <h6 class="text-h6 mb-0">استمارة طلب تأمين السيارات</h6>
      </div>
    </VCol>

    <!-- ١. بيانات السيارة -->
    <VCol cols="12">
      <VCard variant="outlined" class="pa-4">
        <div class="text-overline mb-3 text-primary">بيانات السيارة</div>
        <VRow>
          <VCol cols="12" md="3">
            <AppTextField v-model="vd.car_type" label="نوع السيارة *" />
          </VCol>
          <VCol cols="12" md="2">
            <AppTextField v-model="vd.car_year" label="سنة الصنع *" type="number" />
          </VCol>
          <VCol cols="12" md="2">
            <AppTextField v-model="vd.engine_power" label="قوة الدائرة (CC)" />
          </VCol>
          <VCol cols="12" md="2">
            <AppTextField v-model="vd.color" label="اللون" />
          </VCol>
          <VCol cols="12" md="3">
            <AppTextField v-model="vd.plate_no" label="رقم السيارة (اللوحة)" />
          </VCol>
          <VCol cols="12" md="3">
            <AppTextField v-model="vd.chassis_no" label="رقم الشاصي / التأميني" />
          </VCol>
          <VCol cols="12" md="2">
            <AppTextField v-model="vd.seats" label="عدد المقاعد (بما فيها السائق)" type="number" />
          </VCol>
          <VCol cols="12" md="3">
            <AppTextField v-model="vd.purchase_price" label="سعر الشراء (د.ع)" type="number" />
          </VCol>
          <VCol cols="12" md="2">
            <AppTextField v-model="vd.purchase_date" label="تاريخ الشراء" type="date" />
          </VCol>
          <VCol cols="12" md="2">
            <AppTextField v-model="vd.current_value" label="تقدير القيمة الحالية (د.ع)" type="number" />
          </VCol>
        </VRow>
      </VCard>
    </VCol>

    <!-- ٢. نوع التغطية -->
    <VCol cols="12">
      <VCard variant="outlined" class="pa-4">
        <div class="text-overline mb-3 text-primary">نوع التغطية التأمينية</div>
        <VRadioGroup v-model="vd.coverage_type" inline>
          <VRadio v-for="o in coverageOptions" :key="o.value" :label="o.title" :value="o.value" />
        </VRadioGroup>
      </VCard>
    </VCol>

    <!-- ٣. الأسئلة -->
    <VCol cols="12">
      <VExpansionPanels variant="accordion">
        <VExpansionPanel>
          <VExpansionPanelTitle>الأسئلة التأمينية (١١ فقرة)</VExpansionPanelTitle>
          <VExpansionPanelText>
            <div class="q-item">
              <div class="q-label">١- هل كان التأمين على أكثر من سيارة واحدة؟ (اذكر عددها)</div>
              <VRow class="mt-1">
                <VCol cols="6" md="3">
                  <VRadioGroup v-model="vd.multiple_vehicles" inline hide-details>
                    <VRadio v-for="o in YN" :key="o.value" :label="o.title" :value="o.value" />
                  </VRadioGroup>
                </VCol>
                <VCol cols="6" md="3" v-if="vd.multiple_vehicles === 'yes'">
                  <AppTextField v-model="vd.multiple_vehicles_count" label="العدد" type="number" density="compact" />
                </VCol>
              </VRow>
            </div>

            <div class="q-item">
              <div class="q-label">٢- أ) المحل الذي توقف فيه السيارة أثناء الليل</div>
              <AppTextField v-model="vd.parking_location" label="الموقع" density="compact" class="mt-1" />
              <div class="q-label mt-2">ب) هل تشكو الشخص الذي يسوق السيارة من أي مشكلة صحية أو مزاجية؟</div>
              <VRadioGroup v-model="vd.driver_complaints" inline hide-details class="mt-1">
                <VRadio v-for="o in YN" :key="o.value" :label="o.title" :value="o.value" />
              </VRadioGroup>
              <AppTextField v-if="vd.driver_complaints === 'yes'" v-model="vd.driver_complaints_detail" label="التفاصيل" density="compact" class="mt-1" />
            </div>

            <div class="q-item">
              <div class="q-label">٤- أ) هل هي مستعارة أو يمتلكها أكثر من شخص؟</div>
              <VRadioGroup v-model="vd.is_borrowed" inline hide-details class="mt-1 mb-2">
                <VRadio v-for="o in YN" :key="o.value" :label="o.title" :value="o.value" />
              </VRadioGroup>
              <div class="q-label">ب) هل تُعامَل السيارة حق للغير (أجرة)؟</div>
              <VRadioGroup v-model="vd.used_for_hire" inline hide-details class="mt-1">
                <VRadio v-for="o in YN" :key="o.value" :label="o.title" :value="o.value" />
              </VRadioGroup>
            </div>

            <div class="q-item">
              <div class="q-label">١٠- أ) ما هي مدة محاركة السيارة (وصف الاستخدام)؟</div>
              <AppTextField v-model="vd.driving_period" label="طبيعة الاستخدام" density="compact" class="mt-1 mb-2" />
              <div class="q-label">ب) هل أجازة السوق نافذة المفعول في الوقت الحاضر؟</div>
              <VRadioGroup v-model="vd.license_valid" inline hide-details class="mt-1">
                <VRadio v-for="o in YN" :key="o.value" :label="o.title" :value="o.value" />
              </VRadioGroup>
            </div>

            <div class="q-item">
              <div class="q-label">هل طالب التأمين أو أحد أفراد عائلته متحمل من أي جزء من حادثة تعويض؟</div>
              <VRadioGroup v-model="vd.previous_claim" inline hide-details class="mt-1">
                <VRadio v-for="o in YN" :key="o.value" :label="o.title" :value="o.value" />
              </VRadioGroup>
              <AppTextField v-if="vd.previous_claim === 'yes'" v-model="vd.previous_claim_detail" label="التفاصيل" density="compact" class="mt-1" />
            </div>

            <div class="q-item">
              <div class="q-label">هل ثمة ذمة مثبتة في أوراق الملك؟</div>
              <VRadioGroup v-model="vd.policy_encumbrance" inline hide-details class="mt-1">
                <VRadio v-for="o in YN" :key="o.value" :label="o.title" :value="o.value" />
              </VRadioGroup>
            </div>

            <div class="q-item">
              <div class="q-label">هل شركة أخرى أو أحد أفراد أسرتها قامت بأي مما يلي:</div>
              <VRow class="mt-1">
                <VCol cols="12" md="6">
                  <span class="text-caption">أ) رفضت قبول طلب تأمين سيارة؟</span>
                  <VRadioGroup v-model="vd.insurer_rejected" inline hide-details>
                    <VRadio v-for="o in YN" :key="o.value" :label="o.title" :value="o.value" />
                  </VRadioGroup>
                </VCol>
                <VCol cols="12" md="6">
                  <span class="text-caption">ب) قدمت تأمين بشروط خاصة؟</span>
                  <VRadioGroup v-model="vd.insurer_special_terms" inline hide-details>
                    <VRadio v-for="o in YN" :key="o.value" :label="o.title" :value="o.value" />
                  </VRadioGroup>
                </VCol>
                <VCol cols="12" md="6">
                  <span class="text-caption">ج) طالبت بأقساط أعلى أو أكثر من واحد؟</span>
                  <VRadioGroup v-model="vd.insurer_higher_premium" inline hide-details>
                    <VRadio v-for="o in YN" :key="o.value" :label="o.title" :value="o.value" />
                  </VRadioGroup>
                </VCol>
                <VCol cols="12" md="6">
                  <span class="text-caption">د) ألغت عقد التأمين أول مرة أو من بعد حادث تعويض؟</span>
                  <VRadioGroup v-model="vd.insurer_cancelled" inline hide-details>
                    <VRadio v-for="o in YN" :key="o.value" :label="o.title" :value="o.value" />
                  </VRadioGroup>
                </VCol>
              </VRow>
            </div>
          </VExpansionPanelText>
        </VExpansionPanel>
      </VExpansionPanels>
    </VCol>

    <!-- ٤. جدول الحوادث السابقة -->
    <VCol cols="12">
      <div class="text-subtitle-1 mb-2 font-weight-bold">١١- بيان الحوادث / الخسائر خلال السنوات الثلاث الماضية</div>
      <VTable density="compact" class="border rounded">
        <thead class="bg-var-theme-background">
          <tr>
            <th>السنة</th>
            <th>المركبة التي تملكها</th>
            <th>فئة الحوادث السابقة</th>
            <th>المبلغ الإجمالي</th>
            <th>ملاحظات</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(row, i) in vd.claims" :key="i">
            <td><AppTextField v-model="row.year"     density="compact" hide-details variant="plain" /></td>
            <td><AppTextField v-model="row.vehicle"  density="compact" hide-details variant="plain" /></td>
            <td><AppTextField v-model="row.category" density="compact" hide-details variant="plain" /></td>
            <td><AppTextField v-model="row.total"    density="compact" hide-details variant="plain" type="number" /></td>
            <td><AppTextField v-model="row.notes"    density="compact" hide-details variant="plain" /></td>
          </tr>
        </tbody>
      </VTable>
    </VCol>

    <!-- ٥. تقرير الكشف على السيارة -->
    <VCol cols="12">
      <VExpansionPanels variant="accordion" class="mt-2">
        <VExpansionPanel>
          <VExpansionPanelTitle>
            <VIcon icon="tabler-clipboard-check" class="me-2" color="warning" />
            تقرير الكشف على السيارة
          </VExpansionPanelTitle>
          <VExpansionPanelText>
            <p class="text-caption mb-3 text-medium-emphasis">ينبغي تدوين كل كلمة سالمة أو متضررة أو ناقصة بالصور المقابل لكل فقرة ولا يكتفى بوضع إشارة</p>

            <!-- Helper macro-like table via component call -->
            <template v-for="(section, sIdx) in [
              { title: 'الهيكل الخارجي', items: extItems },
              { title: 'الهيكل الداخلي', items: intItems },
              { title: 'الزجاج والمصابيح', items: glassItems },
              { title: 'الإطارات وملحقاتها', items: tireItems },
            ]" :key="sIdx">
              <div class="text-subtitle-2 font-weight-bold mt-4 mb-1">{{ section.title }}</div>
              <VTable density="compact" class="border rounded mb-2">
                <thead class="bg-var-theme-background">
                  <tr>
                    <th style="width:35%">البند</th>
                    <th style="width:30%">الحالة</th>
                    <th>تفاصيل الأضرار إن وجدت</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in section.items" :key="item.key">
                    <td class="text-caption">{{ item.label }}</td>
                    <td>
                      <AppSelect
                        :modelValue="vd.inspection[item.key]?.status"
                        @update:modelValue="v => vd.inspection[item.key].status = v"
                        :items="statusOptions"
                        density="compact"
                        hide-details
                        variant="plain"
                      />
                    </td>
                    <td>
                      <AppTextField
                        :modelValue="vd.inspection[item.key]?.damage"
                        @update:modelValue="v => vd.inspection[item.key].damage = v"
                        density="compact"
                        hide-details
                        variant="plain"
                      />
                    </td>
                  </tr>
                </tbody>
              </VTable>
            </template>

            <div class="text-subtitle-2 font-weight-bold mt-3 mb-1">ملاحظات أخرى</div>
            <AppTextarea v-model="vd.inspection.other_notes" rows="2" />
          </VExpansionPanelText>
        </VExpansionPanel>
      </VExpansionPanels>
    </VCol>
  </VRow>
</template>

<style scoped>
.q-item { padding: 10px 0; border-bottom: 1px solid rgba(var(--v-border-color), 0.15); }
.q-item:last-child { border-bottom: none; }
.q-label { font-size: 13px; font-weight: 600; margin-bottom: 4px; }
</style>
