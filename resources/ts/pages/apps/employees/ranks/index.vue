<script setup lang="ts">
import { useConfirmDelete } from '@/composables/useConfirmDelete'
import { showPermissionError, $api } from '@/utils/api'

interface Rank {
  id: number
  name: string
  type: 'admin' | 'producer'
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
const ranks = ref<Rank[]>([])
const isLoading = ref(false)

// 👉 Dialog
const isDialogVisible = ref(false)
const editingRank = ref<Rank | null>(null)
const dialogTitle = computed(() => editingRank.value ? 'تعديل عنوان وظيفي' : 'إضافة عنوان وظيفي')

// 👉 Form
const name = ref('')
const type = ref<'admin' | 'producer'>('admin')
const sortOrder = ref(0)
const isActive = ref(true)
const isSaving = ref(false)

const typeOptions = [
  { title: 'إداري', value: 'admin' },
  { title: 'منتج', value: 'producer' },
]

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
const fetchRanks = async () => {
  isLoading.value = true
  try {
    const res = await $api('/apps/ranks')
    ranks.value = res
  } catch (e) {
    showSnackbar('فشل تحميل العناوين الوظيفية', 'error')
  } finally {
    isLoading.value = false
  }
}

// 👉 Dialog
const openAddDialog = () => {
  editingRank.value = null
  name.value = ''
  type.value = 'admin'
  sortOrder.value = 0
  isActive.value = true
  isDialogVisible.value = true
}

const openEditDialog = (rank: Rank) => {
  editingRank.value = rank
  name.value = rank.name
  type.value = rank.type
  sortOrder.value = rank.sort_order
  isActive.value = rank.is_active
  isDialogVisible.value = true
}

const closeDialog = () => {
  isDialogVisible.value = false
  editingRank.value = null
}

const saveRank = async () => {
  if (!name.value.trim()) {
    showSnackbar('الاسم مطلوب', 'error')
    return
  }

  isSaving.value = true
  try {
    const payload = {
      name: name.value.trim(),
      type: type.value,
      sort_order: Number(sortOrder.value),
      is_active: isActive.value,
    }

    if (editingRank.value) {
      await $api(`/apps/ranks/${editingRank.value.id}`, {
        method: 'POST',
        body: { ...payload, _method: 'PUT' },
      })
      showSnackbar('تم التعديل بنجاح')
    } else {
      await $api('/apps/ranks', {
        method: 'POST',
        body: payload,
      })
      showSnackbar('تم الإضافة بنجاح')
    }
    closeDialog()
    await fetchRanks()
  } catch (error: any) {
    if (!showPermissionError(error)) {
      showSnackbar(error.message || 'حدث خطأ أثناء الحفظ', 'error')
    }
  } finally {
    isSaving.value = false
  }
}

// 👉 Delete
const deleteRank = (rank: Rank) => {
  openConfirmDelete({
    name: rank.name,
    title: 'حذف العنوان الوظيفي',
    message: `هل أنت متأكد من حذف "${rank.name}"؟`,
    onConfirm: async () => {
      try {
        await $api(`/apps/ranks/${rank.id}`, { method: 'DELETE' })
        showSnackbar('تم الحذف بنجاح')
        await fetchRanks()
      } catch (error: any) {
        if (!showPermissionError(error)) {
          showSnackbar(error.message || 'حدث خطأ أثناء الحذف', 'error')
        }
      }
    },
  })
}

// 👉 Helpers
const resolveTypeColor = (t: string) => t === 'admin' ? 'primary' : 'success'
const resolveTypeLabel = (t: string) => t === 'admin' ? 'إداري' : 'منتج'

// 👉 Init
onMounted(fetchRanks)
</script>

<template>
  <section>
    <!-- Header -->
    <VCard class="mb-4">
      <VCardItem>
        <VCardTitle class="d-flex align-center gap-2">
          <VIcon icon="tabler-briefcase" size="24" />
          العناوين الوظيفية
        </VCardTitle>
        <template #append>
          <VBtn
            v-if="$can('create', 'Employee')"
            color="primary"
            prepend-icon="tabler-plus"
            @click="openAddDialog"
          >
            إضافة عنوان
          </VBtn>
        </template>
      </VCardItem>
    </VCard>

    <!-- Table -->
    <VCard>
      <VDataTable
        :items="ranks"
        :headers="[
          { title: 'العنوان الوظيفي', key: 'name' },
          { title: 'النوع', key: 'type', sortable: false },
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

        <!-- Type -->
        <template #item.type="{ item }">
          <VChip
            :color="resolveTypeColor(item.type)"
            size="small"
            label
          >
            {{ resolveTypeLabel(item.type) }}
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
              @click="deleteRank(item)"
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
            <div class="text-body-1 text-medium-emphasis">لا توجد عناوين وظيفية</div>
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
                label="اسم العنوان الوظيفي *"
                placeholder="مثال: مدير قسم، محاسب..."
                :rules="[v => !!v || 'الاسم مطلوب']"
              />
            </VCol>
            <VCol cols="12" sm="6">
              <AppSelect
                v-model="type"
                label="النوع"
                :items="typeOptions"
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
            @click="saveRank"
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
