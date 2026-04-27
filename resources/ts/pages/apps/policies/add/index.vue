<script setup lang="ts">
import AMLDetails from '@/views/apps/policies/AMLDetails.vue'
import FireTheftDetails from '@/views/apps/policies/FireTheftDetails.vue'
import LifeDetails from '@/views/apps/policies/LifeDetails.vue'
import EngineeringDetails from '@/views/apps/policies/EngineeringDetails.vue'
import VehicleDetails from '@/views/apps/policies/VehicleDetails.vue'
import { requiredValidator } from '@core/utils/validators'
import type { PolicyCategory } from '@db/apps/policies/types'

definePage({
  meta: { action: 'create', subject: 'Auth' },
})

const router = useRouter()
const route  = useRoute()

const policyId      = computed(() => route.query.id       ? Number(route.query.id) : null)
const presetCategory = computed(() => route.query.category ? String(route.query.category) : '')
const isEditMode    = computed(() => !!policyId.value)

const currentStep = ref(0)
const isFormValid = ref(false)
const refForm = ref<any>()

const steps = [
  { title: 'البيانات الأساسية', icon: 'tabler-file-info' },
  { title: 'تفاصيل التغطية', icon: 'tabler-shield-check' },
  { title: 'مكافحة غسل الأموال', icon: 'tabler-search' },
  { title: 'المراجعة والحفظ', icon: 'tabler-check' },
]

