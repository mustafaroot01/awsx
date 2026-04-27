<script setup lang="ts">
import { reactive } from 'vue'
import { useRouter } from 'vue-router'
definePage({ meta: { action: 'read', subject: 'Auth' } })
const router = useRouter()

interface BRow { name: string; age: string; share: string }
interface FRow { age: string; health: string; d_age: string; d_cause: string }

const f = reactive({
  policy_no:'', agent_name:'', branch_name:'', branch_code:'',
  holder_ar:'', insured_ar:'', insured_en:'',
  nationality:'', gender:'' as 'male'|'female'|'',
  dob_d:'', dob_m:'', dob_y:'', age:'',
  id_type:'' as 'national_id'|'passport'|'', id_no:'',
  id_iss_d:'', id_iss_m:'', id_iss_y:'',
  id_exp_d:'', id_exp_m:'', id_exp_y:'',
  marital:'' as 'single'|'married'|'widowed'|'divorced'|'separated'|'',
  spouse_name:'', spouse_rel:'',
  ben_type:'' as 'wife'|'children'|'sharia'|'',
  ben_life:  [{name:'',age:'',share:''},{name:'',age:'',share:''},{name:'',age:'',share:''}] as BRow[],
  ben_death: [{name:'',age:'',share:''},{name:'',age:'',share:''},{name:'',age:'',share:''}] as BRow[],
  start_d:'', start_m:'', start_y:'', duration:'20',
  payment:'' as 'annual'|'semi'|'quarter'|'',
  ins_type:'', riders:'', sum:'', currency:'' as 'iqd'|'usd'|'other'|'', currency_o:'',
  employer_addr:'', employer_phone:'', job:'',
  fees_written:'', fees_num:'', receipt_no:'', receipt_date:'',
  home_addr:'', governorate:'', district:'', landmark:'', house_no:'',
  // KYC
  kyc_name_ar:'', kyc_name_en:'', kyc_phone:'', kyc_email:'',
  kyc_addr:'', kyc_res:'' as 'own'|'rent'|'', kyc_landmark:'',
  kyc_abroad:'' as 'yes'|'no'|'',
  kyc_emp:'' as 'gov'|'private'|'retired'|'military'|'employer'|'political'|'student'|'other'|'',
  kyc_emp_name:'', kyc_activity:'', kyc_title:'',
  kyc_passport:'', kyc_passport_iss:'',
  kyc_dob:'', kyc_birth:'',
  kyc_other_nat:'' as 'yes'|'no'|'', kyc_other_nat_name:'',
  kyc_spouse:'', kyc_marital:'', kyc_edu:'',
  income_src:[] as string[], income_extra:'',
  monthly_income:'' as '<1m'|'1-5m'|'5-10m'|'10-25m'|'>25m'|'',
  // أسئلة طبية
  q1:'' as 'yes'|'no'|'', q1d:'',
  q2:'',
  q3a:'' as 'yes'|'no'|'', q3ad:'',
  q3b:'' as 'yes'|'no'|'', q3bd:'',
  q4a:'' as 'yes'|'no'|'', q4ad:'',
  q4b:'' as 'yes'|'no'|'', q4bd:'',
  q5:'' as 'yes'|'no'|'',
  q6a:'' as 'yes'|'no'|'', q6b:'' as 'yes'|'no'|'', q6c:'' as 'yes'|'no'|'',
  q6d:'' as 'yes'|'no'|'', q6e:'' as 'yes'|'no'|'', q6f:'' as 'yes'|'no'|'',
  q6g:'' as 'yes'|'no'|'', q6h:'' as 'yes'|'no'|'', q6i:'' as 'yes'|'no'|'',
  q6_corona:'' as 'yes'|'no'|'', q6_details:'',
  q7a:'' as 'yes'|'no'|'', q7ad:'',
  q7b:'' as 'yes'|'no'|'', q7bd:'',
  q7c:'' as 'yes'|'no'|'', q7cd:'',
  q8a:'' as 'yes'|'no'|'', q8ad:'',
  q8b:'' as 'yes'|'no'|'',
  q8c:'' as 'yes'|'no'|'', q8cd:'',
  q9_weight:'', q9_changed:'' as 'yes'|'no'|'', q9_gain:'', q9_loss:'', q9_reason:'',
  fam_spouse: {age:'',health:'',d_age:'',d_cause:''} as FRow,
  fam_father: {age:'',health:'',d_age:'',d_cause:''} as FRow,
  fam_mother: {age:'',health:'',d_age:'',d_cause:''} as FRow,
  sib_alive:'', sib_complaint:'',
  sib_dead: [{n:'',cause:''},{n:'',cause:''},{n:'',cause:''},{n:'',cause:''}] as {n:string;cause:string}[],
  sign_city:'', sign_d:'', sign_m:'', sign_y:'',
  holder_full:'', insured_full:'', guardian:'', witness:'',
})

const YN = [{title:'نعم',value:'yes'},{title:'لا',value:'no'}]
const D  = (v:string,n=30)=>v||'.'.repeat(n)
const yn = (v:string)=>v==='yes'?'نعم':v==='no'?'لا':'......'
const maritalLabel: Record<string,string> = {single:'أعزب',married:'متزوج',widowed:'أرمل',divorced:'مطلق',separated:'مفارق'}
const payLabel: Record<string,string> = {annual:'سنوي',semi:'نصف سنوي',quarter:'ربع سنوي'}
const empLabel: Record<string,string> = {gov:'حكومي',private:'قطاع خاص',retired:'متقاعد',military:'عسكري',employer:'رب عمل',political:'منصب سياسي',student:'طالب',other:'أخرى'}
const incomeLabel: Record<string,string> = {'<1m':'أقل من مليون','1-5m':'1-5 مليون','5-10m':'5-10 مليون','10-25m':'10-25 مليون','>25m':'أكثر من 25 مليون'}
const printForm = ()=>window.print()

const q6Items = [
  {key:'q6a', label:'أ) أمراض الرئة أو الجهاز التنفسي / الصرع / الجنون'},
  {key:'q6b', label:'ب) أمراض الكبد أو الدم أو الجهاز الهضمي'},
  {key:'q6c', label:'ج) أمراض الروماتيزم أو المفاصل'},
  {key:'q6d', label:'د) أمراض الكلى أو المسالك البولية أو التناسلي'},
  {key:'q6e', label:'هـ) أمراض القلب أو الأوعية الدموية أو ضغط الدم'},
  {key:'q6f', label:'و) داء السكر أو سكر البول'},
  {key:'q6g', label:'ز) التهاب المفاصل أو العضلات أو العظام'},
  {key:'q6h', label:'ح) علة في البصر أو السمع'},
  {key:'q6i', label:'ط) السرطان / الملاريا / الزهري / أمراض الجلد'},
  {key:'q6_corona', label:'كورونا'},
]
</script>

