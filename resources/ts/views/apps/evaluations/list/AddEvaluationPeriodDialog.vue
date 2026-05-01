<script setup lang="ts">
import type { VForm } from 'vuetify/components/VForm'

interface Emit {
  (e: 'update:isDialogVisible', value: boolean): void
  (e: 'periodData', value: any): void
}
interface Props { isDialogVisible: boolean }

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

const isFormValid = ref(false)
const refForm = ref<VForm>()

const year = ref<number>(new Date().getFullYear())
const periodNo = ref<number>(1)
const startDate = ref('')
const endDate = ref('')
const selectedBranches = ref<number[]>([])
const branchOptions = ref<{ title: string; value: number }[]>([])

const periodOptions = [
  { title: 'الفترة 1 — يناير / فبراير', value: 1 },
  { title: 'الفترة 2 — مارس / أبريل', value: 2 },
  { title: 'الفترة 3 — مايو / يونيو', value: 3 },
  { title: 'الفترة 4 — يوليو / أغسطس', value: 4 },
  { title: 'الفترة 5 — سبتمبر / أكتوبر', value: 5 },
  { title: 'الفترة 6 — نوفمبر / ديسمبر', value: 6 },
]

onMounted(async () => {
  try {
    const res = await $api<any>('/apps/branches?itemsPerPage=-1')
    branchOptions.value = (res?.branches ?? []).map((b: any) => ({ title: b.name, value: b.id }))
  } catch (e) {
    console.error('Failed to load branches', e)
  }
})

// 👉 Select All Logic
const selectAllBranches = () => {
  selectedBranches.value = branchOptions.value.map(b => b.value)
}

const clearAllBranches = () => {
  selectedBranches.value = []
}

watch(periodNo, no => {
  const periodDates: Record<number, { start: string; end: string }> = {
    1: { start: `${year.value}-01-01`, end: `${year.value}-02-28` },
    2: { start: `${year.value}-03-01`, end: `${year.value}-04-30` },
    3: { start: `${year.value}-05-01`, end: `${year.value}-06-30` },
    4: { start: `${year.value}-07-01`, end: `${year.value}-08-31` },
    5: { start: `${year.value}-09-01`, end: `${year.value}-10-31` },
    6: { start: `${year.value}-11-01`, end: `${year.value}-12-31` },
  }
  if (periodDates[no]) {
    startDate.value = periodDates[no].start
    endDate.value = periodDates[no].end
  }
}, { immediate: false })

const closeDialog = () => {
  emit('update:isDialogVisible', false)
  nextTick(() => {
    refForm.value?.reset()
    startDate.value = ''
    endDate.value = ''
    selectedBranches.value = []
  })
}

const onSubmit = () => {
  refForm.value?.validate().then(({ valid }) => {
    if (!valid) return
    emit('periodData', { 
      year: year.value, 
      periodNo: periodNo.value, 
      startDate: startDate.value, 
      endDate: endDate.value,
      branchIds: selectedBranches.value
    })
    closeDialog()
  })
}
</script>

<template>
  <VDialog
    :model-value="props.isDialogVisible"
    max-width="600"
    persistent
    @update:model-value="emit('update:isDialogVisible', $event)"
  >
    <DialogCloseBtn @click="closeDialog" />

    <VCard>
      <VCardItem class="py-3 px-4" density="compact">
        <template #prepend>
          <VAvatar color="primary" variant="tonal" rounded size="38" class="me-2">
            <VIcon icon="tabler-calendar-plus" size="20" />
          </VAvatar>
        </template>
        <VCardTitle class="text-subtitle-1 font-weight-bold">فتح فترة تقييم جديدة</VCardTitle>
      </VCardItem>

      <VDivider thickness="1" />

      <VForm ref="refForm" v-model="isFormValid" @submit.prevent="onSubmit" class="px-4 py-3">
        <VRow>
          <VCol cols="12" md="6">
            <AppTextField
              v-model="year"
              :rules="[requiredValidator]"
              label="السنة"
              type="number"
              placeholder="2025"
            />
          </VCol>

          <VCol cols="12" md="6">
            <AppSelect
              v-model="periodNo"
              :rules="[requiredValidator]"
              label="الفترة"
              :items="periodOptions"
            />
          </VCol>

          <!-- اختيار فروع مخصصة (اختيار متعدد) -->
          <VCol cols="12">
            <div class="d-flex justify-space-between align-center mb-1">
              <label class="v-label font-weight-medium">الفروع المشمولة بالتقييم</label>
              <div class="d-flex gap-2">
                <VBtn variant="text" size="small" color="primary" @click="selectAllBranches">اختيار الكل</VBtn>
                <VBtn variant="text" size="small" color="secondary" @click="clearAllBranches">مسح الكل</VBtn>
              </div>
            </div>
            <AppSelect
              v-model="selectedBranches"
              placeholder="اختر فروعاً محددة أو اضغط اختيار الكل"
              :items="branchOptions"
              multiple
              chips
              closable-chips
              clearable
            />
          </VCol>

          <VCol cols="12" md="6">
            <AppDateTimePicker
              v-model="startDate"
              :rules="[requiredValidator]"
              label="تاريخ البدء"
              placeholder="اختر تاريخ البدء"
              :config="{ dateFormat: 'Y-m-d', allowInput: true }"
            />
          </VCol>

          <VCol cols="12" md="6">
            <AppDateTimePicker
              v-model="endDate"
              :rules="[requiredValidator]"
              label="تاريخ الانتهاء"
              placeholder="اختر تاريخ الانتهاء"
              :config="{ dateFormat: 'Y-m-d', allowInput: true }"
            />
          </VCol>

        </VRow>
      </VForm>

      <VDivider thickness="1" />

      <VCardActions class="justify-center gap-2 pa-3">
        <VBtn variant="tonal" color="secondary" class="px-4" @click="closeDialog">
          <VIcon start icon="tabler-x" size="16" />
          إلغاء
        </VBtn>
        <VBtn type="submit" color="primary" class="px-4" @click="onSubmit">
          <VIcon start icon="tabler-calendar-plus" size="16" />
          فتح الفترة
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