const formData = ref({
  policyNo: '',
  category: (presetCategory.value || 'life') as PolicyCategory,
  status: 'active',
  clientName: '',
  trade_name: '',
  permanent_address: '',
  phone: '',
  occupation: '',
  district: '',
  mahalla: '',
  zuqaq: '',
  dar: '',
  shop_no: '',
  street_region: '',
  shop_phone: '',
  amount: 0,
  issueDate: '',
  expiryDate: '',
  branchId: null as number | null,
  notes: '',
  source_of_funds: [] as string[],
  monthly_income: 'under_1m',
  business_type: '',
  aml_officer_name: '',
  
  lifeDetails: {
    paymentCycle: 'annual',
    accidentFee: 0,
    durationYears: 2,
    marital_status: 'single',
    gender: '',
    id_type: 'national_id',
    id_card_no: '',
    id_expiry: '',
    issue_authority_date: '',
    spouse_name: '',
    work_address: '',
    home_address_detail: '',
    governorate: '',
    landmark: '',
    height_cm: null,
    weight_kg: null,
    weight_change_last_year: '',
    health_questionnaire: Array(7).fill(null),
    health_q: null as any,
  },
  beneficiaries: [] as any[],
  
  engineeringDetails: {
    applicant_name: '', applicant_address: '', local_agent: '',
    project_owner: '', project_location: '', subcontractor: '', consultant: '',
    contract_no: '', contract_name: '', contract_phases: '',
    construction_months: '', construction_start: '', construction_end: '',
    maintenance_months: '', maintenance_start: '', maintenance_end: '',
    works_description: '',
    owner_materials_value: 0, owner_materials_desc: '',
    equipment_value: 0, equipment_desc: '',
    machinery_value: 0, machinery_list_attached: '',
    detail_region: '', detail_dimensions: '', detail_foundation: '',
    detail_construction_method: '', detail_materials: '',
    detail_demolition: '', detail_notes: '',
    subcontractor_works: '',
    seismic_risk: '', soil_nature: '', water_level_surface: '',
    nearest_water_body: '', water_level_min: '', water_level_avg: '', water_level_max: '',
    flood_risk: '', weather_conditions: '',
    express_freight_coverage: '',
    max_loss_per_incident: 0, debris_removal_limit: 0,
    existing_structures_coverage: '', existing_structures_value: 0,
    civil_liability_per_event: 0, civil_liability_bodily_injury: 0, civil_liability_property: 0,
    surrounding_area_desc: '',
  },

  vehicleDetails: {
    car_type: '', car_year: '', engine_power: '', color: '',
    plate_no: '', chassis_no: '', seats: '',
    purchase_price: 0, purchase_date: '', current_value: 0,
    coverage_type: '',
    multiple_vehicles: '', multiple_vehicles_count: '',
    parking_location: '',
    driver_complaints: '', driver_complaints_detail: '',
    is_borrowed: '', used_for_hire: '',
    driving_period: '', license_valid: '',
    previous_claim: '', previous_claim_detail: '',
    policy_encumbrance: '',
    insurer_rejected: '', insurer_special_terms: '',
    insurer_higher_premium: '', insurer_cancelled: '',
    claims: [
      { year: '', vehicle: '', category: '', total: '', notes: '' },
      { year: '', vehicle: '', category: '', total: '', notes: '' },
      { year: '', vehicle: '', category: '', total: '', notes: '' },
    ],
    inspection: {
      ext_body:     { status: '', damage: '' },
      ext_paint:    { status: '', damage: '' },
      ext_chrome:   { status: '', damage: '' },
      ext_emblem:   { status: '', damage: '' },
      ext_wipers:   { status: '', damage: '' },
      ext_antenna:  { status: '', damage: '' },
      int_seats:    { status: '', damage: '' },
      int_floor:    { status: '', damage: '' },
      int_radio:    { status: '', damage: '' },
      int_ac:       { status: '', damage: '' },
      int_recorder: { status: '', damage: '' },
      glass_front:  { status: '', damage: '' },
      glass_rear:   { status: '', damage: '' },
      glass_side:   { status: '', damage: '' },
      light_main:   { status: '', damage: '' },
      light_signal: { status: '', damage: '' },
      light_spare:  { status: '', damage: '' },
      light_mirror: { status: '', damage: '' },
      tire_front:   { status: '', damage: '' },
      tire_rear:    { status: '', damage: '' },
      tire_spare:   { status: '', damage: '' },
      tire_caps:    { status: '', damage: '' },
      tools:        { status: '', damage: '' },
      other_notes: '',
    },
  },

  fireTheftDetails: {
    is_owner: true,
    has_accounting_records: false,
    jewelry_storage: '',
    is_insured_amount_real: true,
    closing_duration: '',
    guarding_nature: '',
    previous_incidents: '',
    neighbors_incidents: '',
    hazardous_materials: '',
    previous_insurance_history: '',
    peril_explosion: false,
    peril_flood: false,
    peril_storm: false,
    peril_riot: false,
    peril_tank_overflow: false,
    peril_self_combustion: false,
    peril_aircraft_impact: false,
    peril_earthquake: false,
  },
  
  inspection: {
    construction_description: '',
    wall_material: '',
    roof_material: '',
    floor_material: '',
    neighbors_connectivity: false,
    neighbors_nature: '',
    doors_locks_type: '',
    window_grids: false,
    lighting_heating: '',
    machine_fuel: '',
    wood_layers: false,
    water_source: '',
    extinguishers: '',
    electrical_state: '',
    hazardous_observation: '',
    waste_disposal: '',
    sketch_path: '',
  },
  
  companyDetails: {
    authorized_name: '',
    authorized_address: '',
    founder_names: '',
    manager_name: '',
    board_chairman: '',
    board_members: '',
    shareholder_names: '',
    activity_type: 'none',
    founding_date: '',
    capital: 0,
    founding_place: '',
    external_auditor_name: '',
  },
  
  funds: {
    building: { value: 0, description: '' },
    goods: { value: 0, description: '' },
    machinery: { value: 0, description: '' },
    furniture: { value: 0, description: '' },
    others: { value: 0, description: '' },
  },
})

const branchOptions = ref<{ title: string; value: number }[]>([])
const categoryOptions = [
  { title: 'تأمين السيارات', value: 'vehicle' },
  { title: 'الحريق والسرقة', value: 'fire_theft' },
  { title: 'الصحي الجماعي', value: 'group_health' },
  { title: 'النقل / البحري', value: 'transport_marine' },
  { title: 'التأمين الهندسي', value: 'engineering' },
  { title: 'تأمين الحياة', value: 'life' },
  { title: 'الحوادث الشخصية', value: 'personal_accident' },
  { title: 'تأمين النقد', value: 'cash' },
]