<template>
  <section>
    <!-- ══ SCREEN ══ -->
    <div class="no-print">
      <div class="d-flex align-center justify-space-between mb-6">
        <div class="d-flex align-center gap-3">
          <VBtn icon variant="tonal" color="secondary" size="small" @click="router.back()"><VIcon icon="tabler-arrow-right"/></VBtn>
          <div>
            <h4 class="text-h4 font-weight-bold">استمارة طلب تأمين على الحياة</h4>
            <p class="text-body-2 text-medium-emphasis mb-0">شركة التأمين العراقية العامة</p>
          </div>
        </div>
        <VBtn color="primary" prepend-icon="tabler-printer" @click="printForm">طباعة</VBtn>
      </div>

      <VRow>
        <VCol cols="12" lg="8">

          <!-- رأس الوثيقة -->
          <VCard class="mb-5"><VCardItem><template #prepend><VAvatar color="secondary" variant="tonal" rounded><VIcon icon="tabler-file-certificate"/></VAvatar></template><VCardTitle>بيانات الوثيقة</VCardTitle></VCardItem><VDivider/><VCardText><VRow>
            <VCol cols="6" md="3"><AppTextField v-model="f.policy_no" label="رقم الوثيقة"/></VCol>
            <VCol cols="6" md="3"><AppTextField v-model="f.agent_name" label="اسم المندوب"/></VCol>
            <VCol cols="6" md="3"><AppTextField v-model="f.branch_name" label="الفرع / الجهة"/></VCol>
            <VCol cols="6" md="3"><AppTextField v-model="f.branch_code" label="الرمز"/></VCol>
          </VRow></VCardText></VCard>

          <!-- المؤمن له -->
          <VCard class="mb-5"><VCardItem><template #prepend><VAvatar color="primary" variant="tonal" rounded><VIcon icon="tabler-user"/></VAvatar></template><VCardTitle>بيانات المؤمن له / المؤمن عليه</VCardTitle></VCardItem><VDivider/><VCardText><VRow>
            <VCol cols="12" md="6"><AppTextField v-model="f.holder_ar" label="طالب التأمين (الاسم الرباعي)"/></VCol>
            <VCol cols="12" md="6"><AppTextField v-model="f.insured_ar" label="المؤمن عليه (الاسم الرباعي)"/></VCol>
            <VCol cols="12" md="6"><AppTextField v-model="f.insured_en" label="الاسم بالإنجليزية"/></VCol>
            <VCol cols="12" md="3"><AppTextField v-model="f.nationality" label="الجنسية"/></VCol>
            <VCol cols="12" md="3"><AppSelect v-model="f.gender" :items="[{title:'ذكر',value:'male'},{title:'أنثى',value:'female'}]" label="الجنس"/></VCol>
            <VCol cols="12"><p class="text-subtitle-2 font-weight-bold mt-2 mb-1">تاريخ الميلاد</p></VCol>
            <VCol cols="4" md="2"><AppTextField v-model="f.dob_d" label="اليوم"/></VCol>
            <VCol cols="4" md="2"><AppTextField v-model="f.dob_m" label="الشهر"/></VCol>
            <VCol cols="4" md="3"><AppTextField v-model="f.dob_y" label="السنة"/></VCol>
            <VCol cols="12" md="2"><AppTextField v-model="f.age" label="العمر" type="number"/></VCol>
            <VCol cols="12"><VDivider/><p class="text-subtitle-2 font-weight-bold mt-2 mb-1">المستند الثبوتي</p></VCol>
            <VCol cols="12" md="4"><AppSelect v-model="f.id_type" :items="[{title:'هوية وطنية',value:'national_id'},{title:'جواز سفر',value:'passport'}]" label="نوع المستند"/></VCol>
            <VCol cols="12" md="4"><AppTextField v-model="f.id_no" label="رقم المستند"/></VCol>
            <VCol cols="12"><p class="text-caption text-medium-emphasis">تاريخ الإصدار</p></VCol>
            <VCol cols="4" md="2"><AppTextField v-model="f.id_iss_d" label="اليوم"/></VCol>
            <VCol cols="4" md="2"><AppTextField v-model="f.id_iss_m" label="الشهر"/></VCol>
            <VCol cols="4" md="2"><AppTextField v-model="f.id_iss_y" label="السنة"/></VCol>
            <VCol cols="12"><p class="text-caption text-medium-emphasis">تاريخ الانتهاء</p></VCol>
            <VCol cols="4" md="2"><AppTextField v-model="f.id_exp_d" label="اليوم"/></VCol>
            <VCol cols="4" md="2"><AppTextField v-model="f.id_exp_m" label="الشهر"/></VCol>
            <VCol cols="4" md="2"><AppTextField v-model="f.id_exp_y" label="السنة"/></VCol>
            <VCol cols="12"><VDivider/></VCol>
            <VCol cols="12" md="4"><AppSelect v-model="f.marital" :items="[{title:'أعزب',value:'single'},{title:'متزوج',value:'married'},{title:'أرمل',value:'widowed'},{title:'مطلق',value:'divorced'},{title:'مفارق',value:'separated'}]" label="الحالة الاجتماعية"/></VCol>
            <VCol cols="12" md="4"><AppTextField v-model="f.spouse_name" label="اسم الزوج / الزوجة"/></VCol>
            <VCol cols="12" md="4"><AppTextField v-model="f.spouse_rel" label="صلة القرابة"/></VCol>
          </VRow></VCardText></VCard>

          <!-- المستفيدون -->
          <VCard class="mb-5"><VCardItem><template #prepend><VAvatar color="info" variant="tonal" rounded><VIcon icon="tabler-users"/></VAvatar></template><VCardTitle>المستفيدون من التأمين</VCardTitle></VCardItem><VDivider/><VCardText>
            <AppSelect v-model="f.ben_type" class="mb-4" :items="[{title:'الزوجة',value:'wife'},{title:'الأولاد',value:'children'},{title:'حسب القسام الشرعي',value:'sharia'}]" label="نوع المستفيد"/>
            <p class="text-subtitle-2 font-weight-bold mb-2">في حالة وفاة المؤمن قبل انتهاء المدة</p>
            <VTable density="compact" class="mb-4"><thead><tr><th>#</th><th>الاسم الرباعي واللقب</th><th>العمر</th><th>الحصة</th></tr></thead><tbody>
              <tr v-for="(r,i) in f.ben_life" :key="i"><td>{{i+1}}</td><td><AppTextField v-model="r.name" density="compact" hide-details/></td><td><AppTextField v-model="r.age" density="compact" hide-details style="width:70px"/></td><td><AppTextField v-model="r.share" density="compact" hide-details style="width:80px"/></td></tr>
            </tbody></VTable>
            <p class="text-subtitle-2 font-weight-bold mb-2">في حالة وفاة المؤمن عليه قبل انتهاء المدة</p>
            <VTable density="compact"><thead><tr><th>#</th><th>الاسم الرباعي واللقب</th><th>العمر</th><th>الحصة</th></tr></thead><tbody>
              <tr v-for="(r,i) in f.ben_death" :key="i"><td>{{i+1}}</td><td><AppTextField v-model="r.name" density="compact" hide-details/></td><td><AppTextField v-model="r.age" density="compact" hide-details style="width:70px"/></td><td><AppTextField v-model="r.share" density="compact" hide-details style="width:80px"/></td></tr>
            </tbody></VTable>
          </VCardText></VCard>

          <!-- الوثيقة والأقساط -->
          <VCard class="mb-5"><VCardItem><template #prepend><VAvatar color="warning" variant="tonal" rounded><VIcon icon="tabler-file-invoice"/></VAvatar></template><VCardTitle>بيانات الوثيقة والأقساط</VCardTitle></VCardItem><VDivider/><VCardText><VRow>
            <VCol cols="12"><p class="text-subtitle-2 font-weight-bold mb-1">ابتداء التأمين</p></VCol>
            <VCol cols="4" md="2"><AppTextField v-model="f.start_d" label="اليوم"/></VCol>
            <VCol cols="4" md="2"><AppTextField v-model="f.start_m" label="الشهر"/></VCol>
            <VCol cols="4" md="2"><AppTextField v-model="f.start_y" label="السنة"/></VCol>
            <VCol cols="6" md="3"><AppTextField v-model="f.duration" label="مدة التأمين (سنة)" type="number"/></VCol>
            <VCol cols="6" md="3"><AppSelect v-model="f.payment" :items="[{title:'سنوي',value:'annual'},{title:'نصف سنوي',value:'semi'},{title:'ربع سنوي',value:'quarter'}]" label="طريقة دفع الأقساط"/></VCol>
            <VCol cols="12" md="6"><AppTextField v-model="f.ins_type" label="نوع التأمين"/></VCol>
            <VCol cols="12" md="6"><AppTextField v-model="f.riders" label="الملاحق الإضافية"/></VCol>
            <VCol cols="12" md="4"><AppTextField v-model="f.sum" label="مبلغ التأمين" type="number"/></VCol>
            <VCol cols="12" md="4"><AppSelect v-model="f.currency" :items="[{title:'دينار عراقي',value:'iqd'},{title:'دولار أمريكي',value:'usd'},{title:'أخرى',value:'other'}]" label="العملة"/></VCol>
            <VCol v-if="f.currency==='other'" cols="12" md="4"><AppTextField v-model="f.currency_o" label="العملة الأخرى"/></VCol>
            <VCol cols="12" md="5"><AppTextField v-model="f.fees_written" label="المصاريف الإدارية كتابةً"/></VCol>
            <VCol cols="12" md="2"><AppTextField v-model="f.fees_num" label="الأرقام" type="number"/></VCol>
            <VCol cols="12" md="2"><AppTextField v-model="f.receipt_no" label="رقم الوصل"/></VCol>
            <VCol cols="12" md="3"><AppTextField v-model="f.receipt_date" label="تاريخ الوصل" type="date"/></VCol>
          </VRow></VCardText></VCard>

          <!-- الوظيفة والسكن -->
          <VCard class="mb-5"><VCardItem><template #prepend><VAvatar color="success" variant="tonal" rounded><VIcon icon="tabler-briefcase"/></VAvatar></template><VCardTitle>المعلومات الوظيفية وبيانات السكن</VCardTitle></VCardItem><VDivider/><VCardText><VRow>
            <VCol cols="12" md="6"><AppTextField v-model="f.employer_addr" label="عنوان محل العمل"/></VCol>
            <VCol cols="12" md="3"><AppTextField v-model="f.employer_phone" label="هاتف العمل"/></VCol>
            <VCol cols="12" md="3"><AppTextField v-model="f.job" label="الوظيفة"/></VCol>
            <VCol cols="12"><VDivider/><p class="text-subtitle-2 font-weight-bold mt-2 mb-1">بيانات السكن</p></VCol>
            <VCol cols="12" md="6"><AppTextField v-model="f.home_addr" label="عنوان السكن"/></VCol>
            <VCol cols="12" md="3"><AppTextField v-model="f.governorate" label="المحافظة"/></VCol>
            <VCol cols="12" md="3"><AppTextField v-model="f.district" label="المحلة"/></VCol>
            <VCol cols="12" md="5"><AppTextField v-model="f.landmark" label="أقرب نقطة دالة"/></VCol>
            <VCol cols="12" md="3"><AppTextField v-model="f.house_no" label="رقم الدار"/></VCol>
          </VRow></VCardText></VCard>

          <!-- KYC -->
          <VCard class="mb-5"><VCardItem><template #prepend><VAvatar color="error" variant="tonal" rounded><VIcon icon="tabler-shield-check"/></VAvatar></template><VCardTitle>استمارة التعهد — بيانات المستفيد (AML/KYC)</VCardTitle></VCardItem><VDivider/><VCardText><VRow>
            <VCol cols="12" md="6"><AppTextField v-model="f.kyc_name_ar" label="اسم المستفيد (عربي)"/></VCol>
            <VCol cols="12" md="6"><AppTextField v-model="f.kyc_name_en" label="الاسم (إنجليزي)"/></VCol>
            <VCol cols="12" md="4"><AppTextField v-model="f.kyc_phone" label="أرقام الاتصال"/></VCol>
            <VCol cols="12" md="4"><AppTextField v-model="f.kyc_email" label="البريد الإلكتروني"/></VCol>
            <VCol cols="12" md="6"><AppTextField v-model="f.kyc_addr" label="عنوان السكن"/></VCol>
            <VCol cols="12" md="3"><AppSelect v-model="f.kyc_res" :items="[{title:'ملك',value:'own'},{title:'إيجار',value:'rent'}]" label="نوع السكن"/></VCol>
            <VCol cols="12" md="5"><AppTextField v-model="f.kyc_landmark" label="أقرب نقطة دالة"/></VCol>
            <VCol cols="12" md="4">
              <VRadioGroup v-model="f.kyc_abroad" inline><template #label><span class="text-body-2 me-2">مقيم في بلد آخر؟</span></template><VRadio v-for="o in YN" :key="o.value" :label="o.title" :value="o.value"/></VRadioGroup>
            </VCol>
            <VCol cols="12" md="6"><AppSelect v-model="f.kyc_emp" label="طبيعة العمل" :items="[{title:'موظف حكومي',value:'gov'},{title:'موظف قطاع خاص',value:'private'},{title:'متقاعد',value:'retired'},{title:'عسكري',value:'military'},{title:'رب عمل',value:'employer'},{title:'صاحب منصب سياسي',value:'political'},{title:'طالب',value:'student'},{title:'أخرى',value:'other'}]"/></VCol>
            <VCol cols="12" md="3"><AppTextField v-model="f.kyc_emp_name" label="اسم المؤسسة"/></VCol>
            <VCol cols="12" md="3"><AppTextField v-model="f.kyc_title" label="المسمى الوظيفي"/></VCol>
            <VCol cols="12" md="4"><AppTextField v-model="f.kyc_passport" label="رقم جواز السفر"/></VCol>
            <VCol cols="12" md="4"><AppTextField v-model="f.kyc_passport_iss" label="جهة/تاريخ إصدار الجواز"/></VCol>
            <VCol cols="12" md="3"><AppTextField v-model="f.kyc_dob" label="تاريخ الميلاد" type="date"/></VCol>
            <VCol cols="12" md="3"><AppTextField v-model="f.kyc_birth" label="مكان الميلاد"/></VCol>
            <VCol cols="12" md="3"><AppTextField v-model="f.kyc_spouse" label="اسم الزوج/ة"/></VCol>
            <VCol cols="12" md="3"><AppTextField v-model="f.kyc_marital" label="الحالة الزوجية"/></VCol>
            <VCol cols="12" md="3"><AppTextField v-model="f.kyc_edu" label="مستوى التعليم"/></VCol>
            <VCol cols="12">
              <p class="text-subtitle-2 font-weight-bold mb-2">مصدر الأموال</p>
              <div class="d-flex flex-wrap gap-3">
                <VCheckbox v-model="f.income_src" label="الراتب" value="salary" hide-details/>
                <VCheckbox v-model="f.income_src" label="عوائد تجارية" value="trade" hide-details/>
                <VCheckbox v-model="f.income_src" label="استثمارات" value="invest" hide-details/>
                <VCheckbox v-model="f.income_src" label="ادخارات شخصية" value="savings" hide-details/>
                <VCheckbox v-model="f.income_src" label="أخرى" value="other" hide-details/>
              </div>
            </VCol>
            <VCol cols="12" md="6"><AppTextField v-model="f.income_extra" label="الدخل الإضافي"/></VCol>
            <VCol cols="12" md="6"><AppSelect v-model="f.monthly_income" label="الدخل الشهري الإجمالي" :items="[{title:'أقل من مليون',value:'<1m'},{title:'مليون إلى 5 مليون',value:'1-5m'},{title:'5 إلى 10 مليون',value:'5-10m'},{title:'10 إلى 25 مليون',value:'10-25m'},{title:'أكثر من 25 مليون',value:'>25m'}]"/></VCol>
          </VRow></VCardText></VCard>

          <!-- الأسئلة الطبية -->
          <VCard class="mb-5"><VCardItem><template #prepend><VAvatar color="warning" variant="tonal" rounded><VIcon icon="tabler-heart-rate-monitor"/></VAvatar></template><VCardTitle>استفسار بيان الحالة الصحية</VCardTitle></VCardItem><VDivider/><VCardText>

            <template v-for="(q, key) in {q1:{l:'١- هل سبق واستلمت تقاعداً أو تعويضاً عن العجز؟',d:'q1d'},q3a:{l:'٣أ- هل تعاطيت مادة مخدرة؟',d:'q3ad'},q3b:{l:'٣ب- هل عولجت من الكحوليات؟',d:'q3bd'},q4a:{l:'٤أ- هل سكنت مع مصابين بالإيدز؟',d:'q4ad'},q4b:{l:'٤ب- هل أصيبت عائلتك بالإيدز؟',d:'q4bd'}}" :key="key">
              <div class="q-block">
                <p class="text-body-2 font-weight-bold mb-1">{{ q.l }}</p>
                <VRadioGroup :modelValue="(f as any)[key]" @update:modelValue="(v:any)=>(f as any)[key]=v" inline class="mb-1"><VRadio v-for="o in YN" :key="o.value" :label="o.title" :value="o.value"/></VRadioGroup>
                <AppTextField v-if="(f as any)[key]==='yes'" :modelValue="(f as any)[q.d]" @update:modelValue="(v:any)=>(f as any)[q.d]=v" label="التفاصيل"/>
              </div>
            </template>

            <div class="q-block">
              <p class="text-body-2 font-weight-bold mb-1">٢- لأي حد تعاطيت سيجارة أو المسكرات الكحولية؟</p>
              <AppTextField v-model="f.q2" label="التفاصيل"/>
            </div>

            <div class="q-block">
              <p class="text-body-2 font-weight-bold mb-1">٥- هل أنت بصحة جيدة؟</p>
              <VRadioGroup v-model="f.q5" inline><VRadio v-for="o in YN" :key="o.value" :label="o.title" :value="o.value"/></VRadioGroup>
            </div>

            <div class="q-block">
              <p class="text-body-2 font-weight-bold mb-2">٦- هل أصبت بأي من الأمراض التالية؟</p>
              <VTable density="compact">
                <tbody>
                  <tr v-for="item in q6Items" :key="item.key">
                    <td class="text-caption" style="width:70%">{{ item.label }}</td>
                    <td>
                      <VRadioGroup :modelValue="(f as any)[item.key]" @update:modelValue="(v:any)=>(f as any)[item.key]=v" inline hide-details>
                        <VRadio v-for="o in YN" :key="o.value" :label="o.title" :value="o.value"/>
                      </VRadioGroup>
                    </td>
                  </tr>
                </tbody>
              </VTable>
              <AppTextField v-model="f.q6_details" label="التفاصيل إن وجدت" class="mt-2"/>
            </div>

            <template v-for="(q,key) in {q7a:{l:'٧أ- فحوصات تشخيصية؟',d:'q7ad'},q7b:{l:'٧ب- معالجة أو كشف طبي؟',d:'q7bd'},q7c:{l:'٧ج- حادثة أو عملية جراحية؟',d:'q7cd'}}" :key="key">
              <div class="q-block">
                <p class="text-body-2 font-weight-bold mb-1">{{ q.l }}</p>
                <VRadioGroup :modelValue="(f as any)[key]" @update:modelValue="(v:any)=>(f as any)[key]=v" inline class="mb-1"><VRadio v-for="o in YN" :key="o.value" :label="o.title" :value="o.value"/></VRadioGroup>
                <AppTextField v-if="(f as any)[key]==='yes'" :modelValue="(f as any)[q.d]" @update:modelValue="(v:any)=>(f as any)[q.d]=v" label="التفاصيل"/>
              </div>
            </template>

            <div class="q-block" v-if="f.gender==='female'">
              <p class="text-body-2 font-weight-bold mb-1">٨- للمرأة فقط:</p>
              <p class="text-caption mb-1">أ) هل أنت حامل؟</p>
              <VRadioGroup v-model="f.q8a" inline class="mb-1"><VRadio v-for="o in YN" :key="o.value" :label="o.title" :value="o.value"/></VRadioGroup>
              <AppTextField v-if="f.q8a==='yes'" v-model="f.q8ad" label="عدد الأشهر" class="mb-2"/>
              <p class="text-caption mb-1">ب) هل حدث إجهاض أو قيصرية؟</p>
              <VRadioGroup v-model="f.q8b" inline class="mb-1"><VRadio v-for="o in YN" :key="o.value" :label="o.title" :value="o.value"/></VRadioGroup>
              <p class="text-caption mb-1">ج) أمراض في الجهاز التناسلي؟</p>
              <VRadioGroup v-model="f.q8c" inline class="mb-1"><VRadio v-for="o in YN" :key="o.value" :label="o.title" :value="o.value"/></VRadioGroup>
              <AppTextField v-if="f.q8c==='yes'" v-model="f.q8cd" label="التفاصيل"/>
            </div>

            <div class="q-block">
              <p class="text-body-2 font-weight-bold mb-2">٩- الوزن</p>
              <VRow>
                <VCol cols="12" md="3"><AppTextField v-model="f.q9_weight" label="الوزن (كغم)" type="number"/></VCol>
                <VCol cols="12" md="4"><VRadioGroup v-model="f.q9_changed" inline><template #label><span class="text-body-2 me-2">هل تغير وزنك؟</span></template><VRadio v-for="o in YN" :key="o.value" :label="o.title" :value="o.value"/></VRadioGroup></VCol>
                <VCol cols="6" md="2" v-if="f.q9_changed==='yes'"><AppTextField v-model="f.q9_gain" label="زيادة كغم"/></VCol>
                <VCol cols="6" md="2" v-if="f.q9_changed==='yes'"><AppTextField v-model="f.q9_loss" label="نقص كغم"/></VCol>
                <VCol cols="12" v-if="f.q9_changed==='yes'"><AppTextField v-model="f.q9_reason" label="السبب"/></VCol>
              </VRow>
            </div>

            <div class="q-block">
              <p class="text-body-2 font-weight-bold mb-2">١٠- معلومات الأسرة</p>
              <VTable density="compact" class="mb-2">
                <thead><tr><th>الفرد</th><th>العمر</th><th>الحالة الصحية</th><th>عمر الوفاة</th><th>سبب الوفاة</th></tr></thead>
                <tbody>
                  <tr v-for="(row,lbl) in {الزوجة:f.fam_spouse,الأب:f.fam_father,الأم:f.fam_mother}" :key="lbl">
                    <td class="text-caption font-weight-bold">{{lbl}}</td>
                    <td><AppTextField v-model="row.age" density="compact" hide-details/></td>
                    <td><AppTextField v-model="row.health" density="compact" hide-details/></td>
                    <td><AppTextField v-model="row.d_age" density="compact" hide-details/></td>
                    <td><AppTextField v-model="row.d_cause" density="compact" hide-details/></td>
                  </tr>
                </tbody>
              </VTable>
              <VRow>
                <VCol cols="6" md="3"><AppTextField v-model="f.sib_alive" label="عدد الإخوة الأحياء" type="number"/></VCol>
                <VCol cols="6" md="5"><AppTextField v-model="f.sib_complaint" label="هل يشكو أحدهم؟"/></VCol>
                <VCol cols="12"><p class="text-caption font-weight-bold mb-1">الإخوة الأموات:</p></VCol>
                <VCol cols="6" md="3" v-for="(s,i) in f.sib_dead" :key="i">
                  <AppTextField v-model="s.cause" :label="'سبب وفاة '+(i+1)"/>
                </VCol>
              </VRow>
            </div>

          </VCardText></VCard>

          <!-- التوقيعات -->
          <VCard class="mb-5"><VCardItem><template #prepend><VAvatar color="secondary" variant="tonal" rounded><VIcon icon="tabler-writing"/></VAvatar></template><VCardTitle>الإقرار والتوقيعات</VCardTitle></VCardItem><VDivider/><VCardText><VRow>
            <VCol cols="12" md="3"><AppTextField v-model="f.sign_city" label="مكان التوقيع"/></VCol>
            <VCol cols="4" md="2"><AppTextField v-model="f.sign_d" label="اليوم"/></VCol>
            <VCol cols="4" md="2"><AppTextField v-model="f.sign_m" label="الشهر"/></VCol>
            <VCol cols="4" md="2"><AppTextField v-model="f.sign_y" label="السنة"/></VCol>
            <VCol cols="12" md="6"><AppTextField v-model="f.holder_full" label="الاسم الكامل لطالب التأمين"/></VCol>
            <VCol cols="12" md="6"><AppTextField v-model="f.insured_full" label="الاسم الكامل للمؤمن عليه"/></VCol>
            <VCol cols="12" md="6"><AppTextField v-model="f.guardian" label="ولي الأمر الشرعي (إن وجد)"/></VCol>
            <VCol cols="12" md="6"><AppTextField v-model="f.witness" label="اسم الشاهد"/></VCol>
          </VRow></VCardText></VCard>

        </VCol>

        <!-- Sidebar -->
        <VCol cols="12" lg="4">
          <VCard class="position-sticky" style="top:80px">
            <VCardItem><template #prepend><VAvatar color="primary" variant="tonal" rounded><VIcon icon="tabler-eye"/></VAvatar></template><VCardTitle>ملخص</VCardTitle></VCardItem>
            <VDivider/><VCardText>
              <div class="d-flex flex-column gap-2">
                <div class="d-flex justify-space-between"><span class="text-body-2 text-medium-emphasis">المؤمن عليه</span><span class="text-body-2 font-weight-medium">{{ f.insured_ar||'—' }}</span></div>
                <div class="d-flex justify-space-between"><span class="text-body-2 text-medium-emphasis">نوع التأمين</span><span class="text-body-2">{{ f.ins_type||'—' }}</span></div>
                <div class="d-flex justify-space-between"><span class="text-body-2 text-medium-emphasis">مبلغ التأمين</span><span class="text-body-2 font-weight-bold text-primary">{{ f.sum?Number(f.sum).toLocaleString('ar-IQ'):'—' }}</span></div>
                <div class="d-flex justify-space-between"><span class="text-body-2 text-medium-emphasis">المدة</span><span class="text-body-2">{{ f.duration||'—' }} سنة</span></div>
                <div class="d-flex justify-space-between"><span class="text-body-2 text-medium-emphasis">الدفع</span><span class="text-body-2">{{ payLabel[f.payment]||'—' }}</span></div>
                <div class="d-flex justify-space-between"><span class="text-body-2 text-medium-emphasis">العملة</span><span class="text-body-2">{{ {iqd:'دينار عراقي',usd:'دولار أمريكي',other:f.currency_o}[f.currency as string]||'—' }}</span></div>
              </div>
            </VCardText>
            <VDivider/>
            <VCardActions class="pa-4"><VBtn block color="primary" size="large" prepend-icon="tabler-printer" @click="printForm">طباعة الاستمارة</VBtn></VCardActions>
          </VCard>
        </VCol>
      </VRow>
    </div>

    <!-- ══ PRINT VIEW ══ -->
    <div class="print-only" dir="rtl">

      <!-- ص1: الاستمارة الرئيسية -->
      <div class="print-page">
        <table width="100%" style="margin-bottom:6px;font-size:11px">
          <tr>
            <td style="width:35%">الفرع: <strong>{{f.branch_name}}</strong> — الرمز: {{f.branch_code}}</td>
            <td style="width:30%;text-align:center"><div style="font-size:13px;font-weight:bold;border:2px solid #000;padding:4px 8px;display:inline-block">شركة التأمين العراقية العامة<br><span style="font-size:9px">IRAQI GENERAL INSURANCE COMPANY</span></div></td>
            <td style="width:35%;text-align:left;font-size:10px">بغداد ساحة عقبة بن نافع<br>شارع خالد بن الوليد</td>
          </tr>
        </table>
        <div class="p-title">استمارة طلب تأمين على الحياة</div>
        <div style="font-size:11px;margin-bottom:6px">رقم الوثيقة: <strong>{{D(f.policy_no,15)}}</strong> &nbsp;&nbsp; اسم المندوب: <strong>{{D(f.agent_name,20)}}</strong></div>

        <table class="p-tbl" width="100%">
          <tr><td colspan="4" class="p-sh">١- الأسماء</td></tr>
          <tr><td style="width:18%">طالب التأمين:</td><td style="width:32%">{{D(f.holder_ar)}}</td><td style="width:18%">المؤمن عليه:</td><td>{{D(f.insured_ar)}}</td></tr>
          <tr><td>Name:</td><td colspan="3">{{D(f.insured_en)}}</td></tr>
          <tr><td colspan="4" class="p-sh">٢- الجنسية والجنس</td></tr>
          <tr><td>الجنسية:</td><td>{{D(f.nationality,12)}}</td><td>الجنس:</td><td>{{f.gender==='male'?'ذكر':f.gender==='female'?'أنثى':'......'}}</td></tr>
          <tr><td colspan="4" class="p-sh">٣- تاريخ الميلاد</td></tr>
          <tr><td>اليوم:</td><td>{{D(f.dob_d,6)}}</td><td>الشهر:</td><td>{{D(f.dob_m,6)}}</td></tr>
          <tr><td>السنة:</td><td>{{D(f.dob_y,8)}}</td><td>العمر:</td><td>{{D(f.age,6)}}</td></tr>
          <tr><td colspan="4" class="p-sh">٤- المستند الثبوتي</td></tr>
          <tr><td>النوع:</td><td>{{f.id_type==='national_id'?'هوية':f.id_type==='passport'?'جواز':'......'}}</td><td>الرقم:</td><td>{{D(f.id_no,15)}}</td></tr>
          <tr><td>تاريخ الإصدار:</td><td>{{D(f.id_iss_d,4)}}/{{D(f.id_iss_m,4)}}/{{D(f.id_iss_y,6)}}</td><td>تاريخ الانتهاء:</td><td>{{D(f.id_exp_d,4)}}/{{D(f.id_exp_m,4)}}/{{D(f.id_exp_y,6)}}</td></tr>
          <tr><td colspan="4" class="p-sh">٥- الحالة الاجتماعية</td></tr>
          <tr><td>الحالة:</td><td>{{maritalLabel[f.marital]||'......'}}</td><td>الزوج/ة:</td><td>{{D(f.spouse_name,15)}}</td></tr>
        </table>

        <p class="p-sh" style="margin-top:6px">المستفيدون من التأمين</p>
        <table class="p-atbl" width="100%" border="1" cellpadding="3" cellspacing="0" style="font-size:10px">
          <thead><tr><th colspan="4">في حالة وفاة المؤمن قبل انتهاء المدة</th></tr><tr><th>#</th><th>الاسم الرباعي</th><th>العمر</th><th>الحصة</th></tr></thead>
          <tbody><tr v-for="(r,i) in f.ben_life" :key="i"><td>{{i+1}}</td><td>{{r.name||'..............................................'}}</td><td>{{r.age||'....'}}</td><td>{{r.share||'....'}}</td></tr></tbody>
          <thead><tr><th colspan="4">في حالة وفاة المؤمن عليه قبل انتهاء المدة</th></tr><tr><th>#</th><th>الاسم الرباعي</th><th>العمر</th><th>الحصة</th></tr></thead>
          <tbody><tr v-for="(r,i) in f.ben_death" :key="i"><td>{{i+1}}</td><td>{{r.name||'..............................................'}}</td><td>{{r.age||'....'}}</td><td>{{r.share||'....'}}</td></tr></tbody>
        </table>

        <table class="p-tbl" width="100%" style="margin-top:6px">
          <tr><td colspan="4" class="p-sh">بيانات الوثيقة</td></tr>
          <tr><td style="width:18%">ابتداء التأمين:</td><td>{{D(f.start_d,4)}}/{{D(f.start_m,4)}}/{{D(f.start_y,6)}}</td><td>المدة:</td><td>{{f.duration||'...'}} سنة</td></tr>
          <tr><td>طريقة الدفع:</td><td>{{payLabel[f.payment]||'......'}}</td><td>نوع التأمين:</td><td>{{D(f.ins_type)}}</td></tr>
          <tr><td>الملاحق:</td><td colspan="3">{{D(f.riders)}}</td></tr>
          <tr><td>مبلغ التأمين:</td><td>{{f.sum?Number(f.sum).toLocaleString('ar-IQ'):'...............'}}</td><td>العملة:</td><td>{{({iqd:'دينار عراقي',usd:'دولار أمريكي',other:f.currency_o} as any)[f.currency]||'......'}}</td></tr>
          <tr><td>عنوان العمل:</td><td>{{D(f.employer_addr)}}</td><td>الهاتف:</td><td>{{D(f.employer_phone,12)}}</td></tr>
          <tr><td>المصاريف:</td><td>{{D(f.fees_written)}}</td><td>الأرقام:</td><td>{{f.fees_num||'...........'}}</td></tr>
          <tr><td>رقم الوصل:</td><td>{{D(f.receipt_no,12)}}</td><td>التاريخ:</td><td>{{D(f.receipt_date,12)}}</td></tr>
          <tr><td colspan="4" class="p-sh">بيانات السكن</td></tr>
          <tr><td>العنوان:</td><td colspan="3">{{D(f.home_addr)}}</td></tr>
          <tr><td>المحافظة:</td><td>{{D(f.governorate,10)}}</td><td>المحلة:</td><td>{{D(f.district,10)}}</td></tr>
          <tr><td>أقرب نقطة:</td><td colspan="3">{{D(f.landmark)}}</td></tr>
        </table>
      </div>

      <!-- ص2: AML/KYC -->
      <div class="print-page page-break">
        <div class="p-title">استمارة تعهد — بيانات المستفيد من التأمين</div>
        <p style="font-size:10px;margin-bottom:6px">أقر أني الموقع أدناه بإرادتي المنفردة وبكامل أهليتي القانونية أن جميع البيانات المذكورة أعلاه صحيحة كما أتعهد بامتثالي لقانون مكافحة غسل الأموال وتمويل الإرهاب رقم (39) لسنة 2015 والأنظمة والضوابط الصادرة بموجبه.</p>
        <table class="p-tbl" width="100%">
          <tr><td colspan="4" class="p-sh">بيانات المستفيد</td></tr>
          <tr><td style="width:18%">الاسم (عربي):</td><td>{{D(f.kyc_name_ar)}}</td><td>الاسم (إنجليزي):</td><td>{{D(f.kyc_name_en)}}</td></tr>
          <tr><td>الاتصال:</td><td>{{D(f.kyc_phone)}}</td><td>البريد:</td><td>{{D(f.kyc_email)}}</td></tr>
          <tr><td>السكن:</td><td colspan="3">{{D(f.kyc_addr)}}</td></tr>
          <tr><td>نوع السكن:</td><td>{{f.kyc_res==='own'?'ملك':f.kyc_res==='rent'?'إيجار':'......'}}</td><td>أقرب نقطة:</td><td>{{D(f.kyc_landmark)}}</td></tr>
          <tr><td>طبيعة العمل:</td><td colspan="3">{{empLabel[f.kyc_emp as string]||'......'}}</td></tr>
          <tr><td>المؤسسة:</td><td>{{D(f.kyc_emp_name)}}</td><td>المسمى:</td><td>{{D(f.kyc_title)}}</td></tr>
          <tr><td>جواز السفر:</td><td>{{D(f.kyc_passport)}}</td><td>جهة/تاريخ الإصدار:</td><td>{{D(f.kyc_passport_iss)}}</td></tr>
          <tr><td>تاريخ الميلاد:</td><td>{{D(f.kyc_dob)}}</td><td>مكان الميلاد:</td><td>{{D(f.kyc_birth)}}</td></tr>
          <tr><td>الحالة الزوجية:</td><td>{{D(f.kyc_marital)}}</td><td>التعليم:</td><td>{{D(f.kyc_edu)}}</td></tr>
          <tr><td>مصدر الأموال:</td><td colspan="3">{{f.income_src.join(' / ')||'..............................'}}</td></tr>
          <tr><td>الدخل الشهري:</td><td colspan="3">{{incomeLabel[f.monthly_income as string]||'......'}} دينار عراقي</td></tr>
        </table>
        <table width="100%" style="margin-top:20px;font-size:11px">
          <tr>
            <td style="width:50%">توقيع المؤمن له: .....................................<br><br>التاريخ: .....................................</td>
            <td style="width:50%;text-align:right">أنا مسؤول الأبلاغ عن غسل الأموال:<br><br>الاسم والتوقيع: .............................</td>
          </tr>
        </table>
      </div>

      <!-- ص3: الأسئلة الطبية -->
      <div class="print-page page-break">
        <div class="p-title">استفسار بيان الحالة الصحية</div>
        <p style="font-size:10px;margin-bottom:6px">وقع في {{D(f.sign_city,10)}} في {{D(f.sign_d,4)}}/{{D(f.sign_m,4)}}/{{D(f.sign_y,6)}}</p>

        <table class="p-atbl" width="100%" border="1" cellpadding="3" cellspacing="0" style="font-size:10px">
          <thead><tr><th style="width:5%">ت</th><th style="width:75%">السؤال</th><th style="width:10%">الجواب</th><th style="width:10%">ملاحظات</th></tr></thead>
          <tbody>
            <tr><td>١</td><td>هل سبق واستلمت تقاعداً أو تعويضاً عن العجز؟</td><td>{{yn(f.q1)}}</td><td>{{f.q1d}}</td></tr>
            <tr><td>٢</td><td>لأي حد تعاطيت سيجارة أو المسكرات الكحولية؟</td><td colspan="2">{{f.q2||'...........................'}}</td></tr>
            <tr><td>٣أ</td><td>هل تعاطيت مادة مخدرة؟</td><td>{{yn(f.q3a)}}</td><td>{{f.q3ad}}</td></tr>
            <tr><td>٣ب</td><td>هل عولجت من الكحوليات؟</td><td>{{yn(f.q3b)}}</td><td>{{f.q3bd}}</td></tr>
            <tr><td>٤أ</td><td>هل سكنت مع مصابين بالإيدز؟</td><td>{{yn(f.q4a)}}</td><td>{{f.q4ad}}</td></tr>
            <tr><td>٤ب</td><td>هل أصيبت عائلتك بالإيدز أو الجنون؟</td><td>{{yn(f.q4b)}}</td><td>{{f.q4bd}}</td></tr>
            <tr><td>٥</td><td>هل أنت بصحة جيدة؟</td><td>{{yn(f.q5)}}</td><td></td></tr>
            <tr><td colspan="4" class="p-sh">٦- هل أصبت بأي من الأمراض التالية؟</td></tr>
            <tr v-for="item in q6Items" :key="item.key">
              <td></td><td>{{item.label}}</td><td>{{yn((f as any)[item.key])}}</td><td></td>
            </tr>
            <tr v-if="f.q6_details"><td colspan="4">ملاحظات: {{f.q6_details}}</td></tr>
            <tr><td>٧أ</td><td>فحوصات تشخيصية (أشعة / تخطيط قلب / دم)؟</td><td>{{yn(f.q7a)}}</td><td>{{f.q7ad}}</td></tr>
            <tr><td>٧ب</td><td>معالجة أو كشف طبي في مستشفى؟</td><td>{{yn(f.q7b)}}</td><td>{{f.q7bd}}</td></tr>
            <tr><td>٧ج</td><td>حادثة أو عملية جراحية غير مذكورة؟</td><td>{{yn(f.q7c)}}</td><td>{{f.q7cd}}</td></tr>
            <tr v-if="f.gender==='female'"><td>٨أ</td><td>هل أنت حامل؟</td><td>{{yn(f.q8a)}}</td><td>{{f.q8ad}}</td></tr>
            <tr v-if="f.gender==='female'"><td>٨ب</td><td>إجهاض أو قيصرية؟</td><td>{{yn(f.q8b)}}</td><td></td></tr>
            <tr v-if="f.gender==='female'"><td>٨ج</td><td>أمراض في الجهاز التناسلي؟</td><td>{{yn(f.q8c)}}</td><td>{{f.q8cd}}</td></tr>
            <tr><td>٩</td><td>الوزن: {{f.q9_weight||'....'}} كغم &nbsp; تغير الوزن: {{yn(f.q9_changed)}} &nbsp; زيادة: {{f.q9_gain||'....'}}&nbsp; نقص: {{f.q9_loss||'....'}}</td><td colspan="2">السبب: {{f.q9_reason||'.....................'}}</td></tr>
          </tbody>
        </table>

        <p class="p-sh" style="margin-top:8px">١٠- معلومات الأسرة</p>
        <table class="p-atbl" width="100%" border="1" cellpadding="3" cellspacing="0" style="font-size:10px">
          <thead><tr><th>الفرد</th><th colspan="2">الأحياء</th><th colspan="2">الأموات</th></tr><tr><th></th><th>العمر</th><th>الحالة الصحية</th><th>عمر الوفاة</th><th>سبب الوفاة</th></tr></thead>
          <tbody>
            <tr v-for="(row,lbl) in {الزوجة:f.fam_spouse,الأب:f.fam_father,الأم:f.fam_mother}" :key="lbl">
              <td class="font-weight-bold">{{lbl}}</td>
              <td>{{row.age||'....'}}</td><td>{{row.health||'.....................'}}</td>
              <td>{{row.d_age||'....'}}</td><td>{{row.d_cause||'.....................'}}</td>
            </tr>
            <tr>
              <td>الإخوة الأحياء</td><td colspan="2">عدد: {{f.sib_alive||'....'}} — {{f.sib_complaint||'هل يشكو أحدهم؟ ...........'}}</td>
              <td colspan="2">الأموات وأسباب الوفاة: {{f.sib_dead.filter(s=>s.cause).map(s=>s.cause).join(' / ')||'.....................'}}</td>
            </tr>
          </tbody>
        </table>

        <!-- الإقرار النهائي -->
        <div style="border-top:1px solid #000;margin-top:10px;padding-top:6px;font-size:10px;line-height:1.8">
          إن الإقرارات والإجابات أعلاه هي تامة وكاملة وصحيحة وأنها ستعتبر أساساً لأي تأمين يصدر بموجبها...<br>
          ١- أخول أي طبيب التصريح بمعلومات تتعلق بصحتي. &nbsp; ٢- أتخلى عن حقي بموجب النصوص القانونية.
        </div>
        <table width="100%" style="margin-top:16px;font-size:11px">
          <tr>
            <td style="width:33%">المطلوب التأمين على حياته:<br><br>الاسم: {{D(f.insured_full)}}<br><br>التوقيع: .............................<br><br>التاريخ: {{D(f.sign_d,4)}}/{{D(f.sign_m,4)}}/{{D(f.sign_y,6)}}</td>
            <td style="width:34%;text-align:center">طالب التأمين:<br><br>الاسم: {{D(f.holder_full)}}<br><br>التوقيع: .............................<br><br>التاريخ: .........................</td>
            <td style="width:33%;text-align:right">مندوب التأمين:<br><br>الاسم الكامل: {{D(f.agent_name)}}<br><br>التوقيع: .............................<br><br>رقم الوثيقة: {{f.policy_no||'............'}}</td>
          </tr>
        </table>
        <div v-if="f.guardian" style="margin-top:12px;font-size:11px;border-top:1px dotted #000;padding-top:6px">
          ولي الأمر الشرعي: {{D(f.guardian)}} &nbsp;&nbsp; التوقيع: ..................................
        </div>
        <div v-if="f.witness" style="margin-top:8px;font-size:11px">
          الشاهد: {{D(f.witness)}} &nbsp;&nbsp; التوقيع: ..................................
        </div>
      </div>

    </div>
  </section>
</template>

<style scoped>
.q-block { padding: 10px 0; border-bottom: 1px solid rgba(var(--v-border-color), 0.12); }
.q-block:last-child { border-bottom: none; }
@media screen { .print-only { display: none !important; } }
@media print  { .no-print   { display: none !important; } }
@media print {
  .print-only { display: block !important; }
  .print-page { font-family: 'Noto Naskh Arabic','Arial',sans-serif; font-size: 12px; direction: rtl; padding: 10mm; color: #000; }
  .page-break { page-break-before: always; }
  .p-title { text-align:center; font-size:14px; font-weight:bold; border:2px solid #000; padding:5px; margin:8px 0; }
  .p-sh { background:#f0f0f0; font-weight:bold; padding:3px 6px; }
  .p-tbl { border-collapse:collapse; width:100%; }
  .p-tbl td { padding:3px 6px; vertical-align:top; }
  .p-atbl { border-collapse:collapse; }
  .p-atbl th { background:#f0f0f0; font-weight:bold; padding:3px 6px; text-align:right; }
  .p-atbl td { padding:3px 6px; }
}
</style>
