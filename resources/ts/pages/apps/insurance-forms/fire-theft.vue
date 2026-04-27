<script setup lang="ts">
import { reactive, computed } from 'vue'
import { useRouter } from 'vue-router'

definePage({ meta: { action: 'create', subject: 'Policy' } })

const router = useRouter()

interface AssetRow { description: string; amount: string }

const form = reactive({
  // بيانات طالب التأمين
  policy_holder_name: '',
  commercial_name: '',
  permanent_address: '',
  profession: '',
  phone: '',
  period_from: '',
  period_to: '',

  // السؤال 1: موقع المحل
  shop_number: '',
  alley: '',
  area: '',
  street_district: '',

  // الأسئلة 2-17
  q2: '' as 'yes' | 'no' | '',
  q3: '' as 'yes' | 'no' | '',
  q3_details: '',
  q4: '',
  q5: '' as 'yes' | 'no' | '',
  q5_details: '',
  q6: '' as 'yes' | 'no' | '',
  q6_details: '',
  q7: '' as 'yes' | 'no' | '',
  q7_details: '',
  q8: '' as 'yes' | 'no' | '',
  q8_details: '',
  q9: '' as 'yes' | 'no' | '',
  q9_details: '',
  q10: '' as 'yes' | 'no' | '',
  q10_details: '',
  q11: '' as 'yes' | 'no' | '',
  q11_company: '',
  q11_policy_no: '',
  q12: '' as 'yes' | 'no' | '',
  q12_company: '',
  q13a: '' as 'yes' | 'no' | '',
  q13b: '' as 'yes' | 'no' | '',
  q13c: '' as 'yes' | 'no' | '',
  q13d: '' as 'yes' | 'no' | '',
  q14_details: '',
  r_flood: false,
  r_spontaneous: false,
  r_storms: false,
  r_disturbances: false,
  r_aircraft: false,
  r_earthquake: false,
  q15: '' as 'fire' | 'fire_theft' | '',
  q16: '',
  q17: '',

  // جدول الأصول
  buildings: [
    { description: 'البناء (عدا الأرض والأساس)', amount: '' },
    { description: 'التأسيسات الكهربائية', amount: '' },
    { description: 'أجهزة تكييف الهواء المركزية', amount: '' },
    { description: '', amount: '' },
  ] as AssetRow[],
  goods: [
    { description: 'بضائع تجارية جاهزة', amount: '' },
    { description: 'بضائع تحت الصنع', amount: '' },
    { description: 'مواد أولية', amount: '' },
    { description: 'أدوات احتياطية', amount: '' },
    { description: '', amount: '' },
  ] as AssetRow[],
  machines: Array.from({ length: 6 }, () => ({ description: '', amount: '' })) as AssetRow[],
  furniture: Array.from({ length: 6 }, () => ({ description: '', amount: '' })) as AssetRow[],
  other_assets: Array.from({ length: 4 }, () => ({ description: '', amount: '' })) as AssetRow[],
  total_written: '',

  // توقيعات
  applicant_name: '',
  applicant_date: '',
  agent_name: '',
  agent_date: '',
})

const YN = [{ title: 'نعم', value: 'yes' }, { title: 'لا', value: 'no' }]

const sumRows = (rows: AssetRow[]) => rows.reduce((s, r) => s + (parseFloat(r.amount) || 0), 0)
const totalAmount = computed(() =>
  sumRows(form.buildings) + sumRows(form.goods) + sumRows(form.machines) +
  sumRows(form.furniture) + sumRows(form.other_assets)
)
const fmt = (v: number) => new Intl.NumberFormat('ar-IQ', { maximumFractionDigits: 0 }).format(v || 0)
const yn = (v: string) => v === 'yes' ? 'نعم' : v === 'no' ? 'لا' : '............'
const line = (v: string) => v || '......................................................................'

const printForm = () => window.print()
</script>

