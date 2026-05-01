<script setup lang="ts">
import type { Employee } from '@db/apps/employees/types'

interface Props {
  isDialogVisible: boolean
  employee: Employee | null
}

const props = defineProps<Props>()
const emit = defineEmits<{ (e: 'update:isDialogVisible', val: boolean): void }>()

const dialogVisible = computed({
  get: () => props.isDialogVisible,
  set: val => emit('update:isDialogVisible', val),
})

const getFullName = (emp: Employee | null) => {
  if (!emp) return '-'
  return `${emp.firstName} ${emp.secondName} ${emp.thirdName} ${emp.fourthName || ''} ${emp.lastName || ''}`.trim()
}

const formatDate = (date: string | null | undefined) => {
  if (!date) return '-'
  const [year, month, day] = date.split('-')
  return `${day}/${month}/${year}`
}

const resolveGenderVariant = (gender: string) =>
  gender === 'male' ? { color: 'info', label: 'ذكر' } : { color: 'error', label: 'أنثى' }

const resolveJobTypeVariant = (jobType: string) => {
  if (jobType === 'permanent') return { color: 'success', label: 'تعيين ملاك' }
  if (jobType === 'contract') return { color: 'warning', label: 'عقد' }
  return { color: 'secondary', label: 'أجر يومي' }
}
</script>

<template>
  <VDialog
    v-model="dialogVisible"
    max-width="600"
    scrollable
  >
    <VCard>
      <VCardItem class="py-3 px-4">
        <template #prepend>
          <VAvatar color="primary" variant="tonal" rounded size="38" class="me-2">
            <VIcon icon="tabler-user" size="20" />
          </VAvatar>
        </template>
        <VCardTitle class="text-subtitle-1 font-weight-bold">
          ملف الموظف
        </VCardTitle>
        <template #append>
          <VBtn icon variant="text" size="small" @click="dialogVisible = false">
            <VIcon icon="tabler-x" />
          </VBtn>
        </template>
      </VCardItem>

      <VDivider />

      <VCardText class="pa-4">
        <div v-if="employee">
          <!-- ── البيانات الشخصية ── -->
          <div class="d-flex align-center gap-2 mb-3">
            <VAvatar color="info" variant="tonal" size="32">
              <VIcon icon="tabler-user" size="16" />
            </VAvatar>
            <span class="text-subtitle-2 font-weight-bold">البيانات الشخصية</span>
          </div>

          <VCard variant="outlined" class="mb-5 pa-3">
            <VRow dense>
              <VCol cols="12" md="6" class="mb-2">
                <div class="text-caption text-medium-emphasis">الاسم الكامل</div>
                <div class="font-weight-bold">{{ getFullName(employee) }}</div>
              </VCol>
              <VCol cols="12" md="6" class="mb-2">
                <div class="text-caption text-medium-emphasis">الجنس</div>
                <VChip
                  :color="resolveGenderVariant(employee.gender).color"
                  size="small"
                  label
                >
                  {{ resolveGenderVariant(employee.gender).label }}
                </VChip>
              </VCol>
              <VCol v-if="employee.phone" cols="12" md="6" class="mb-2">
                <div class="text-caption text-medium-emphasis">الهاتف</div>
                <div>{{ employee.phone }}</div>
              </VCol>
              <VCol v-if="employee.birthDate" cols="12" md="6" class="mb-2">
                <div class="text-caption text-medium-emphasis">تاريخ المواليد</div>
                <div>{{ formatDate(employee.birthDate) }}</div>
              </VCol>
              <VCol v-if="employee.nationalId" cols="12" md="6" class="mb-2">
                <div class="text-caption text-medium-emphasis">البطاقة الوطنية</div>
                <div>{{ employee.nationalId }}</div>
              </VCol>
              <VCol v-if="employee.address" cols="12" class="mb-2">
                <div class="text-caption text-medium-emphasis">العنوان / أقرب نقطة دالة</div>
                <div>{{ employee.address }}</div>
              </VCol>
            </VRow>
          </VCard>

          <!-- ── البيانات الوظيفية ── -->
          <div class="d-flex align-center gap-2 mb-3">
            <VAvatar color="success" variant="tonal" size="32">
              <VIcon icon="tabler-briefcase" size="16" />
            </VAvatar>
            <span class="text-subtitle-2 font-weight-bold">البيانات الوظيفية</span>
          </div>

          <VCard variant="outlined" class="pa-3">
            <VRow dense>
              <VCol cols="12" md="6" class="mb-2">
                <div class="text-caption text-medium-emphasis">الدرجة</div>
                <div>{{ employee.degree }}</div>
              </VCol>
              <VCol cols="12" md="6" class="mb-2">
                <div class="text-caption text-medium-emphasis">العنوان الوظيفي</div>
                <div>{{ employee.rank }}</div>
              </VCol>
              <VCol cols="12" md="6" class="mb-2">
                <div class="text-caption text-medium-emphasis">نوع الموظف</div>
                <div>{{ employee.jobTrack === 'producer' ? 'منتج' : 'إداري' }}</div>
              </VCol>
              <VCol cols="12" md="6" class="mb-2">
                <div class="text-caption text-medium-emphasis">الشهادة</div>
                <div>{{ employee.education }}</div>
              </VCol>
              <VCol cols="12" md="6" class="mb-2">
                <div class="text-caption text-medium-emphasis">نوع الوظيفة</div>
                <VChip
                  :color="resolveJobTypeVariant(employee.jobType).color"
                  size="small"
                  label
                >
                  {{ resolveJobTypeVariant(employee.jobType).label }}
                </VChip>
              </VCol>
              <VCol v-if="employee.productionNo" cols="12" md="6" class="mb-2">
                <div class="text-caption text-medium-emphasis">الرقم الإنتاجي</div>
                <div>{{ employee.productionNo }}</div>
              </VCol>
              <VCol cols="12" md="6" class="mb-2">
                <div class="text-caption text-medium-emphasis">تاريخ التعيين</div>
                <div>{{ formatDate(employee.hireDate) }}</div>
              </VCol>
              <VCol cols="12" md="6" class="mb-2">
                <div class="text-caption text-medium-emphasis">الفرع</div>
                <div>{{ employee.branch?.name || '-' }}</div>
              </VCol>
            </VRow>
          </VCard>
        </div>
      </VCardText>

      <VDivider />

      <VCardActions class="pa-3 d-flex justify-end">
        <VBtn
          variant="tonal"
          color="secondary"
          size="small"
          @click="dialogVisible = false"
        >
          <VIcon start icon="tabler-x" size="16" />
          إغلاق
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
