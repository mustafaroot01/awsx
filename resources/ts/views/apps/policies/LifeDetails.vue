<script setup lang="ts">
interface Props { formData: any }
const props = defineProps<Props>()

const maritalOptions = [
  { title: 'متزوج', value: 'married' },
  { title: 'أعزب', value: 'single' },
  { title: 'أرمل', value: 'widowed' },
  { title: 'مطلق', value: 'divorced' },
]

const idTypes = [
  { title: 'هوية وطنية', value: 'national_id' },
  { title: 'جواز سفر', value: 'passport' },
]

const paymentOptions = [
  { title: 'سنوي', value: 'annual' },
  { title: 'نصف سنوي', value: 'semi' },
  { title: 'ربع سنوي', value: 'quarter' },
]

const YN = [{ title: 'نعم', value: 'yes' }, { title: 'لا', value: 'no' }]

const q6Items = [
  { key: 'q6a', label: 'أ) بتولد / صرع / تشنج، أو علة في الرئة أو الجهاز التنفسي' },
  { key: 'q6b', label: 'ب) أمراض الكبد / الدم / الجهاز الهضمي / الزيادة الدموية' },
  { key: 'q6c', label: 'ج) أمراض الروماتيزم / المفاصل أو علة أخرى' },
  { key: 'q6d', label: 'د) أمراض الكلى أو المسالك البولية أو التناسلي' },
  { key: 'q6e', label: 'هـ) أمراض القلب / الأوعية الدموية / ارتفاع أو انخفاض الضغط' },
  { key: 'q6f', label: 'و) داء السكر أو سكر البول' },
  { key: 'q6g', label: 'ز) التهاب المفاصل / العظام / عضلات المفاصل' },
  { key: 'q6h', label: 'ح) علة في البصر أو السمع' },
  { key: 'q6i', label: 'ط) سرطان / ملاريا / زهري / أمراض جلدية' },
  { key: 'q6_corona', label: 'إصابة بمرض كورونا' },
]

if (!props.formData.lifeDetails.health_q) {
  props.formData.lifeDetails.health_q = {
    q1: '', q1d: '',
    q2: '',
    q3a: '', q3ad: '',
    q3b: '', q3bd: '',
    q4a: '', q4ad: '',
    q4b: '', q4bd: '',
    q5: '',
    q6a: '', q6b: '', q6c: '', q6d: '', q6e: '', q6f: '', q6g: '', q6h: '', q6i: '', q6_corona: '',
    q6_details: '',
    q7a: '', q7ad: '',
    q7b: '', q7bd: '',
    q7c: '', q7cd: '',
    q8a: '', q8ad: '',
    q8b: '',
    q8c: '', q8cd: '',
    q9_weight: '', q9_changed: '', q9_gain: '', q9_loss: '', q9_reason: '',
    fam_spouse_age: '', fam_spouse_health: '', fam_spouse_d_age: '', fam_spouse_d_cause: '',
    fam_father_age: '', fam_father_health: '', fam_father_d_age: '', fam_father_d_cause: '',
    fam_mother_age: '', fam_mother_health: '', fam_mother_d_age: '', fam_mother_d_cause: '',
    sib_alive: '', sib_complaint: '', sib_dead_cause: '',
  }
}
const hq = props.formData.lifeDetails.health_q
</script>