<template>
  <section>
    <!-- ═══ SCREEN VIEW (hidden on print) ═══ -->
    <div class="no-print">
      <!-- Page Header -->
      <div class="d-flex align-center justify-space-between mb-6">
        <div class="d-flex align-center gap-3">
          <VBtn icon variant="tonal" color="secondary" size="small" @click="router.back()">
            <VIcon icon="tabler-arrow-right" />
          </VBtn>
          <div>
            <h4 class="text-h4 font-weight-bold">استمارة طلب التأمين من الحريق والسرقة</h4>
            <p class="text-body-2 text-medium-emphasis mb-0">للمحلات التجارية — شركة التأمين العراقية العامة</p>
          </div>
        </div>
        <VBtn color="primary" prepend-icon="tabler-printer" @click="printForm">
          طباعة الاستمارة
        </VBtn>
      </div>

      <VRow>
        <VCol cols="12" lg="8">

          <!-- 1. بيانات طالب التأمين -->
          <VCard class="mb-6">
            <VCardItem>
              <template #prepend><VAvatar color="primary" variant="tonal" rounded><VIcon icon="tabler-user" /></VAvatar></template>
              <VCardTitle>بيانات طالب التأمين</VCardTitle>
            </VCardItem>
            <VDivider />
            <VCardText>
              <VRow>
                <VCol cols="12" md="6">
                  <AppTextField v-model="form.policy_holder_name" label="اسم طالب التأمين" prepend-inner-icon="tabler-user" />
                </VCol>
                <VCol cols="12" md="6">
                  <AppTextField v-model="form.commercial_name" label="الاسم التجاري للمحل" prepend-inner-icon="tabler-building-store" />
                </VCol>
                <VCol cols="12" md="6">
                  <AppTextField v-model="form.permanent_address" label="العنوان الدائم لطالب التأمين" prepend-inner-icon="tabler-map-pin" />
                </VCol>
                <VCol cols="12" md="3">
                  <AppTextField v-model="form.profession" label="المهنة" prepend-inner-icon="tabler-briefcase" />
                </VCol>
                <VCol cols="12" md="3">
                  <AppTextField v-model="form.phone" label="رقم الهاتف" prepend-inner-icon="tabler-phone" />
                </VCol>
                <VCol cols="12" md="4">
                  <AppTextField v-model="form.period_from" label="مدة التأمين — من" type="date" prepend-inner-icon="tabler-calendar" />
                </VCol>
                <VCol cols="12" md="4">
                  <AppTextField v-model="form.period_to" label="إلى" type="date" prepend-inner-icon="tabler-calendar" />
                </VCol>
              </VRow>
            </VCardText>
          </VCard>

          <!-- 2. موقع المحل (السؤال 1) -->
          <VCard class="mb-6">
            <VCardItem>
              <template #prepend><VAvatar color="warning" variant="tonal" rounded><VIcon icon="tabler-map-pin" /></VAvatar></template>
              <VCardTitle>١- موقع ورقم المحل</VCardTitle>
            </VCardItem>
            <VDivider />
            <VCardText>
              <VRow>
                <VCol cols="12" md="3">
                  <AppTextField v-model="form.shop_number" label="رقم المحل" />
                </VCol>
                <VCol cols="12" md="3">
                  <AppTextField v-model="form.alley" label="الزقاق" />
                </VCol>
                <VCol cols="12" md="3">
                  <AppTextField v-model="form.area" label="المحلة" />
                </VCol>
                <VCol cols="12" md="3">
                  <AppTextField v-model="form.street_district" label="الشارع / المنطقة" />
                </VCol>
              </VRow>
            </VCardText>
          </VCard>

          <!-- 3. الأسئلة 2-17 -->
          <VCard class="mb-6">
            <VCardItem>
              <template #prepend><VAvatar color="info" variant="tonal" rounded><VIcon icon="tabler-list-check" /></VAvatar></template>
              <VCardTitle>الأسئلة التفصيلية</VCardTitle>
            </VCardItem>
            <VDivider />
            <VCardText>

              <!-- Q2 -->
              <div class="question-block">
                <p class="text-body-2 font-weight-bold mb-2">٢- هل أنت مالك الأموال المراد تأمينها؟</p>
                <VRadioGroup v-model="form.q2" inline>
                  <VRadio v-for="o in YN" :key="o.value" :label="o.title" :value="o.value" />
                </VRadioGroup>
              </div>

              <!-- Q3 -->
              <div class="question-block">
                <p class="text-body-2 font-weight-bold mb-2">٣- هل تمسك مجموعة أصولية من السجلات المحاسبية؟ أو هل تجري جرداً منتظماً للموجودات؟</p>
                <VRadioGroup v-model="form.q3" inline class="mb-2">
                  <VRadio v-for="o in YN" :key="o.value" :label="o.title" :value="o.value" />
                </VRadioGroup>
                <AppTextField v-if="form.q3" v-model="form.q3_details" label="بخلاف ذلك بين التفاصيل" />
              </div>

              <!-- Q4 -->
              <div class="question-block">
                <p class="text-body-2 font-weight-bold mb-2">٤- أين تحفظ الأموال الثمينة كالمجوهرات والذهب والساعات؟</p>
                <AppTextField v-model="form.q4" label="أذكر المكان" />
              </div>

              <!-- Q5 -->
              <div class="question-block">
                <p class="text-body-2 font-weight-bold mb-2">٥- هل مبلغ التأمين المطلوب يمثل القيمة الحقيقية للأموال؟</p>
                <VRadioGroup v-model="form.q5" inline class="mb-2">
                  <VRadio v-for="o in YN" :key="o.value" :label="o.title" :value="o.value" />
                </VRadioGroup>
                <AppTextField v-if="form.q5 === 'no'" v-model="form.q5_details" label="بين القيمة الحقيقية" />
              </div>

              <!-- Q6 -->
              <div class="question-block">
                <p class="text-body-2 font-weight-bold mb-2">٦- هل تترك المحل مقفلاً بدون مزاولة عمل فيه لأي مدة؟</p>
                <VRadioGroup v-model="form.q6" inline class="mb-2">
                  <VRadio v-for="o in YN" :key="o.value" :label="o.title" :value="o.value" />
                </VRadioGroup>
                <AppTextField v-if="form.q6 === 'yes'" v-model="form.q6_details" label="بين المدة" />
              </div>

              <!-- Q7 -->
              <div class="question-block">
                <p class="text-body-2 font-weight-bold mb-2">٧- هل تمتلك حراسة على المحل؟ من طبيعة هذه الحراسة؟</p>
                <VRadioGroup v-model="form.q7" inline class="mb-2">
                  <VRadio v-for="o in YN" :key="o.value" :label="o.title" :value="o.value" />
                </VRadioGroup>
                <AppTextField v-if="form.q7 === 'yes'" v-model="form.q7_details" label="طبيعة الحراسة" />
              </div>

              <!-- Q8 -->
              <div class="question-block">
                <p class="text-body-2 font-weight-bold mb-2">٨- هل وقع لك حادث حريق أو سرقة في هذا المحل أو سواه؟</p>
                <VRadioGroup v-model="form.q8" inline class="mb-2">
                  <VRadio v-for="o in YN" :key="o.value" :label="o.title" :value="o.value" />
                </VRadioGroup>
                <AppTextField v-if="form.q8 === 'yes'" v-model="form.q8_details" label="التفاصيل" />
              </div>

              <!-- Q9 -->
              <div class="question-block">
                <p class="text-body-2 font-weight-bold mb-2">٩- هل وقع حادث حريق أو سرقة للمحلات المجاورة والقريبة من محلك؟</p>
                <VRadioGroup v-model="form.q9" inline class="mb-2">
                  <VRadio v-for="o in YN" :key="o.value" :label="o.title" :value="o.value" />
                </VRadioGroup>
                <AppTextField v-if="form.q9 === 'yes'" v-model="form.q9_details" label="التفاصيل" />
              </div>

              <!-- Q10 -->
              <div class="question-block">
                <p class="text-body-2 font-weight-bold mb-2">١٠- هل تخزّن في المحل وقوداً أو مواد كيمياوية خطرة؟</p>
                <VRadioGroup v-model="form.q10" inline class="mb-2">
                  <VRadio v-for="o in YN" :key="o.value" :label="o.title" :value="o.value" />
                </VRadioGroup>
                <AppTextField v-if="form.q10 === 'yes'" v-model="form.q10_details" label="أنواعها وكمياتها والغرض منها" />
              </div>

              <!-- Q11 -->
              <div class="question-block">
                <p class="text-body-2 font-weight-bold mb-2">١١- هل كانت الأموال محل تأمين عليها سابقاً؟</p>
                <VRadioGroup v-model="form.q11" inline class="mb-2">
                  <VRadio v-for="o in YN" :key="o.value" :label="o.title" :value="o.value" />
                </VRadioGroup>
                <VRow v-if="form.q11 === 'yes'">
                  <VCol cols="6"><AppTextField v-model="form.q11_company" label="اسم الجهة" /></VCol>
                  <VCol cols="6"><AppTextField v-model="form.q11_policy_no" label="رقم الوثيقة" /></VCol>
                </VRow>
              </div>

              <!-- Q12 -->
              <div class="question-block">
                <p class="text-body-2 font-weight-bold mb-2">١٢- هل تمتلك تأمين سارٍ المفعول حالياً على نفس الأموال لدى شركة أخرى؟</p>
                <VRadioGroup v-model="form.q12" inline class="mb-2">
                  <VRadio v-for="o in YN" :key="o.value" :label="o.title" :value="o.value" />
                </VRadioGroup>
                <AppTextField v-if="form.q12 === 'yes'" v-model="form.q12_company" label="اسم الشركة" />
              </div>

              <!-- Q13 -->
              <div class="question-block">
                <p class="text-body-2 font-weight-bold mb-3">١٣- شركات أخرى أو هذه الشركة سبق وإن:</p>
                <VTable density="compact" class="mb-3">
                  <tbody>
                    <tr>
                      <td class="text-body-2">أ- رفضت إجراء التأمين على هذه الأموال؟</td>
                      <td style="width:200px">
                        <VRadioGroup v-model="form.q13a" inline hide-details>
                          <VRadio v-for="o in YN" :key="o.value" :label="o.title" :value="o.value" />
                        </VRadioGroup>
                      </td>
                    </tr>
                    <tr>
                      <td class="text-body-2">ب- رفضت تجديد وثيقة تأمين صادرة لك؟</td>
                      <td>
                        <VRadioGroup v-model="form.q13b" inline hide-details>
                          <VRadio v-for="o in YN" :key="o.value" :label="o.title" :value="o.value" />
                        </VRadioGroup>
                      </td>
                    </tr>
                    <tr>
                      <td class="text-body-2">ج- ألغت وثيقة صادرة لك؟</td>
                      <td>
                        <VRadioGroup v-model="form.q13c" inline hide-details>
                          <VRadio v-for="o in YN" :key="o.value" :label="o.title" :value="o.value" />
                        </VRadioGroup>
                      </td>
                    </tr>
                    <tr>
                      <td class="text-body-2">د- جددت وثيقة تأمين صادرة بشروط خاصة؟</td>
                      <td>
                        <VRadioGroup v-model="form.q13d" inline hide-details>
                          <VRadio v-for="o in YN" :key="o.value" :label="o.title" :value="o.value" />
                        </VRadioGroup>
                      </td>
                    </tr>
                  </tbody>
                </VTable>
              </div>

              <!-- Q14 -->
              <div class="question-block">
                <p class="text-body-2 font-weight-bold mb-2">١٤- الأخطار الإضافية المراد إضافتها:</p>
                <VRow class="mb-2">
                  <VCol cols="6" sm="4"><VCheckbox v-model="form.r_flood" label="(أ) الفيضان والأمطار" hide-details /></VCol>
                  <VCol cols="6" sm="4"><VCheckbox v-model="form.r_spontaneous" label="(ب) الاحتراق الذاتي" hide-details /></VCol>
                  <VCol cols="6" sm="4"><VCheckbox v-model="form.r_storms" label="(ج) الزوابع والعواصف" hide-details /></VCol>
                  <VCol cols="6" sm="4"><VCheckbox v-model="form.r_disturbances" label="(د) الشغب والاضطرابات" hide-details /></VCol>
                  <VCol cols="6" sm="4"><VCheckbox v-model="form.r_aircraft" label="(هـ) سقوط الطائرات" hide-details /></VCol>
                  <VCol cols="6" sm="4"><VCheckbox v-model="form.r_earthquake" label="(و) الزلازل" hide-details /></VCol>
                </VRow>
                <AppTextField v-model="form.q14_details" label="تفاصيل إضافية (إن وجدت)" />
              </div>

              <!-- Q15 -->
              <div class="question-block">
                <p class="text-body-2 font-weight-bold mb-2">١٥- هل ترغب بتأمين المحل من الحريق فقط أم من الحريق والسرقة؟</p>
                <VRadioGroup v-model="form.q15" inline>
                  <VRadio label="من الحريق فقط" value="fire" />
                  <VRadio label="من الحريق والسرقة" value="fire_theft" />
                </VRadioGroup>
              </div>

              <!-- Q16 & Q17 -->
              <VRow>
                <VCol cols="12" md="6">
                  <div class="question-block">
                    <p class="text-body-2 font-weight-bold mb-2">١٦- مصدر الأموال</p>
                    <AppTextField v-model="form.q16" label="مصدر الأموال" />
                  </div>
                </VCol>
                <VCol cols="12" md="6">
                  <div class="question-block">
                    <p class="text-body-2 font-weight-bold mb-2">١٧- الدخل الشهري</p>
                    <AppTextField v-model="form.q17" label="الدخل الشهري" type="number" />
                  </div>
                </VCol>
              </VRow>

            </VCardText>
          </VCard>

          <!-- 4. جدول الأصول -->
          <VCard class="mb-6">
            <VCardItem>
              <template #prepend><VAvatar color="success" variant="tonal" rounded><VIcon icon="tabler-table" /></VAvatar></template>
              <VCardTitle>جدول بتفاصيل الأموال المطلوب التأمين عليها</VCardTitle>
              <template #append>
                <span class="text-body-2 font-weight-bold text-primary">
                  الإجمالي: {{ fmt(totalAmount) }} د.ع
                </span>
              </template>
            </VCardItem>
            <VDivider />
            <VCardText>

              <!-- أ - البنايات -->
              <p class="text-subtitle-2 font-weight-bold text-warning mb-3">أ - البنايات</p>
              <VTable density="compact" class="mb-6">
                <thead>
                  <tr><th>التفاصيل</th><th style="width:200px">مبلغ التأمين المطلوب (د.ع)</th></tr>
                </thead>
                <tbody>
                  <tr v-for="(row, i) in form.buildings" :key="i">
                    <td>
                      <AppTextField v-model="row.description" density="compact" hide-details :placeholder="i < 3 ? row.description : 'تفصيل إضافي'" />
                    </td>
                    <td><AppTextField v-model="row.amount" density="compact" hide-details type="number" placeholder="0" /></td>
                  </tr>
                </tbody>
              </VTable>

              <!-- ب - الضائع (البضائع) -->
              <p class="text-subtitle-2 font-weight-bold text-warning mb-3">ب - الضائع (البضائع)</p>
              <VTable density="compact" class="mb-6">
                <thead>
                  <tr><th>التفاصيل</th><th style="width:200px">مبلغ التأمين المطلوب (د.ع)</th></tr>
                </thead>
                <tbody>
                  <tr v-for="(row, i) in form.goods" :key="i">
                    <td><AppTextField v-model="row.description" density="compact" hide-details :placeholder="i < 4 ? row.description : 'تفصيل إضافي'" /></td>
                    <td><AppTextField v-model="row.amount" density="compact" hide-details type="number" placeholder="0" /></td>
                  </tr>
                </tbody>
              </VTable>

              <!-- ج - الآلات والمكائن -->
              <p class="text-subtitle-2 font-weight-bold text-warning mb-3">ج - الآلات ومكائن وملحقاتها (مع أرقامها وأنواعها)</p>
              <VTable density="compact" class="mb-6">
                <thead>
                  <tr><th>الوصف / النوع / الرقم</th><th style="width:200px">مبلغ التأمين المطلوب (د.ع)</th></tr>
                </thead>
                <tbody>
                  <tr v-for="(row, i) in form.machines" :key="i">
                    <td><AppTextField v-model="row.description" density="compact" hide-details :placeholder="'آلة ' + (i+1)" /></td>
                    <td><AppTextField v-model="row.amount" density="compact" hide-details type="number" placeholder="0" /></td>
                  </tr>
                </tbody>
              </VTable>

              <!-- د - الأثاث -->
              <p class="text-subtitle-2 font-weight-bold text-warning mb-3">د - الأثاث</p>
              <VTable density="compact" class="mb-6">
                <thead>
                  <tr><th>التفاصيل</th><th style="width:200px">مبلغ التأمين المطلوب (د.ع)</th></tr>
                </thead>
                <tbody>
                  <tr v-for="(row, i) in form.furniture" :key="i">
                    <td><AppTextField v-model="row.description" density="compact" hide-details :placeholder="'قطعة ' + (i+1)" /></td>
                    <td><AppTextField v-model="row.amount" density="compact" hide-details type="number" placeholder="0" /></td>
                  </tr>
                </tbody>
              </VTable>

              <!-- هـ - أموال أخرى -->
              <p class="text-subtitle-2 font-weight-bold text-warning mb-3">هـ - أموال أخرى غير ما ذُكر أعلاه</p>
              <VTable density="compact" class="mb-4">
                <thead>
                  <tr><th>التفاصيل</th><th style="width:200px">مبلغ التأمين المطلوب (د.ع)</th></tr>
                </thead>
                <tbody>
                  <tr v-for="(row, i) in form.other_assets" :key="i">
                    <td><AppTextField v-model="row.description" density="compact" hide-details :placeholder="'بيان آخر ' + (i+1)" /></td>
                    <td><AppTextField v-model="row.amount" density="compact" hide-details type="number" placeholder="0" /></td>
                  </tr>
                </tbody>
              </VTable>

              <VDivider class="mb-4" />
              <VRow align="center">
                <VCol cols="12" md="8">
                  <AppTextField v-model="form.total_written" label="مجموع مبلغ التأمين كتابةً (فقط)" />
                </VCol>
                <VCol cols="12" md="4" class="text-end">
                  <div class="text-caption text-medium-emphasis">الإجمالي الرقمي</div>
                  <div class="text-h6 font-weight-black text-primary">{{ fmt(totalAmount) }} د.ع</div>
                </VCol>
              </VRow>

            </VCardText>
          </VCard>

          <!-- 5. التوقيعات -->
          <VCard class="mb-6">
            <VCardItem>
              <template #prepend><VAvatar color="secondary" variant="tonal" rounded><VIcon icon="tabler-writing" /></VAvatar></template>
              <VCardTitle>التوقيعات والتصريح</VCardTitle>
            </VCardItem>
            <VDivider />
            <VCardText>
              <VAlert type="info" variant="tonal" class="mb-4" density="compact">
                أصرح / نصرح بأن التفاصيل المذكورة أعلاه صحيحة وحقيقية، وأوافق على أن يكون هذا الطلب أساساً للتعاقد بيني وبين شركة التأمين العراقية العامة وفق شروط وثيقة الحريق / أو السرقة المعتمدة بها في الشركة.
              </VAlert>
              <VRow>
                <VCol cols="12" md="5">
                  <AppTextField v-model="form.applicant_name" label="الاسم (طالب التأمين)" prepend-inner-icon="tabler-user" />
                </VCol>
                <VCol cols="12" md="3">
                  <AppTextField v-model="form.applicant_date" label="التاريخ" type="date" />
                </VCol>
              </VRow>
              <VRow class="mt-2">
                <VCol cols="12" md="5">
                  <AppTextField v-model="form.agent_name" label="اسم المنتج" prepend-inner-icon="tabler-user-check" />
                </VCol>
                <VCol cols="12" md="3">
                  <AppTextField v-model="form.agent_date" label="التاريخ" type="date" />
                </VCol>
              </VRow>
            </VCardText>
          </VCard>

        </VCol>

        <!-- Sidebar -->
        <VCol cols="12" lg="4">
          <VCard class="position-sticky" style="top:80px">
            <VCardItem>
              <template #prepend><VAvatar color="primary" variant="tonal" rounded><VIcon icon="tabler-eye" /></VAvatar></template>
              <VCardTitle>ملخص الاستمارة</VCardTitle>
            </VCardItem>
            <VDivider />
            <VCardText>
              <div class="d-flex flex-column gap-2 mb-4">
                <div class="d-flex justify-space-between">
                  <span class="text-body-2 text-medium-emphasis">اسم طالب التأمين</span>
                  <span class="text-body-2 font-weight-medium">{{ form.policy_holder_name || '—' }}</span>
                </div>
                <div class="d-flex justify-space-between">
                  <span class="text-body-2 text-medium-emphasis">الاسم التجاري</span>
                  <span class="text-body-2 font-weight-medium">{{ form.commercial_name || '—' }}</span>
                </div>
                <div class="d-flex justify-space-between">
                  <span class="text-body-2 text-medium-emphasis">نوع التغطية</span>
                  <VChip :color="form.q15 === 'fire_theft' ? 'error' : 'warning'" size="small" label>
                    {{ form.q15 === 'fire_theft' ? 'حريق + سرقة' : form.q15 === 'fire' ? 'حريق فقط' : '—' }}
                  </VChip>
                </div>
                <div class="d-flex justify-space-between">
                  <span class="text-body-2 text-medium-emphasis">مدة التأمين</span>
                  <span class="text-body-2">{{ form.period_from || '—' }} ← {{ form.period_to || '—' }}</span>
                </div>
              </div>
              <VDivider class="mb-4" />
              <div class="d-flex flex-column gap-1 mb-4">
                <div class="d-flex justify-space-between">
                  <span class="text-body-2 text-medium-emphasis">البنايات</span>
                  <span class="text-body-2">{{ fmt(sumRows(form.buildings)) }}</span>
                </div>
                <div class="d-flex justify-space-between">
                  <span class="text-body-2 text-medium-emphasis">البضائع</span>
                  <span class="text-body-2">{{ fmt(sumRows(form.goods)) }}</span>
                </div>
                <div class="d-flex justify-space-between">
                  <span class="text-body-2 text-medium-emphasis">الآلات والمكائن</span>
                  <span class="text-body-2">{{ fmt(sumRows(form.machines)) }}</span>
                </div>
                <div class="d-flex justify-space-between">
                  <span class="text-body-2 text-medium-emphasis">الأثاث</span>
                  <span class="text-body-2">{{ fmt(sumRows(form.furniture)) }}</span>
                </div>
                <div class="d-flex justify-space-between">
                  <span class="text-body-2 text-medium-emphasis">أموال أخرى</span>
                  <span class="text-body-2">{{ fmt(sumRows(form.other_assets)) }}</span>
                </div>
                <VDivider class="my-1" />
                <div class="d-flex justify-space-between">
                  <span class="text-body-2 font-weight-bold">الإجمالي الكلي</span>
                  <span class="text-body-1 font-weight-black text-primary">{{ fmt(totalAmount) }} د.ع</span>
                </div>
              </div>
            </VCardText>
            <VDivider />
            <VCardActions class="pa-4">
              <VBtn block color="primary" size="large" prepend-icon="tabler-printer" @click="printForm">
                طباعة الاستمارة
              </VBtn>
            </VCardActions>
          </VCard>
        </VCol>
      </VRow>
    </div><!-- END no-print -->

    <!-- ═══ PRINT VIEW (visible only on print) ═══ -->
    <div class="print-only" dir="rtl">

      <!-- ════ صفحة 1: الاستمارة الرئيسية ════ -->
      <div class="print-page">

        <!-- رأس الصفحة -->
        <table class="p-header-table" width="100%">
          <tr>
            <td style="width:40%; text-align:right; vertical-align:top; font-size:11px; padding:4px 0">
              أن استلام شركة التأمين لهذه الأستمارة<br>
              لا يعني قبولها أجراء التأمين
            </td>
            <td style="width:20%; text-align:center; vertical-align:middle">
              <div style="font-size:14px; font-weight:bold; border:2px solid #000; padding:6px 12px; display:inline-block">
                شركة التأمين العراقية العامة<br>
                <span style="font-size:11px">IRAQI GENERAL INSURANCE COMPANY</span>
              </div>
            </td>
            <td style="width:40%; text-align:left; vertical-align:top; font-size:11px; padding:4px 0">
              بغداد ساحة عقبة بن نافع<br>
              شارع خالد بن الوليد<br>
              هاتف ٧١٩٢٦٥٦
            </td>
          </tr>
        </table>

        <div class="p-title">
          (استمارة طلب التأمين من الحريق والسرقة للمحلات التجارية)
        </div>

        <!-- بيانات أساسية -->
        <table class="p-info-table" width="100%">
          <tr>
            <td style="width:30%">اسم طالب التأمين :</td>
            <td style="width:70%" class="p-dotted">{{ form.policy_holder_name }}</td>
          </tr>
          <tr>
            <td>الاسم التجاري للمحل :</td>
            <td class="p-dotted">{{ form.commercial_name }}</td>
          </tr>
          <tr>
            <td>العنوان الدائم لطالب التأمين :</td>
            <td style="display:flex; gap:16px">
              <span>{{ form.permanent_address }}</span>
              <span style="margin-right:auto">المهنة : {{ form.profession }}</span>
              <span>رقم الهاتف : {{ form.phone }}</span>
            </td>
          </tr>
          <tr>
            <td>مدة التأمين :</td>
            <td>من {{ form.period_from || '..../....../20' }} &nbsp;&nbsp; إلى {{ form.period_to || '..../....../20' }}</td>
          </tr>
        </table>

        <!-- الأسئلة -->
        <div class="p-q">
          <p><strong>١- موقع ورقم المحل الذي توجد فيه الأموال المطلوب التأمين عليها:</strong><br>
          محل {{ form.shop_number || '......' }} / زقاق {{ form.alley || '......' }} / محلة {{ form.area || '.......' }} / {{ form.street_district || '.....................' }}</p>
        </div>

        <div class="p-q">
          <p><strong>٢- هل أنت مالك الأموال المراد تأمينها؟</strong> &nbsp;&nbsp; {{ yn(form.q2) }}</p>
        </div>

        <div class="p-q">
          <p><strong>٣- هل تمسك مجموعة أصولية من السجلات المحاسبية؟ أو هل تجري جرداً منتظماً للموجودات؟</strong></p>
          <p>{{ yn(form.q3) }} &nbsp;&nbsp; بخلاف ذلك بين التفاصيل: {{ line(form.q3_details) }}</p>
        </div>

        <div class="p-q">
          <p><strong>٤- أين تحفظ الأموال الثمينة كالمجوهرات والذهب والساعات؟</strong></p>
          <p>{{ line(form.q4) }}</p>
        </div>

        <div class="p-q">
          <p><strong>٥- هل مبلغ التأمين المطلوب يمثل القيمة الحقيقية للأموال؟</strong> &nbsp;&nbsp; {{ yn(form.q5) }}</p>
          <p v-if="form.q5_details">{{ line(form.q5_details) }}</p>
        </div>

        <div class="p-q">
          <p><strong>٦- هل تترك المحل مقفلاً بدون مزاولة عمل فيه لأي مدة؟</strong> &nbsp;&nbsp; {{ yn(form.q6) }} &nbsp;&nbsp;
          <span v-if="form.q6 === 'yes'">المدة: {{ line(form.q6_details) }}</span></p>
        </div>

        <div class="p-q">
          <p><strong>٧- هل تمتلك حراسة على المحل؟</strong> &nbsp;&nbsp; {{ yn(form.q7) }} &nbsp;&nbsp;
          <span v-if="form.q7 === 'yes'">طبيعتها: {{ line(form.q7_details) }}</span></p>
        </div>

        <div class="p-q">
          <p><strong>٨- هل وقع لك حادث حريق أو سرقة في هذا المحل أو سواه؟</strong> &nbsp;&nbsp; {{ yn(form.q8) }}</p>
          <p v-if="form.q8 === 'yes'">{{ line(form.q8_details) }}</p>
        </div>

        <div class="p-q">
          <p><strong>٩- هل وقع حادث حريق أو سرقة للمحلات المجاورة والقريبة من محلك؟</strong> &nbsp;&nbsp; {{ yn(form.q9) }}</p>
          <p v-if="form.q9 === 'yes'">{{ line(form.q9_details) }}</p>
        </div>

        <div class="p-q">
          <p><strong>١٠- هل تخزّن في المحل وقوداً أو مواد كيمياوية خطرة؟</strong> &nbsp;&nbsp; {{ yn(form.q10) }}</p>
          <p v-if="form.q10 === 'yes'">أنواعها وكمياتها: {{ line(form.q10_details) }}</p>
        </div>

        <div class="p-q">
          <p><strong>١١- هل كانت الأموال محل تأمين عليها سابقاً؟</strong> &nbsp;&nbsp; {{ yn(form.q11) }}
          <span v-if="form.q11 === 'yes'"> &nbsp; الجهة: {{ line(form.q11_company) }} &nbsp; رقم الوثيقة: {{ line(form.q11_policy_no) }}</span></p>
        </div>

        <div class="p-q">
          <p><strong>١٢- هل تمتلك تأمين سارٍ المفعول حالياً على نفس الأموال لدى شركة أخرى؟</strong> &nbsp;&nbsp; {{ yn(form.q12) }}
          <span v-if="form.q12 === 'yes'"> &nbsp; الشركة: {{ line(form.q12_company) }}</span></p>
        </div>

        <div class="p-q">
          <p><strong>١٣- شركات أخرى أو هذه الشركة سبق وإن:</strong></p>
          <p>أ- رفضت إجراء التأمين على هذه الأموال؟ {{ yn(form.q13a) }} &nbsp;&nbsp;
             ب- رفضت تجديد وثيقة تأمين؟ {{ yn(form.q13b) }}</p>
          <p>ج- ألغت وثيقة صادرة لك؟ {{ yn(form.q13c) }} &nbsp;&nbsp;
             د- جددت وثيقة بشروط خاصة؟ {{ yn(form.q13d) }}</p>
        </div>

        <div class="p-q">
          <p><strong>١٤- الأخطار الإضافية:</strong>
            (أ) الفيضان والأمطار [{{ form.r_flood ? '✓' : ' ' }}] &nbsp;
            (ب) الاحتراق الذاتي [{{ form.r_spontaneous ? '✓' : ' ' }}] &nbsp;
            (ج) الزوابع والعواصف [{{ form.r_storms ? '✓' : ' ' }}] &nbsp;
            (د) الشغب والاضطرابات [{{ form.r_disturbances ? '✓' : ' ' }}] &nbsp;
            (هـ) سقوط الطائرات [{{ form.r_aircraft ? '✓' : ' ' }}] &nbsp;
            (و) الزلازل [{{ form.r_earthquake ? '✓' : ' ' }}]
          </p>
          <p v-if="form.q14_details">{{ line(form.q14_details) }}</p>
        </div>

        <div class="p-q">
          <p><strong>١٥- نوع التغطية:</strong>
            {{ form.q15 === 'fire' ? 'من الحريق فقط' : form.q15 === 'fire_theft' ? 'من الحريق والسرقة' : '......................................' }}
          </p>
        </div>

        <div class="p-q">
          <p><strong>١٦- مصدر الأموال:</strong> {{ line(form.q16) }} &nbsp;&nbsp; <strong>١٧- الدخل الشهري:</strong> {{ line(form.q17) }}</p>
        </div>

        <!-- إقرار وتوقيعات -->
        <div class="p-declaration">
          <p>أني / نحن ................................................ أصرح / نصرح بأن التفاصيل المذكورة أعلاه صحيحة وحقيقية وأوافق / نوافق على أن يكون هذا الطلب أساساً للتعاقد بيني / بيننا وبين شركة التأمين العراقية العراقية وفق شروط وثيقة الحريق / أو السرقة المعتمدة بها في الشركة وأتعهد بتسديد قسط التأمين إذا قبلت الشركة هذا الطلب وأصدرت الوثيقة.</p>
        </div>

        <table class="p-sig-table" width="100%">
          <tr>
            <td style="width:50%">
              الاسم : {{ line(form.applicant_name) }}<br><br>
              التوقيع : .......................................................<br><br>
              التاريخ : {{ form.applicant_date || '..../....../20' }}
            </td>
            <td style="width:50%; text-align:right">
              اسم المنتج : {{ line(form.agent_name) }}<br><br>
              توقيعه : .......................................................<br><br>
              التاريخ : {{ form.agent_date || '..../....../20' }}
            </td>
          </tr>
        </table>

      </div><!-- END print-page 1 -->

      <!-- ════ صفحة 2: جدول الأصول ════ -->
      <div class="print-page page-break">

        <div class="p-title" style="font-size:16px; margin-bottom:8px">
          جدول بتفاصيل الأموال المطلوب التأمين عليها
        </div>
        <p style="font-size:10px; text-align:center; margin-bottom:8px">
          يرجى تنظيم جدول خارجي في حالة عدم كفاية الجدول أدناه مع الإشارة إلى اسم طالب التأمين والمحل
        </p>

        <table class="p-assets-table" width="100%" border="1" cellpadding="4" cellspacing="0">
          <thead>
            <tr>
              <th style="width:70%">التفاصيل</th>
              <th style="width:30%">مبلغ التأمين المطلوب</th>
            </tr>
          </thead>
          <tbody>
            <!-- أ - البنايات -->
            <tr>
              <td colspan="2" class="p-section-header">أ - البنايات:</td>
            </tr>
            <tr v-for="(row, i) in form.buildings" :key="'b'+i">
              <td style="padding-right:24px">{{ i+1 }}- {{ row.description || '...................................................' }}</td>
              <td>{{ row.amount ? fmt(parseFloat(row.amount)) : '.........................' }}</td>
            </tr>

            <!-- ب - الضائع -->
            <tr>
              <td colspan="2" class="p-section-header">ب - الضائع:</td>
            </tr>
            <tr v-for="(row, i) in form.goods" :key="'g'+i">
              <td style="padding-right:24px">{{ i+1 }}- {{ row.description || '...................................................' }}</td>
              <td>{{ row.amount ? fmt(parseFloat(row.amount)) : '.........................' }}</td>
            </tr>

            <!-- ج - الآلات والمكائن -->
            <tr>
              <td colspan="2" class="p-section-header">ج - الآلات ومكائن وملحقاتها: (تُذكر أرقامها وأنواعها)</td>
            </tr>
            <tr v-for="(row, i) in form.machines" :key="'m'+i">
              <td style="padding-right:24px">{{ i+1 }}- {{ row.description || '...................................................' }}</td>
              <td>{{ row.amount ? fmt(parseFloat(row.amount)) : '.........................' }}</td>
            </tr>

            <!-- د - الأثاث -->
            <tr>
              <td colspan="2" class="p-section-header">د - الأثاث:</td>
            </tr>
            <tr v-for="(row, i) in form.furniture" :key="'f'+i">
              <td style="padding-right:24px">{{ i+1 }}- {{ row.description || '...................................................' }}</td>
              <td>{{ row.amount ? fmt(parseFloat(row.amount)) : '.........................' }}</td>
            </tr>

            <!-- هـ - أموال أخرى -->
            <tr>
              <td colspan="2" class="p-section-header">هـ - أموال أخرى غير ما ذُكر أعلاه:</td>
            </tr>
            <tr v-for="(row, i) in form.other_assets" :key="'o'+i">
              <td style="padding-right:24px">{{ i+1 }}- {{ row.description || '...................................................' }}</td>
              <td>{{ row.amount ? fmt(parseFloat(row.amount)) : '.........................' }}</td>
            </tr>

            <!-- الإجمالي -->
            <tr>
              <td style="text-align:right; font-weight:bold; padding:6px">
                مجموع مبلغ التأمين كتابة (فقط): {{ form.total_written || '.................................................................' }} لاغيرها
              </td>
              <td style="font-weight:bold; font-size:13px">{{ fmt(totalAmount) }}</td>
            </tr>
          </tbody>
        </table>

        <!-- توقيعات الجدول -->
        <table class="p-sig-table" width="100%" style="margin-top:24px">
          <tr>
            <td style="width:50%">
              اسم طالب التأمين : {{ line(form.policy_holder_name) }}<br><br>
              توقيعه : .......................................................<br><br>
              التاريخ : {{ form.applicant_date || '..../....../20' }}
            </td>
            <td style="width:50%; text-align:right">
              اسم المنتج : {{ line(form.agent_name) }}<br><br>
              توقيعه : .......................................................<br><br>
              التاريخ : {{ form.agent_date || '..../....../20' }}
            </td>
          </tr>
        </table>

      </div><!-- END print-page 2 -->

    </div><!-- END print-only -->

  </section>