onMounted(async () => {
  const result = await $api<any>('/apps/branches?itemsPerPage=-1')
  branchOptions.value = (result?.branches ?? []).map((b: any) => ({ title: b.name, value: b.id }))

  // ── Pre-selected category from navigation menu ────────────────────────
  if (presetCategory.value && !policyId.value) {
    formData.value.category = presetCategory.value as PolicyCategory
    currentStep.value = 1
  }

  // ── Edit mode: load existing policy data ──────────────────────────────
  if (policyId.value) {
    try {
      const p = await $api<any>(`/apps/policies/${policyId.value}`)
      formData.value.policyNo          = p.policyNo           ?? ''
      formData.value.category          = p.category           ?? 'life'
      formData.value.status            = p.status             ?? 'active'
      formData.value.clientName        = p.clientName         ?? ''
      formData.value.trade_name        = p.trade_name         ?? ''
      formData.value.permanent_address = p.permanent_address  ?? ''
      formData.value.phone             = p.phone              ?? ''
      formData.value.occupation        = p.occupation         ?? ''
      formData.value.district          = p.district           ?? ''
      formData.value.mahalla           = p.mahalla            ?? ''
      formData.value.zuqaq             = p.zuqaq              ?? ''
      formData.value.dar               = p.dar                ?? ''
      formData.value.shop_no           = p.shop_no            ?? ''
      formData.value.street_region     = p.street_region      ?? ''
      formData.value.shop_phone        = p.shop_phone         ?? ''
      formData.value.amount            = p.amount             ?? 0
      formData.value.issueDate         = p.issueDate          ?? ''
      formData.value.expiryDate        = p.expiryDate         ?? ''
      formData.value.branchId          = p.branchId           ?? null
      formData.value.notes             = p.notes              ?? ''
      formData.value.source_of_funds   = p.source_of_funds    ?? []
      formData.value.monthly_income    = p.monthly_income     ?? 'under_1m'
      formData.value.business_type     = p.business_type      ?? ''
      formData.value.aml_officer_name  = p.aml_officer_name   ?? ''

      if (p.lifeDetails) {
        formData.value.lifeDetails = {
          paymentCycle:            p.lifeDetails.paymentCycle            ?? 'annual',
          accidentFee:             p.lifeDetails.accidentFee             ?? 0,
          durationYears:           p.lifeDetails.durationYears           ?? 2,
          marital_status:          p.lifeDetails.marital_status          ?? 'single',
          gender:                  p.lifeDetails.gender                  ?? '',
          id_type:                 p.lifeDetails.id_type                 ?? 'national_id',
          id_card_no:              p.lifeDetails.id_card_no              ?? '',
          id_expiry:               p.lifeDetails.id_expiry               ?? '',
          issue_authority_date:    p.lifeDetails.issue_authority_date    ?? '',
          spouse_name:             p.lifeDetails.spouse_name             ?? '',
          work_address:            p.lifeDetails.work_address            ?? '',
          home_address_detail:     p.lifeDetails.home_address_detail     ?? '',
          governorate:             p.lifeDetails.governorate             ?? '',
          landmark:                p.lifeDetails.landmark                ?? '',
          height_cm:               p.lifeDetails.height_cm               ?? null,
          weight_kg:               p.lifeDetails.weight_kg               ?? null,
          weight_change_last_year: p.lifeDetails.weight_change_last_year ?? '',
          health_questionnaire:    p.lifeDetails.health_questionnaire    ?? Array(7).fill(null),
          health_q:                p.lifeDetails.health_q                ?? null,
        }
      }

      if (p.engineeringDetails)
        formData.value.engineeringDetails = { ...formData.value.engineeringDetails, ...p.engineeringDetails }

      if (p.vehicleDetails) {
        formData.value.vehicleDetails = {
          ...formData.value.vehicleDetails,
          ...p.vehicleDetails,
          inspection: { ...formData.value.vehicleDetails.inspection, ...(p.vehicleDetails.inspection ?? {}) },
          claims: p.vehicleDetails.claims ?? formData.value.vehicleDetails.claims,
        }
      }

      if (p.fireTheftDetails) {
        formData.value.fireTheftDetails = { ...formData.value.fireTheftDetails, ...p.fireTheftDetails }
      }

      if (p.companyDetails) {
        formData.value.companyDetails = { ...formData.value.companyDetails, ...p.companyDetails }
      }

      if (Array.isArray(p.beneficiaries) && p.beneficiaries.length)
        formData.value.beneficiaries = p.beneficiaries

    } catch (e) {
      console.error('Failed to load policy for edit', e)
    }
  }
})

const isSaving = ref(false)
const showSnackbar = ref(false)
const snackbarText = ref('')
const snackbarColor = ref('success')