<template>
  <VRow>
    <VCol cols="12">
      <div class="d-flex align-center gap-2 mb-4">
        <VIcon icon="tabler-heart-rate-monitor" color="error" />
        <h6 class="text-h6 mb-0">استمارة طلب تأمين على الحياة</h6>
      </div>
    </VCol>

    <!-- 0. بيانات الوثيقة (دفع + مدة) -->
    <VCol cols="12">
      <VCard variant="outlined" class="pa-4">
        <div class="text-overline mb-3 text-primary">بيانات الوثيقة</div>
        <VRow>
          <VCol cols="12" md="4">
            <AppSelect v-model="props.formData.lifeDetails.paymentCycle" :items="paymentOptions" label="طريقة دفع الأقساط *" />
          </VCol>
          <VCol cols="12" md="4">
            <AppTextField v-model="props.formData.lifeDetails.durationYears" label="مدة التأمين (سنة) *" type="number" />
          </VCol>
          <VCol cols="12" md="4">
            <AppTextField v-model="props.formData.lifeDetails.accidentFee" label="رسوم الحوادث الإضافية (د.ع)" type="number" />
          </VCol>
        </VRow>
      </VCard>
    </VCol>

    <!-- 1. بيانات المؤمن عليه التفصيلية -->
    <VCol cols="12">
      <VCard variant="outlined" class="pa-4">
        <div class="text-overline mb-3 text-error">بيانات المؤمن عليه</div>
        <VRow>
          <VCol cols="12" md="6">
            <AppSelect v-model="props.formData.lifeDetails.marital_status" :items="maritalOptions" label="الحالة الاجتماعية *" />
          </VCol>
          <VCol cols="12" md="4">
            <AppSelect v-model="props.formData.lifeDetails.id_type" :items="idTypes" label="نوع المستند" />
          </VCol>
          <VCol cols="12" md="4">
            <AppTextField v-model="props.formData.lifeDetails.id_card_no" label="رقم المستند الثبوتي *" />
          </VCol>
          <VCol cols="12" md="4">
            <AppTextField v-model="props.formData.lifeDetails.issue_authority_date" label="جهة وتاريخ الإصدار" />
          </VCol>
          <VCol cols="12" md="4">
            <AppTextField v-model="props.formData.lifeDetails.id_expiry" label="تاريخ انتهاء المستند" type="date" />
          </VCol>
          <VCol cols="12" md="4">
            <AppTextField v-model="props.formData.lifeDetails.spouse_name" label="اسم الزوج / الزوجة" />
          </VCol>
          <VCol cols="12" md="4">
            <AppTextField v-model="props.formData.lifeDetails.work_address" label="عنوان العمل" />
          </VCol>
          <VCol cols="12" md="6">
            <AppTextField v-model="props.formData.lifeDetails.home_address_detail" label="عنوان السكن التفصيلي *" />
          </VCol>
          <VCol cols="12" md="3">
            <AppTextField v-model="props.formData.lifeDetails.governorate" label="المحافظة" />
          </VCol>
          <VCol cols="12" md="3">
            <AppTextField v-model="props.formData.lifeDetails.landmark" label="أقرب نقطة دالة" />
          </VCol>
        </VRow>
      </VCard>
    </VCol>

    <!-- 2. القياسات البدنية -->
    <VCol cols="12">
      <VCard variant="tonal" color="secondary" class="pa-4">
        <div class="text-subtitle-2 mb-3">القياسات البدنية</div>
        <VRow>
          <VCol cols="12" md="4">
            <AppTextField v-model="props.formData.lifeDetails.height_cm" label="الطول (سم)" type="number" />
          </VCol>
          <VCol cols="12" md="4">
            <AppTextField v-model="props.formData.lifeDetails.weight_kg" label="الوزن (كغم)" type="number" />
          </VCol>
          <VCol cols="12" md="4">
            <AppTextField v-model="props.formData.lifeDetails.weight_change_last_year" label="تغير الوزن في السنة الأخيرة" />
          </VCol>
        </VRow>
      </VCard>
    </VCol>

    <!-- 3. المستفيدين (جدول مطابق للورقة) -->
    <VCol cols="12">
      <div class="text-subtitle-1 mb-2 font-weight-bold">المستفيدين وتوزيع الحصص</div>
      <VTable density="compact" class="border">
        <thead>
          <tr>
            <th>الاسم الرباعي واللقب</th>
            <th>صلة القرابة</th>
            <th>حصة البقاء (%)</th>
            <th>حصة الوفاة (%)</th>
            <th />
          </tr>
        </thead>
        <tbody>
          <tr v-for="(ben, index) in props.formData.beneficiaries" :key="index">
            <td><AppTextField v-model="ben.name_quad" variant="plain" density="compact" hide-details /></td>
            <td><AppTextField v-model="ben.relationship" variant="plain" density="compact" hide-details /></td>
            <td><AppTextField v-model="ben.share_survival" type="number" variant="plain" density="compact" hide-details /></td>
            <td><AppTextField v-model="ben.share_death" type="number" variant="plain" density="compact" hide-details /></td>
            <td>
              <VBtn icon="tabler-trash" size="x-small" color="error" variant="text" @click="props.formData.beneficiaries.splice(index, 1)" />
            </td>
          </tr>
        </tbody>
      </VTable>
      <VBtn variant="text" size="small" class="mt-2" prepend-icon="tabler-plus" @click="props.formData.beneficiaries.push({})">إضافة مستفيد</VBtn>
    </VCol>

    <!-- 4. بيان الحالة الصحية الكامل -->
    <VCol cols="12">
      <VCard variant="outlined" class="pa-4 mt-2 border-error">
        <h6 class="text-subtitle-1 mb-4 text-error">استفسار بيان الحالة الصحية</h6>

        <!-- س1 -->
        <div class="q-item">
          <div class="q-label">١- هل سبق واستلمت تقاعداً أو تعويضاً عن العجز من أي مكان؟</div>
          <VRadioGroup v-model="hq.q1" inline hide-details class="mb-1"><VRadio v-for="o in YN" :key="o.value" :label="o.title" :value="o.value"/></VRadioGroup>
          <AppTextField v-if="hq.q1==='yes'" v-model="hq.q1d" label="التفاصيل" density="compact" class="mt-1"/>
        </div>

        <!-- س2 -->
        <div class="q-item">
          <div class="q-label">٢- لأي حد تعاطيت سيجارة أو المسكرات الكحولية؟</div>
          <AppTextField v-model="hq.q2" label="التفاصيل" density="compact"/>
        </div>

        <!-- س3 -->
        <div class="q-item">
          <div class="q-label">٣ أ) هل تعاطيت أي مادة مخدرة؟</div>
          <VRadioGroup v-model="hq.q3a" inline hide-details class="mb-1"><VRadio v-for="o in YN" :key="o.value" :label="o.title" :value="o.value"/></VRadioGroup>
          <AppTextField v-if="hq.q3a==='yes'" v-model="hq.q3ad" label="التفاصيل" density="compact" class="mt-1 mb-2"/>
          <div class="q-label mt-1">٣ ب) هل عولجت من الكحوليات؟</div>
          <VRadioGroup v-model="hq.q3b" inline hide-details class="mb-1"><VRadio v-for="o in YN" :key="o.value" :label="o.title" :value="o.value"/></VRadioGroup>
          <AppTextField v-if="hq.q3b==='yes'" v-model="hq.q3bd" label="التفاصيل" density="compact" class="mt-1"/>
        </div>

        <!-- س4 -->
        <div class="q-item">
          <div class="q-label">٤ أ) هل كانت لك علاقة أو سكنت مع مصابين بالإيدز؟</div>
          <VRadioGroup v-model="hq.q4a" inline hide-details class="mb-1"><VRadio v-for="o in YN" :key="o.value" :label="o.title" :value="o.value"/></VRadioGroup>
          <AppTextField v-if="hq.q4a==='yes'" v-model="hq.q4ad" label="التفاصيل" density="compact" class="mt-1 mb-2"/>
          <div class="q-label mt-1">٤ ب) هل أصيبت عائلتك بالإيدز أو الجنون أو المخدرات؟</div>
          <VRadioGroup v-model="hq.q4b" inline hide-details class="mb-1"><VRadio v-for="o in YN" :key="o.value" :label="o.title" :value="o.value"/></VRadioGroup>
          <AppTextField v-if="hq.q4b==='yes'" v-model="hq.q4bd" label="التفاصيل" density="compact" class="mt-1"/>
        </div>

        <!-- س5 -->
        <div class="q-item">
          <div class="q-label">٥- هل أنت بصحة جيدة وهل تمتعت بصحة جيدة اعتيادياً؟</div>
          <VRadioGroup v-model="hq.q5" inline hide-details><VRadio v-for="o in YN" :key="o.value" :label="o.title" :value="o.value"/></VRadioGroup>
        </div>

        <!-- س6 -->
        <div class="q-item">
          <div class="q-label">٦- هل أصبت بأي من الأمراض التالية؟</div>
          <VTable density="compact" class="mt-2 border">
            <tbody>
              <tr v-for="item in q6Items" :key="item.key">
                <td class="text-caption" style="width:75%">{{ item.label }}</td>
                <td>
                  <VRadioGroup :modelValue="hq[item.key]" @update:modelValue="v => hq[item.key] = v" inline hide-details>
                    <VRadio v-for="o in YN" :key="o.value" :label="o.title" :value="o.value"/>
                  </VRadioGroup>
                </td>
              </tr>
            </tbody>
          </VTable>
          <AppTextField v-model="hq.q6_details" label="تفاصيل إضافية" density="compact" class="mt-2"/>
        </div>

        <!-- س7 -->
        <div class="q-item">
          <div class="q-label">٧ أ) فحوصات تشخيصية (أشعة / تخطيط قلب / دم)؟</div>
          <VRadioGroup v-model="hq.q7a" inline hide-details class="mb-1"><VRadio v-for="o in YN" :key="o.value" :label="o.title" :value="o.value"/></VRadioGroup>
          <AppTextField v-if="hq.q7a==='yes'" v-model="hq.q7ad" label="التفاصيل" density="compact" class="mt-1 mb-2"/>
          <div class="q-label mt-1">٧ ب) معالجة أو كشف طبي في مستشفى؟</div>
          <VRadioGroup v-model="hq.q7b" inline hide-details class="mb-1"><VRadio v-for="o in YN" :key="o.value" :label="o.title" :value="o.value"/></VRadioGroup>
          <AppTextField v-if="hq.q7b==='yes'" v-model="hq.q7bd" label="التفاصيل" density="compact" class="mt-1 mb-2"/>
          <div class="q-label mt-1">٧ ج) حادثة أو عملية جراحية غير مذكورة؟</div>
          <VRadioGroup v-model="hq.q7c" inline hide-details class="mb-1"><VRadio v-for="o in YN" :key="o.value" :label="o.title" :value="o.value"/></VRadioGroup>
          <AppTextField v-if="hq.q7c==='yes'" v-model="hq.q7cd" label="التفاصيل" density="compact" class="mt-1"/>
        </div>

        <!-- س8: نساء فقط -->
        <div class="q-item" v-if="props.formData.lifeDetails.gender === 'female'">
          <div class="q-label">٨- للمرأة فقط:</div>
          <div class="text-caption mt-1">أ) هل أنت حامل وكم مضى على ذلك؟</div>
          <VRadioGroup v-model="hq.q8a" inline hide-details class="mb-1"><VRadio v-for="o in YN" :key="o.value" :label="o.title" :value="o.value"/></VRadioGroup>
          <AppTextField v-if="hq.q8a==='yes'" v-model="hq.q8ad" label="عدد الأشهر" density="compact" class="mt-1 mb-2"/>
          <div class="text-caption mt-1">ب) هل حدث إجهاض أو عملية قيصرية؟</div>
          <VRadioGroup v-model="hq.q8b" inline hide-details class="mb-1"><VRadio v-for="o in YN" :key="o.value" :label="o.title" :value="o.value"/></VRadioGroup>
          <div class="text-caption mt-1">ج) أمراض في الجهاز التناسلي؟</div>
          <VRadioGroup v-model="hq.q8c" inline hide-details class="mb-1"><VRadio v-for="o in YN" :key="o.value" :label="o.title" :value="o.value"/></VRadioGroup>
          <AppTextField v-if="hq.q8c==='yes'" v-model="hq.q8cd" label="التفاصيل" density="compact" class="mt-1"/>
        </div>

        <!-- س9: الوزن -->
        <div class="q-item">
          <div class="q-label">٩- الوزن</div>
          <VRow class="mt-1">
            <VCol cols="6" md="3"><AppTextField v-model="hq.q9_weight" label="الوزن (كغم)" type="number" density="compact"/></VCol>
            <VCol cols="6" md="3">
              <VRadioGroup v-model="hq.q9_changed" inline><template #label><span class="text-caption">تغيّر؟</span></template>
                <VRadio v-for="o in YN" :key="o.value" :label="o.title" :value="o.value"/>
              </VRadioGroup>
            </VCol>
            <VCol cols="6" md="2" v-if="hq.q9_changed==='yes'"><AppTextField v-model="hq.q9_gain" label="زيادة كغم" density="compact"/></VCol>
            <VCol cols="6" md="2" v-if="hq.q9_changed==='yes'"><AppTextField v-model="hq.q9_loss" label="نقص كغم" density="compact"/></VCol>
            <VCol cols="12" v-if="hq.q9_changed==='yes'"><AppTextField v-model="hq.q9_reason" label="السبب" density="compact"/></VCol>
          </VRow>
        </div>

        <!-- س10: جدول الأسرة -->
        <div class="q-item">
          <div class="q-label mb-2">١٠- معلومات الأسرة (الأحياء والأموات)</div>
          <VTable density="compact" class="border">
            <thead>
              <tr><th>الفرد</th><th>العمر</th><th>الحالة الصحية</th><th>عمر الوفاة</th><th>سبب الوفاة</th></tr>
            </thead>
            <tbody>
              <tr v-for="(row, key) in {
                الزوجة: { age: 'fam_spouse_age', health: 'fam_spouse_health', d_age: 'fam_spouse_d_age', d_cause: 'fam_spouse_d_cause' },
                الأب:   { age: 'fam_father_age', health: 'fam_father_health', d_age: 'fam_father_d_age', d_cause: 'fam_father_d_cause' },
                الأم:   { age: 'fam_mother_age', health: 'fam_mother_health', d_age: 'fam_mother_d_age', d_cause: 'fam_mother_d_cause' },
              }" :key="key">
                <td class="font-weight-bold text-caption">{{ key }}</td>
                <td><AppTextField :modelValue="hq[row.age]" @update:modelValue="v => hq[row.age] = v" density="compact" hide-details/></td>
                <td><AppTextField :modelValue="hq[row.health]" @update:modelValue="v => hq[row.health] = v" density="compact" hide-details/></td>
                <td><AppTextField :modelValue="hq[row.d_age]" @update:modelValue="v => hq[row.d_age] = v" density="compact" hide-details/></td>
                <td><AppTextField :modelValue="hq[row.d_cause]" @update:modelValue="v => hq[row.d_cause] = v" density="compact" hide-details/></td>
              </tr>
            </tbody>
          </VTable>
          <VRow class="mt-2">
            <VCol cols="6" md="3"><AppTextField v-model="hq.sib_alive" label="عدد الإخوة الأحياء" type="number" density="compact"/></VCol>
            <VCol cols="6" md="5"><AppTextField v-model="hq.sib_complaint" label="هل يشكو أحدهم؟" density="compact"/></VCol>
            <VCol cols="12" md="4"><AppTextField v-model="hq.sib_dead_cause" label="أسباب وفاة الأخوة الأموات" density="compact"/></VCol>
          </VRow>
        </div>

      </VCard>
    </VCol>
  </VRow>
</template>

<style scoped>
.border-error { border-color: rgba(var(--v-theme-error), 0.5) !important; }
.q-item { padding: 10px 0; border-bottom: 1px solid rgba(var(--v-border-color), 0.15); }
.q-item:last-child { border-bottom: none; }
.q-label { font-size: 13px; font-weight: 600; margin-bottom: 4px; }
</style>
