<script setup lang="ts">
import type { Policy, PolicyCategory, LifePolicyDetails } from '@db/apps/policies/types'
import type { VForm } from 'vuetify/components/VForm'

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

const policyNo = ref('')
const category = ref<PolicyCategory>('life')
const clientName = ref('')
const amount = ref<number>(0)
const issueDate = ref('')
const expiryDate = ref('')
const branchId = ref<number | null>(null)
const notes = ref('')

// Life insurance specific
const paymentCycle = ref('annual')
const accidentFee = ref<number>(0)
const durationYears = ref<number>(2)
const idNumber = ref('')
const birthDate = ref('')
const phone = ref('')
const address = ref('')
const beneficiaryName = ref('')
const beneficiaryRelation = ref('')

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

const fillForm = (p: Policy) => {
  policyNo.value = p.policyNo
  category.value = p.category
  clientName.value = p.clientName
  amount.value = p.amount
  issueDate.value = p.issueDate
  expiryDate.value = p.expiryDate
  branchId.value = p.branchId
  notes.value = p.notes ?? ''
  if (p.lifeDetails) {
    const ld = p.lifeDetails
    paymentCycle.value = ld.paymentCycle
    accidentFee.value = ld.accidentFee
    durationYears.value = ld.durationYears
    idNumber.value = ld.idNumber ?? ''
    birthDate.value = ld.birthDate ?? ''
    phone.value = ld.phone ?? ''
    address.value = ld.address ?? ''
    beneficiaryName.value = ld.beneficiaryName ?? ''
    beneficiaryRelation.value = ld.beneficiaryRelation ?? ''
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
    branchId.value = null
    issueDate.value = ''
    expiryDate.value = ''
    birthDate.value = ''
  })
}

const onSubmit = () => {
  refForm.value?.validate().then(({ valid }) => {
    if (!valid) return

    const payload: any = {
      id: props.policyToEdit?.id ?? 0,
      policyNo: policyNo.value,
      category: category.value,
      clientName: clientName.value,
      amount: amount.value,
      issueDate: issueDate.value,
      expiryDate: expiryDate.value,
      branchId: branchId.value,
      notes: notes.value || null,
    }

    if (isLifePolicy.value) {
      payload.lifeDetails = {
        paymentCycle: paymentCycle.value,
        accidentFee: accidentFee.value,
        durationYears: durationYears.value,
        idNumber: idNumber.value || null,
        birthDate: birthDate.value || null,
        phone: phone.value || null,
        address: address.value || null,
        beneficiaryName: beneficiaryName.value || null,
        beneficiaryRelation: beneficiaryRelation.value || null,
      }
    }

    emit('policyData', payload)
    closeDrawer()
  })
}
</script>

