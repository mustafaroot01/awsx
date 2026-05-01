<script setup lang="ts">
import { useConfirmDelete } from '@/composables/useConfirmDelete'
import { showPermissionError, $api } from '@/utils/api'

interface AdminPosition {
  id: number
  name: string
  points: number
  sort_order: number
  is_active: boolean
  created_at: string
}

definePage({
  meta: {
    action: 'read',
    subject: 'Employee',
  },
})

const { open: openConfirmDelete } = useConfirmDelete()

// 👉 Data
const positions = ref<AdminPosition[]>([])
const isLoading = ref(false)

// 👉 Dialog
const isDialogVisible = ref(false)
const editingPosition = ref<AdminPosition | null>(null)
const dialogTitle = computed(() => editingPosition.value ? 'تعديل منصب إداري' : 'إضافة منصب إداري')

// 👉 Form
const name = ref('')
const points = ref(0)
const sortOrder = ref(0)
const isActive = ref(true)
const isSaving = ref(false)

// 👉 Snackbar
const isSnackbarVisible = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref('success')

const showSnackbar = (msg: string, color = 'success') => {
  snackbarMessage.value = msg
  snackbarColor.value = color
  isSnackbarVisible.value = true
}

// 👉 Fetch
const fetchPositions = async () => {
  isLoading.value = true
  try {
    const res = await $api('/apps/admin-positions')
    positions.value = Array.isArray(res) ? res : res?.data ?? []
  } catch (e) {
    positions.value = []
    showSnackbar('فشل تحميل العناوين الإدارية - يرجى إنشاء الـ API endpoint', 'error')
  } finally {
    isLoading.value = false
  }
}

// 👉 Dialog
const openAddDialog = () => {
  editingPosition.value = null
  name.value = ''
  points.value = 0
  sortOrder.value = 0
  isActive.value = true
  isDialogVisible.value = true
}

const openEditDialog = (pos: AdminPosition) => {
  editingPosition.value = pos
  name.value = pos.name
  points.value = pos.points
  sortOrder.value = pos.sort_order
  isActive.value = pos.is_active
  isDialogVisible.value = true
}

const closeDialog = () => {
  isDialogVisible.value = false
  editingPosition.value = null
}

const savePosition = async () => {
  if (!name.value.trim()) {
    showSnackbar('الاسم مطلوب', 'error')
    return
  }

  isSaving.value = true
  try {
    const payload = {
      name: name.value.trim(),
      points: Number(points.value),
      sort_order: Number(sortOrder.value),
      is_active: isActive.value,
    }

    if (editingPosition.value) {
      await $api(`/apps/admin-positions/${editingPosition.value.id}`, {
        method: 'POST',
        body: { ...payload, _method: 'PUT' },
      })
      showSnackbar('تم التعديل بنجاح')
    } else {
      await $api('/apps/admin-positions', {
        method: 'POST',
        body: payload,
      })
      showSnackbar('تم الإضافة بنجاح')
    }
    closeDialog()
    await fetchPositions()
  } catch (error: any) {
    if (!showPermissionError(error)) {
      showSnackbar(error.message || 'حدث خطأ أثناء الحفظ', 'error')
    }
  } finally {
    isSaving.value = false
  }
}

// 👉 Delete
const deletePosition = (pos: AdminPosition) => {
  openConfirmDelete({
    name: pos.name,
    title: 'حذف المنصب الإداري',
    message: `هل أنت متأكد من حذف "${pos.name}"؟`,
    onConfirm: async () => {
      try {
        await $api(`/apps/admin-positions/${pos.id}`, { method: 'DELETE' })
        showSnackbar('تم الحذف بنجاح')
        await fetchPositions()
      } catch (error: any) {
        if (!showPermissionError(error)) {
          showSnackbar(error.message || 'حدث خطأ أثناء الحذف', 'error')
        }
      }
    },
  })
}

// 👉 Init
onMounted(fetchPositions)
</script>