</template>

<style scoped>
/* ─── Screen styles ─────────────────────────────────────────────────────────── */
.question-block {
  padding: 12px 0;
  border-bottom: 1px solid rgba(var(--v-border-color), 0.12);
}
.question-block:last-child { border-bottom: none; }

/* ─── Print: hide screen, show print view ───────────────────────────────────── */
@media screen { .print-only { display: none !important; } }
@media print  { .no-print   { display: none !important; } }

/* ─── Print layout ──────────────────────────────────────────────────────────── */
@media print {
  .print-only { display: block !important; }

  .print-page {
    font-family: 'Noto Naskh Arabic', 'Arial', sans-serif;
    font-size: 12px;
    direction: rtl;
    padding: 12mm;
    color: #000;
  }

  .page-break { page-break-before: always; }

  .p-title {
    text-align: center;
    font-size: 15px;
    font-weight: bold;
    border: 2px solid #000;
    padding: 6px;
    margin: 10px 0;
  }

  .p-header-table { margin-bottom: 8px; }

  .p-info-table { border-collapse: collapse; margin-bottom: 10px; width: 100%; }
  .p-info-table td { padding: 3px 6px; vertical-align: top; }
  .p-dotted { border-bottom: 1px dotted #000; }

  .p-q { margin-bottom: 6px; line-height: 1.8; }
  .p-q p { margin: 2px 0; }

  .p-declaration {
    border-top: 1px solid #000;
    border-bottom: 1px solid #000;
    padding: 6px 0;
    margin: 10px 0;
    font-size: 11px;
    line-height: 1.8;
  }

  .p-sig-table { margin-top: 16px; }
  .p-sig-table td { padding: 4px 8px; line-height: 2; }

  .p-assets-table { border-collapse: collapse; font-size: 11px; }
  .p-assets-table th { background: #f0f0f0; font-weight: bold; padding: 4px 8px; text-align: right; }
  .p-assets-table td { padding: 3px 8px; }
  .p-section-header { background: #f5f5f5; font-weight: bold; padding: 4px 8px; }
}
</style>