<template>
  <VNavigationDrawer
    :model-value="props.isDrawerOpen"
    temporary
    location="end"
    width="550"
    @update:model-value="emit('update:isDrawerOpen', $event)"
  >
    <AppDrawerHeaderSection
      :title="isEditMode ? 'تعديل وثيقة التأمين' : 'إضافة وثيقة تأمين'"
      @cancel="closeDrawer"
    />
    <VDivider />

    <VForm ref="refForm" v-model="isFormValid" class="pa-5" @submit.prevent="onSubmit">
      <VRow>
        <!-- رقم الوثيقة -->
        <VCol cols="12" md="6">
          <AppTextField
            v-model="policyNo"
            :rules="[requiredValidator]"
            label="رقم الوثيقة"
            placeholder="POL-2025-001"
          />
        </VCol>

        <!-- الفئة -->
        <VCol cols="12" md="6">
          <AppSelect
            v-model="category"
            :rules="[requiredValidator]"
            label="فئة التأمين"
            :items="categoryOptions"
          />
        </VCol>

        <!-- اسم العميل -->
        <VCol cols="12">
          <AppTextField
            v-model="clientName"
            :rules="[requiredValidator]"
            label="اسم العميل"
            placeholder="محمد أحمد"
          />
        </VCol>

        <!-- المبلغ -->
        <VCol cols="12" md="6">
          <AppTextField
            v-model="amount"
            :rules="[requiredValidator]"
            label="مبلغ الوثيقة (د.ع)"
            type="number"
            placeholder="5000000"
          />
        </VCol>

        <!-- الفرع -->
        <VCol cols="12" md="6">
          <AppSelect
            v-model="branchId"
            label="الفرع"
            :items="branchOptions"
            clearable
            clear-icon="tabler-x"
          />
        </VCol>

        <!-- تاريخ الإصدار -->
        <VCol cols="12" md="6">
          <AppDateTimePicker
            v-model="issueDate"
            :rules="[requiredValidator]"
            label="تاريخ الإصدار"
            placeholder="YYYY-MM-DD"
            :config="{ dateFormat: 'Y-m-d' }"
          />
        </VCol>

        <!-- تاريخ الانتهاء -->
        <VCol cols="12" md="6">
          <AppDateTimePicker
            v-model="expiryDate"
            :rules="[requiredValidator]"
            label="تاريخ الانتهاء"
            placeholder="YYYY-MM-DD"
            :config="{ dateFormat: 'Y-m-d' }"
          />
        </VCol>

        <!-- ملاحظات -->
        <VCol cols="12">
          <AppTextField
            v-model="notes"
            label="ملاحظات (اختياري)"
            placeholder="أي تفاصيل إضافية"
          />
        </VCol>

        <!-- ══ تأمين الحياة - بيانات إضافية ══ -->
        <template v-if="isLifePolicy">
          <VCol cols="12">
            <VDivider class="my-2" />
            <div class="text-subtitle-2 font-weight-bold mb-3 text-primary">
              <VIcon icon="tabler-heart-rate-monitor" class="me-1" size="18" />
              بيانات تأمين الحياة
            </div>
          </VCol>

          <!-- دورية السداد -->
          <VCol cols="12" md="6">
            <AppSelect
              v-model="paymentCycle"
              :rules="[requiredValidator]"
              label="دورية السداد"
              :items="paymentCycleOptions"
            />
          </VCol>

          <!-- مدة التأمين -->
          <VCol cols="12" md="6">
            <AppTextField
              v-model="durationYears"
              :rules="[requiredValidator, (v: number) => v >= 2 || 'الحد الأدنى سنتان']"
              label="مدة التأمين (سنوات)"
              type="number"
              placeholder="2"
            />
          </VCol>

          <!-- رسوم الحوادث -->
          <VCol cols="12" md="6">
            <AppTextField
              v-model="accidentFee"
              label="رسوم الحوادث الثابتة (د.ع)"
              type="number"
              placeholder="50000"
            />
          </VCol>

          <!-- رقم الهوية -->
          <VCol cols="12" md="6">
            <AppTextField
              v-model="idNumber"
              label="رقم الهوية الوطنية"
              placeholder="12345678"
            />
          </VCol>

          <!-- تاريخ الميلاد -->
          <VCol cols="12" md="6">
            <AppDateTimePicker
              v-model="birthDate"
              label="تاريخ الميلاد"
              placeholder="YYYY-MM-DD"
              :config="{ dateFormat: 'Y-m-d', maxDate: 'today' }"
            />
          </VCol>

          <!-- الهاتف -->
          <VCol cols="12" md="6">
            <AppTextField
              v-model="phone"
              label="رقم الهاتف"
              placeholder="07X-XXX-XXXX"
            />
          </VCol>

          <!-- العنوان -->
          <VCol cols="12">
            <AppTextField
              v-model="address"
              label="العنوان"
              placeholder="بغداد، الكرادة..."
            />
          </VCol>

          <!-- المستفيد -->
          <VCol cols="12" md="6">
            <AppTextField
              v-model="beneficiaryName"
              label="اسم المستفيد"
              placeholder="اسم المستفيد الكامل"
            />
          </VCol>

          <!-- صلة القرابة -->
          <VCol cols="12" md="6">
            <AppTextField
              v-model="beneficiaryRelation"
              label="صلة القرابة بالمستفيد"
              placeholder="زوجة / ابن / ابنة"
            />
          </VCol>
        </template>

        <!-- أزرار -->
        <VCol cols="12" class="d-flex gap-4">
          <VBtn type="submit">{{ isEditMode ? 'تحديث' : 'حفظ' }}</VBtn>
          <VBtn color="secondary" variant="tonal" @click="closeDrawer">إلغاء</VBtn>
        </VCol>
      </VRow>
    </VForm>
  </VNavigationDrawer>
</template>