<template>
  <section>
    <!-- Header -->
    <VCard class="mb-4">
      <VCardItem>
        <VCardTitle class="d-flex align-center gap-2">
          <VIcon icon="tabler-crown" size="24" />
          العناوين الإدارية
        </VCardTitle>
        <template #append>
          <VBtn
            v-if="$can('create', 'Employee')"
            color="primary"
            prepend-icon="tabler-plus"
            @click="openAddDialog"
          >
            إضافة منصب
          </VBtn>
        </template>
      </VCardItem>
    </VCard>

    <!-- Table -->
    <VCard>
      <VDataTable
        :items="positions"
        :headers="[
          { title: 'المنصب الإداري', key: 'name' },
          { title: 'النقاط', key: 'points', sortable: false },
          { title: 'الترتيب', key: 'sort_order', sortable: false },
          { title: 'الحالة', key: 'is_active', sortable: false },
          { title: 'الإجراءات', key: 'actions', sortable: false, align: 'center' },
        ]"
        :loading="isLoading"
        class="text-no-wrap"
        item-value="id"
      >
        <!-- Name -->
        <template #item.name="{ item }">
          <span class="font-weight-medium">{{ item.name }}</span>
        </template>

        <!-- Points -->
        <template #item.points="{ item }">
          <VChip
            color="primary"
            variant="elevated"
            size="small"
            class="font-weight-bold"
          >
            {{ item.points }} نقطة
          </VChip>
        </template>

        <!-- Sort Order -->
        <template #item.sort_order="{ item }">
          <VBadge
            :content="item.sort_order"
            color="secondary"
            inline
          />
        </template>

        <!-- Status -->
        <template #item.is_active="{ item }">
          <VChip
            :color="item.is_active ? 'success' : 'error'"
            size="small"
            label
            variant="outlined"
          >
            {{ item.is_active ? 'نشط' : 'معطل' }}
          </VChip>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <div class="d-flex gap-1 justify-center">
            <VBtn
              v-if="$can('update', 'Employee')"
              icon
              size="small"
              variant="text"
              color="primary"
              @click="openEditDialog(item)"
              title="تعديل"
            >
              <VIcon icon="tabler-pencil" size="18" />
            </VBtn>
            <VBtn
              v-if="$can('delete', 'Employee')"
              icon
              size="small"
              variant="text"
              color="error"
              @click="deletePosition(item)"
              title="حذف"
            >
              <VIcon icon="tabler-trash" size="18" />
            </VBtn>
          </div>
        </template>

        <!-- Empty -->
        <template #no-data>
          <div class="py-8 text-center">
            <VIcon icon="tabler-inbox" size="48" class="text-disabled mb-2" />
            <div class="text-body-1 text-medium-emphasis">لا توجد عناوين إدارية</div>
          </div>
        </template>
      </VDataTable>
    </VCard>

    <!-- Dialog -->
    <VDialog v-model="isDialogVisible" max-width="480" persistent>
      <VCard>
        <VCardTitle class="text-h5 py-4 px-5">
          {{ dialogTitle }}
        </VCardTitle>
        <VDivider />
        <VCardText class="px-5 py-4">
          <VRow dense>
            <VCol cols="12">
              <AppTextField
                v-model="name"
                label="اسم المنصب الإداري *"
                placeholder="مثال: مدير عام، رئيس قسم..."
                :rules="[v => !!v || 'الاسم مطلوب']"
              />
            </VCol>
            <VCol cols="12" sm="6">
              <AppTextField
                v-model="points"
                label="عدد النقاط *"
                type="number"
                min="0"
                :rules="[v => v !== '' && Number(v) >= 0 || 'النقاط مطلوبة']"
              />
            </VCol>
            <VCol cols="12" sm="6">
              <AppTextField
                v-model="sortOrder"
                label="الترتيب"
                type="number"
                min="0"
              />
            </VCol>
            <VCol cols="12">
              <VSwitch
                v-model="isActive"
                label="نشط"
                color="success"
                inset
              />
            </VCol>
          </VRow>
        </VCardText>
        <VDivider />
        <VCardActions class="px-5 py-3 justify-end gap-2">
          <VBtn variant="outlined" @click="closeDialog">
            إلغاء
          </VBtn>
          <VBtn
            color="primary"
            :loading="isSaving"
            @click="savePosition"
          >
            حفظ
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Snackbar -->
    <VSnackbar
      v-model="isSnackbarVisible"
      :color="snackbarColor"
      location="top end"
      variant="flat"
    >
      {{ snackbarMessage }}
    </VSnackbar>
  </section>
</template>
