<script setup lang="ts">
import type { Policy, PolicyCategory, LifePolicyDetails } from '@db/apps/policies/types'
import type { VForm } from 'vuetify/components/VForm'
import AMLDetails from '../AMLDetails.vue'
import FireTheftDetails from '../FireTheftDetails.vue'
import LifeDetails from '../LifeDetails.vue'

interface Emit {
  (e: 'update:isDrawerOpen', value: boolean): void
  (e: 'policyData', value: any): void
}
interface Props {
  isDrawerOpen: boolean
  policyToEdit?: Policy | null
}

const props = withDefaults(defineProps<Props>(), { policyToEdit: null })
const emit = defineEmits<Emit>()

const isEditMode = computed(() => !!props.policyToEdit)
const isFormValid = ref(false)
const refForm = ref<VForm>()

const formData = ref({
  policyNo: '',
  category: 'life' as PolicyCategory,
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
  
  // Life specific
  lifeDetails: {
    paymentCycle: 'annual',
    accidentFee: 0,
    durationYears: 2,
    idNumber: '',
    birthDate: '',
    phone: '',
    address: '',
    marital_status: 'single',
    id_card_no: '',
    issue_authority_date: '',
    spouse_name: '',
    work_address: '',
    home_address_detail: '',
    height_cm: null,
    weight_kg: null,
    weight_change_last_year: '',
    health_questionnaire: Array(7).fill(null),
  },
  beneficiaries: [] as any[],
  
  // Fire/Theft specific
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

const paymentCycleOptions = [
  { title: 'سنوي', value: 'annual' },
  { title: 'نصف سنوي', value: 'semi_annual' },
  { title: 'ربع سنوي', value: 'quarterly' },
  { title: 'شهري', value: 'monthly' },
]

const isLifePolicy = computed(() => category.value === 'life')

const fillForm = (p: any) => {
  formData.value.policyNo = p.policy_no
  formData.value.category = p.category
  formData.value.status = p.status
  formData.value.clientName = p.client_name
  formData.value.occupation = p.occupation
  formData.value.mahalla = p.mahalla
  formData.value.zuqaq = p.zuqaq
  formData.value.dar = p.dar
  formData.value.amount = p.amount
  formData.value.issueDate = p.issue_date
  formData.value.expiryDate = p.expiry_date
  formData.value.branchId = p.branch_id
  formData.value.notes = p.notes ?? ''
  formData.value.source_of_funds = p.source_of_funds ?? ''
  formData.value.monthly_income = p.monthly_income ?? 0
  formData.value.business_type = p.business_type ?? ''
  formData.value.aml_officer_name = p.aml_officer_name ?? ''
  
  if (p.life_details) {
    formData.value.lifeDetails = { ...p.life_details }
  }
}

watch(() => props.isDrawerOpen, async open => {
  if (open) {
    const result = await $api<any>('/apps/branches?itemsPerPage=-1')
    branchOptions.value = (result?.branches ?? []).map((b: any) => ({ title: b.name, value: b.id }))
    if (props.policyToEdit) fillForm(props.policyToEdit)
  }
})

watch(() => props.policyToEdit, p => { if (p) fillForm(p) })

const closeDrawer = () => {
  emit('update:isDrawerOpen', false)
  nextTick(() => {
    refForm.value?.reset()
    formData.value.branchId = null
  })
}

const onSubmit = () => {
  refForm.value?.validate().then(({ valid }) => {
    if (!valid) return

    // Clean up empty beneficiaries
    const cleanBeneficiaries = formData.value.beneficiaries.filter(b => b.name_quad || b.name)
    
    const submissionData = {
      ...formData.value,
      beneficiaries: cleanBeneficiaries,
    }

    emit('policyData', submissionData)
    closeDrawer()
  })
}
</script>

<template>
  <VNavigationDrawer
    :model-value="props.isDrawerOpen"
    temporary
    location="end"
    width="750"
    @update:model-value="emit('update:isDrawerOpen', $event)"
  >
    <AppDrawerHeaderSection
      :title="isEditMode ? 'تعديل وثيقة التأمين' : 'إضافة وثيقة تأمين'"
      @cancel="closeDrawer"
    />
    <VDivider />

    <div style="height: calc(100vh - 70px); overflow-y: auto;">
      <VForm ref="refForm" v-model="isFormValid" class="pa-5" @submit.prevent="onSubmit">
        <VRow>
          <!-- رقم الوثيقة -->
          <VCol cols="12" md="6">
            <AppTextField
              v-model="formData.policyNo"
              :rules="[requiredValidator]"
              label="رقم الوثيقة"
              placeholder="POL-2025-001"
            />
          </VCol>

          <!-- الفئة -->
          <VCol cols="12" md="6">
            <AppSelect
              v-model="formData.category"
              :rules="[requiredValidator]"
              label="فئة التأمين"
              :items="categoryOptions"
            />
          </VCol>

          <!-- اسم العميل -->
          <VCol cols="12">
            <AppTextField
              v-model="formData.clientName"
              :rules="[requiredValidator]"
              label="اسم العميل"
              placeholder="محمد أحمد"
            />
          </VCol>

          <!-- المبلغ -->
          <VCol cols="12" md="6">
            <AppTextField
              v-model="formData.amount"
              :rules="[requiredValidator]"
              label="مبلغ الوثيقة (د.ع)"
              type="number"
              placeholder="5000000"
            />
          </VCol>

          <!-- الفرع -->
          <VCol cols="12" md="6">
            <AppSelect
              v-model="formData.branchId"
              label="الفرع"
              :items="branchOptions"
              :rules="[requiredValidator]"
              clearable
              clear-icon="tabler-x"
            />
          </VCol>

          <!-- تاريخ الإصدار -->
          <VCol cols="12" md="6">
            <AppDateTimePicker
              v-model="formData.issueDate"
              :rules="[requiredValidator]"
              label="تاريخ الإصدار"
              placeholder="YYYY-MM-DD"
              :config="{ dateFormat: 'Y-m-d' }"
            />
          </VCol>

          <!-- تاريخ الانتهاء -->
          <VCol cols="12" md="6">
            <AppDateTimePicker
              v-model="formData.expiryDate"
              :rules="[requiredValidator]"
              label="تاريخ الانتهاء"
              placeholder="YYYY-MM-DD"
              :config="{ dateFormat: 'Y-m-d' }"
            />
          </VCol>

          <!-- ملاحظات -->
          <VCol cols="12">
            <AppTextField
              v-model="formData.notes"
              label="ملاحظات (اختياري)"
              placeholder="أي تفاصيل إضافية"
            />
          </VCol>

          <!-- ══ تأمين الحياة - بيانات إضافية ══ -->
          <VCol v-if="formData.category === 'life'" cols="12">
            <VDivider class="my-4" />
            <LifeDetails :form-data="formData" />
          </VCol>

          <!-- ══ تأمين الحريق والسرقة ══ -->
          <VCol v-if="formData.category === 'fire_theft'" cols="12">
            <VDivider class="my-4" />
            <FireTheftDetails :form-data="formData" />
          </VCol>

          <!-- ══ AML ══ -->
          <VCol cols="12">
            <VDivider class="my-4" />
            <AMLDetails :form-data="formData" />
          </VCol>

          <!-- أزرار -->
          <VCol cols="12" class="d-flex gap-4">
            <VBtn type="submit">{{ isEditMode ? 'تحديث' : 'حفظ' }}</VBtn>
            <VBtn color="secondary" variant="tonal" @click="closeDrawer">إلغاء</VBtn>
          </VCol>
        </VRow>
      </VForm>
    </div>
  </VNavigationDrawer>
</template>