const onSubmit = async () => {
  const { valid } = await refForm.value?.validate()
  
  if (!valid) {
    snackbarText.value = 'يرجى ملء جميع الحقول المطلوبة (المؤشرة بالنجمة)'
    snackbarColor.value = 'error'
    showSnackbar.value = true
    return
  }

  isSaving.value = true
  try {
    const cleanBeneficiaries = formData.value.beneficiaries.filter(b => b.name_quad || b.name)
    const submissionData = { ...formData.value, beneficiaries: cleanBeneficiaries }

    if (isEditMode.value) {
      await $api(`/apps/policies/${policyId.value}`, { method: 'PUT', body: submissionData })
    } else {
      await $api('/apps/policies', { method: 'POST', body: submissionData })
    }

    snackbarText.value = isEditMode.value ? 'تم تحديث الوثيقة بنجاح' : 'تم حفظ وإصدار الوثيقة بنجاح'
    snackbarColor.value = 'success'
    showSnackbar.value = true

    // Success redirect after a short delay
    setTimeout(() => {
      router.push('/apps/policies/list')
    }, 1500)
  } catch (error: any) {
    console.error('Failed to save policy', error)
    snackbarText.value = 'حدث خطأ أثناء الحفظ: ' + (error.response?._data?.message || 'يرجى المحاولة لاحقاً')
    snackbarColor.value = 'error'
    showSnackbar.value = true
  } finally {
    isSaving.value = false
  }
}
</script>

