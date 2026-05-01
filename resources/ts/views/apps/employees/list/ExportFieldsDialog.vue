<script setup lang="ts">
interface Field {
  key: string
  title: string
  default?: boolean
}

const props = defineProps<{
  modelValue: boolean
  fields: Field[]
}>()

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  'export': [type: 'pdf' | 'excel', selectedFields: string[]]
}>()

const isDialogVisible = computed({
  get: () => props.modelValue,
  set: val => emit('update:modelValue', val),
})

const selectedFields = ref<string[]>(
  props.fields.filter(f => f.default !== false).map(f => f.key),
)

watch(() => props.modelValue, (open) => {
  if (open) {
    selectedFields.value = props.fields.filter(f => f.default !== false).map(f => f.key)
  }
})

const selectAll = () => {
  selectedFields.value = props.fields.map(f => f.key)
}

const deselectAll = () => {
  selectedFields.value = []
}

const handleExport = (type: 'pdf' | 'excel') => {
  if (selectedFields.value.length === 0) {
    return
  }
  emit('export', type, [...selectedFields.value])
  isDialogVisible.value = false
}
</script>

<template>
  <VDialog
    v-model="isDialogVisible"
    max-width="480"
    persistent
  >
    <VCard>
      <VCardTitle class="text-h5 py-4 px-5">
        اختيار الحقول للتصدير
      </VCardTitle>

      <VDivider />

      <VCardText class="px-5 py-4">
        <div class="d-flex gap-2 mb-4">
          <VBtn
            variant="text"
            size="small"
            color="primary"
            @click="selectAll"
          >
            تحديد الكل
          </VBtn>
          <VBtn
            variant="text"
            size="small"
            color="secondary"
            @click="deselectAll"
          >
            إلغاء الكل
          </VBtn>
        </div>

        <VRow dense>
          <VCol
            v-for="field in props.fields"
            :key="field.key"
            cols="6"
          >
            <VCheckbox
              v-model="selectedFields"
              :value="field.key"
              :label="field.title"
              density="compact"
              hide-details
            />
          </VCol>
        </VRow>
      </VCardText>

      <VDivider />

      <VCardActions class="px-5 py-3 justify-end gap-2">
        <VBtn
          variant="outlined"
          @click="isDialogVisible = false"
        >
          إلغاء
        </VBtn>
        <VBtn
          variant="flat"
          color="success"
          prepend-icon="tabler-file-spreadsheet"
          :disabled="selectedFields.length === 0"
          @click="handleExport('excel')"
        >
          Excel
        </VBtn>
        <VBtn
          variant="flat"
          color="error"
          prepend-icon="tabler-file-type-pdf"
          :disabled="selectedFields.length === 0"
          @click="handleExport('pdf')"
        >
          PDF
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