<template>
  <VRow>
    <VSnackbar v-model="showSnackbar" :color="snackbarColor" location="top end">
      {{ snackbarText }}
    </VSnackbar>

    <VOverlay v-model="isSaving" class="align-center justify-center" persistent>
      <VProgressCircular indeterminate size="64" color="primary" />
      <div class="mt-4 text-white font-weight-bold">{{ isEditMode ? 'جاري حفظ التعديلات...' : 'جاري معالجة وإصدار الوثيقة...' }}</div>
    </VOverlay>
    <VCol cols="12">
      <div class="d-flex align-center gap-2 mb-6">
        <VBtn icon="tabler-arrow-right" variant="tonal" size="small" @click="router.back()" />
        <h4 class="text-h4 mb-0">
          {{ isEditMode ? 'تعديل وثيقة التأمين' : presetCategory ? 'إصدار استمارة — ' + (categoryOptions.find(c => c.value === presetCategory)?.title ?? '') : 'إصدار وثيقة تأمين جديدة' }}
        </h4>
      </div>
    </VCol>

    <VCol cols="12">
      <VCard>
        <VCardText>
          <!-- Stepper Headers -->
          <div class="d-flex justify-space-between mb-8 overflow-x-auto pa-2">
            <div
              v-for="(step, index) in steps"
              :key="index"
              class="d-flex flex-column align-center gap-2 cursor-pointer"
              :class="{ 'text-primary': currentStep >= index, 'text-disabled': currentStep < index }"
              style="min-width: 120px"
              @click="currentStep = index"
            >
              <VAvatar
                :color="currentStep >= index ? 'primary' : 'secondary'"
                :variant="currentStep === index ? 'elevated' : 'tonal'"
                size="40"
              >
                <VIcon :icon="step.icon" />
              </VAvatar>
              <span class="text-caption font-weight-bold">{{ step.title }}</span>
            </div>
          </div>

          <VDivider class="mb-8" />

          <VForm ref="refForm" v-model="isFormValid" @submit.prevent>
            <!-- STEP 1: Basic Info -->
            <div v-show="currentStep === 0">
              <VRow>
                <VCol cols="12">
                  <div class="text-subtitle-1 mb-4 font-weight-bold">البيانات الأساسية للوثيقة</div>
                </VCol>
                <VCol cols="12" md="6">
                  <AppTextField v-model="formData.policyNo" label="رقم الوثيقة *" placeholder="مثال: POL-2025-001" :rules="[requiredValidator]" persistent-placeholder />
                </VCol>
                <VCol cols="12" md="6">
                  <AppSelect v-model="formData.category" :items="categoryOptions" label="فئة التأمين *" :rules="[requiredValidator]" />
                </VCol>
                <VCol cols="12" md="6">
                  <AppTextField v-model="formData.clientName" label="اسم طالب التأمين الكامل *" :rules="[requiredValidator]" placeholder="الاسم كما في الهوية" />
                </VCol>
                <VCol cols="12" md="6">
                  <AppSelect v-model="formData.branchId" :items="branchOptions" label="الفرع المصدر *" :rules="[requiredValidator]" />
                </VCol>
                <VCol cols="12" md="4">
                  <AppTextField v-model="formData.amount" label="مبلغ التأمين الإجمالي (د.ع) *" type="number" :rules="[requiredValidator]" />
                </VCol>
                <VCol cols="12" md="4">
                  <AppDateTimePicker v-model="formData.issueDate" label="تاريخ الإصدار *" :rules="[requiredValidator]" :config="{ dateFormat: 'Y-m-d' }" />
                </VCol>
                <VCol cols="12" md="4">
                  <AppDateTimePicker v-model="formData.expiryDate" label="تاريخ الانتهاء *" :rules="[requiredValidator]" :config="{ dateFormat: 'Y-m-d' }" />
                </VCol>

                <VCol cols="12">
                  <VDivider class="my-4" />
                  <div class="text-subtitle-1 mb-4 font-weight-bold">العنوان ومعلومات الاتصال</div>
                </VCol>
                <VCol cols="12" md="6">
                  <AppTextField v-model="formData.permanent_address" label="العنوان الدائم *" :rules="[requiredValidator]" />
                </VCol>
                <VCol cols="12" md="6">
                  <AppTextField v-model="formData.phone" label="رقم الهاتف الأساسي *" :rules="[requiredValidator]" />
                </VCol>
                <VCol cols="12" md="4">
                  <AppTextField v-model="formData.district" label="الحي (اختياري)" />
                </VCol>
                <VCol cols="12" md="4">
                  <AppTextField v-model="formData.mahalla" label="المحلة (اختياري)" />
                </VCol>
                <VCol cols="12" md="4">
                  <AppTextField v-model="formData.zuqaq" label="الزقاق (اختياري)" />
                </VCol>
                <VCol cols="12">
                  <AppTextField v-model="formData.notes" label="ملاحظات إضافية (اختياري)" placeholder="أي ملاحظات تخص الوثيقة..." />
                </VCol>
              </VRow>
            </div>

            <!-- STEP 2: Category Details -->
            <div v-show="currentStep === 1">
              <div v-if="formData.category === 'life'">
                <LifeDetails :form-data="formData" />
              </div>
              <div v-else-if="formData.category === 'fire_theft'">
                <FireTheftDetails :form-data="formData" />
              </div>
              <div v-else-if="formData.category === 'vehicle'">
                <VehicleDetails :form-data="formData" />
              </div>
              <div v-else-if="formData.category === 'engineering'">
                <EngineeringDetails :form-data="formData" />
              </div>
              <div v-else class="pa-10 text-center">
                <VIcon icon="tabler-info-circle" size="48" color="info" class="mb-4" />
                <p>لا توجد بيانات إضافية مطلوبة لهذه الفئة حالياً</p>
              </div>
            </div>

            <!-- STEP 3: AML / KYB -->
            <div v-show="currentStep === 2">
              <AMLDetails :form-data="formData" />
            </div >

            <!-- STEP 4: Review -->
            <div v-show="currentStep === 3">
              <VAlert type="info" variant="tonal" class="mb-6">
                يرجى مراجعة كافة البيانات المدخلة قبل الحفظ. لن يكون بالإمكان تعديل بعض الحقول بعد الإصدار الرسمي.
              </VAlert>
              <VRow>
                <VCol cols="12" md="6">
                  <VList density="compact" class="border rounded">
                    <VListItem title="رقم الوثيقة" :subtitle="formData.policyNo" />
                    <VListItem title="العميل" :subtitle="formData.clientName" />
                    <VListItem title="المبلغ" :subtitle="formData.amount.toString()" />
                  </VList>
                </VCol>
                <VCol cols="12" md="6">
                   <VList density="compact" class="border rounded">
                    <VListItem title="تاريخ الإصدار" :subtitle="formData.issueDate" />
                    <VListItem title="تاريخ الانتهاء" :subtitle="formData.expiryDate" />
                    <VListItem title="مسؤول الإبلاغ" :subtitle="formData.aml_officer_name" />
                  </VList>
                </VCol>
              </VRow>
            </div>

            <!-- Navigation Buttons -->
            <div class="d-flex justify-space-between mt-8">
              <VBtn
                color="secondary"
                variant="tonal"
                :disabled="currentStep === 0"
                prepend-icon="tabler-chevron-right"
                @click="currentStep--"
              >
                السابق
              </VBtn>

              <VBtn
                v-if="currentStep < steps.length - 1"
                append-icon="tabler-chevron-left"
                @click="currentStep++"
              >
                التالي
              </VBtn>

              <VBtn
                v-else
                color="success"
                append-icon="tabler-device-floppy"
                @click="onSubmit"
              >
                {{ isEditMode ? 'حفظ التعديلات' : 'حفظ وإصدار الوثيقة' }}
              </VBtn>
            </div>
          </VForm>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
