<script setup lang="ts">
import AddNewBranchDrawer from '@/views/apps/branches/list/AddNewBranchDrawer.vue'
import ExportFieldsDialog from '@/views/apps/employees/list/ExportFieldsDialog.vue'
import type { Branch, BranchWithNames } from '@db/apps/branches/types'
import { showPermissionError } from '@/utils/api'
import { useConfirmDelete } from '@/composables/useConfirmDelete'
import { useRouter } from 'vue-router'
import * as XLSX from 'xlsx'

const router = useRouter()

definePage({
  meta: {
    action: 'read',
    subject: 'Branch',
  },
})

// 👉 Search & Filters
const searchQuery = ref('')
const selectedGovernorate = ref()

// 👉 Data Table Options
const itemsPerPage = ref(10)
const page = ref(1)

const headers = [
  { title: 'اسم الفرع', key: 'name', sortable: true },
  { title: 'المكان', key: 'location', sortable: false },
  { title: 'المحافظة', key: 'governorate', sortable: true },
  { title: 'مدير الفرع', key: 'managerName', sortable: false },
  { title: 'المعاون', key: 'deputyName', sortable: false },
  { title: 'الإجراءات', key: 'actions', sortable: false },
]

const governorates = [
  'الإدارة العامة', 'بغداد', 'البصرة', 'نينوى', 'أربيل', 'كربلاء', 'النجف',
  'ذي قار', 'المثنى', 'القادسية', 'ميسان', 'بابل', 'ديالى', 'الأنبار',
  'صلاح الدين', 'كركوك', 'واسط', 'السليمانية', 'دهوك',
]

// 👉 Fetch Branches
const { data: branchesData, execute: fetchBranches } = await useApi<any>(createUrl('/apps/branches', {
  query: {
    q: searchQuery,
    governorate: selectedGovernorate,
    itemsPerPage,
    page,
  },
}))

const branches = computed(() => branchesData.value?.branches ?? [])
const totalBranches = computed(() => branchesData.value?.totalBranches ?? 0)

// 👉 Drawer
const isBranchDrawerVisible = ref(false)
const selectedBranch = ref<Branch | null>(null)

const openAddDrawer = () => {
  selectedBranch.value = null
  isBranchDrawerVisible.value = true
}

const openEditDrawer = (branch: BranchWithNames) => {
  selectedBranch.value = {
    id: branch.id,
    name: branch.name,
    location: branch.location,
    governorate: branch.governorate,
    managerId: branch.managerId,
    deputyId: branch.deputyId,
  }
  isBranchDrawerVisible.value = true
}

// 👉 Notification State
const isSnackbarVisible = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref('success')

const showNotification = (message: string, color = 'success') => {
  snackbarMessage.value = message
  snackbarColor.value = color
  isSnackbarVisible.value = true
}

const saveBranch = async (branchData: Branch) => {
  try {
    if (branchData.id) {
      await $api(`/apps/branches/${branchData.id}`, {
        method: 'PUT',
        body: branchData,
      })
      showNotification('تم تحديث بيانات الفرع بنجاح')
    }
    else {
      await $api('/apps/branches', {
        method: 'POST',
        body: branchData,
      })
      showNotification('تم إضافة الفرع بنجاح')
    }
    fetchBranches()
  } catch (err: any) {
    console.error('Branch Save Error:', err)
    if (!showPermissionError(err)) {
      const errorDetail = err.response?._data?.message || err.message || 'خطأ غير معروف'
      showNotification(`فشل الحفظ: ${errorDetail}`, 'error')
    }
  }
}

const goToBranchPlan = async (branchId: number) => {
  try {
    const res = await $api(`/apps/branches/${branchId}/production-plan`)
    router.push(`/apps/production-plans/${res.planId}`)
  } catch {
    router.push({ path: '/apps/production-plans/list', query: { branchId } })
  }
}

// 👉 Export
const exportFields = [
  { key: 'name', title: 'اسم الفرع', default: true },
  { key: 'location', title: 'المكان / العنوان', default: true },
  { key: 'governorate', title: 'المحافظة', default: true },
  { key: 'managerName', title: 'مدير الفرع', default: true },
  { key: 'deputyName', title: 'المعاون', default: true },
]

const isExportDialogVisible = ref(false)

const openExportDialog = () => {
  isExportDialogVisible.value = true
}

const handleExport = (type: 'pdf' | 'excel', selectedFields: string[]) => {
  if (type === 'excel')
    exportToExcel(selectedFields)
  else
    exportToPDF(selectedFields)
}

const exportToExcel = (selectedFields: string[]) => {
  const allFieldsMap: Record<string, (b: BranchWithNames) => [string, string]> = {
    name: b => ['اسم الفرع', b.name || ''],
    location: b => ['المكان / العنوان', b.location || ''],
    governorate: b => ['المحافظة', b.governorate || ''],
    managerName: b => ['مدير الفرع', b.managerName || ''],
    deputyName: b => ['المعاون', b.deputyName || ''],
  }

  const data = branches.value.map(b => {
    const obj: Record<string, string> = {}
    selectedFields.forEach(key => {
      const [label, value] = allFieldsMap[key](b)
      obj[label] = value
    })
    return obj
  })

  const ws = XLSX.utils.json_to_sheet(data)
  const wb = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(wb, ws, 'الفروع')
  XLSX.writeFile(wb, `الفروع_${new Date().toISOString().split('T')[0]}.xlsx`)
}

const exportToPDF = async (selectedFields: string[]) => {
  const baseUrl = import.meta.env.VITE_API_BASE_URL || '/api'
  const params = new URLSearchParams()
  if (searchQuery.value) params.append('q', searchQuery.value)
  if (selectedGovernorate.value) params.append('governorate', selectedGovernorate.value)
  params.append('fields', selectedFields.join(','))

  const accessToken = useCookie('accessToken').value
  const url = `${baseUrl}/apps/branches/export-pdf?${params.toString()}`

  try {
    const response = await fetch(url, {
      headers: accessToken ? { Authorization: `Bearer ${accessToken}` } : {},
    })

    if (!response.ok) {
      showNotification('فشل تصدير PDF', 'error')
      return
    }

    const blob = await response.blob()
    const downloadUrl = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = downloadUrl
    a.download = `قائمة_الفروع_${new Date().toISOString().split('T')[0]}.pdf`
    document.body.appendChild(a)
    a.click()
    a.remove()
    window.URL.revokeObjectURL(downloadUrl)
  }
  catch {
    showNotification('فشل تصدير PDF', 'error')
  }
}

const { open: openConfirmDelete } = useConfirmDelete()

const deleteBranch = (branch: BranchWithNames) => {
  openConfirmDelete({
    id: branch.id,
    name: branch.name,
    title: 'حذف فرع',
    confirmLabel: 'نعم، احذف الفرع',
    async onConfirm() {
      await $api(`/apps/branches/${branch.id}`, { method: 'DELETE' })
      showNotification('تم حذف الفرع بنجاح')
      fetchBranches()
    },
  })
}
</script>

<template>
  <section>
    <!-- 👉 Notification -->
    <VSnackbar
      v-model="isSnackbarVisible"
      :color="snackbarColor"
      location="top end"
      variant="flat"
    >
      <div class="d-flex align-center gap-2">
        <VIcon :icon="snackbarColor === 'success' ? 'tabler-circle-check' : 'tabler-alert-triangle'" color="white" />
        <span>{{ snackbarMessage }}</span>
      </div>
    </VSnackbar>

    <!-- 👉 Widget -->
    <VRow class="mb-6">
      <VCol
        cols="12"
        sm="6"
        lg="3"
      >
        <VCard>
          <VCardText>
            <div class="d-flex justify-space-between">
              <div class="d-flex flex-column gap-y-1">
                <span class="text-body-1 text-high-emphasis">إجمالي الفروع</span>
                <h4 class="text-h4">
                  {{ totalBranches }}
                </h4>
              </div>
              <VAvatar
                color="primary"
                variant="tonal"
                rounded
                size="42"
              >
                <VIcon
                  icon="tabler-building-community"
                  size="26"
                />
              </VAvatar>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- 👉 Table Card -->
    <VCard>
      <VCardItem class="pb-4">
        <VCardTitle>الفروع</VCardTitle>
      </VCardItem>

      <VCardText>
        <VRow>
          <!-- Filter: Governorate -->
          <VCol
            cols="12"
            sm="4"
          >
            <AppSelect
              v-model="selectedGovernorate"
              placeholder="فلتر: المحافظة"
              :items="governorates"
              clearable
              clear-icon="tabler-x"
            />
          </VCol>
        </VRow>
      </VCardText>

      <VDivider />

      <VCardText class="d-flex flex-wrap gap-4">
        <div class="me-3 d-flex gap-3">
          <AppSelect
            :model-value="itemsPerPage"
            :items="[
              { value: 10, title: '10' },
              { value: 25, title: '25' },
              { value: 50, title: '50' },
              { value: -1, title: 'الكل' },
            ]"
            style="inline-size: 6.25rem;"
            @update:model-value="itemsPerPage = parseInt($event, 10)"
          />
        </div>
        <VSpacer />

        <div class="d-flex align-center flex-wrap gap-4">
          <!-- Search -->
          <div style="inline-size: 15.625rem;">
            <AppTextField
              v-model="searchQuery"
              placeholder="بحث: اسم الفرع أو المحافظة"
              prepend-inner-icon="tabler-search"
            />
          </div>

          <!-- Export -->
          <VBtn
            variant="tonal"
            color="secondary"
            size="small"
            prepend-icon="tabler-upload"
            @click="openExportDialog"
          >
            تصدير
          </VBtn>

          <!-- Add New -->
          <VBtn
            v-if="$can('create', 'Branch')"
            prepend-icon="tabler-plus"
            @click="openAddDrawer"
          >
            إضافة فرع
          </VBtn>
        </div>
      </VCardText>

      <VDivider />

      <!-- Data Table -->
      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:page="page"
        :items="branches"
        item-value="id"
        :items-length="totalBranches"
        :headers="headers"
        class="text-no-wrap"
      >
        <!-- اسم الفرع -->
        <template #item.name="{ item }">
          <div class="d-flex align-center gap-x-3">
            <VAvatar
              color="primary"
              variant="tonal"
              size="34"
              rounded
            >
              <VIcon
                icon="tabler-building"
                size="18"
              />
            </VAvatar>
            <span class="font-weight-medium">{{ item.name }}</span>
          </div>
        </template>

        <!-- مدير الفرع -->
        <template #item.managerName="{ item }">
          <span v-if="item.managerName">{{ item.managerName }}</span>
          <VChip
            v-else
            color="warning"
            size="small"
            label
          >
            غير محدد
          </VChip>
        </template>

        <!-- المعاون -->
        <template #item.deputyName="{ item }">
          <span v-if="item.deputyName">{{ item.deputyName }}</span>
          <VChip
            v-else
            color="secondary"
            size="small"
            label
          >
            غير محدد
          </VChip>
        </template>

        <!-- الإجراءات -->
        <template #item.actions="{ item }">
          <VBtn
            icon
            variant="text"
            color="medium-emphasis"
          >
            <VIcon icon="tabler-dots-vertical" />
            <VMenu activator="parent">
              <VList>
                <!-- عرض الموظفين -->
                <VListItem :to="{ path: '/apps/employees/list', query: { branchId: item.id } }">
                  <template #prepend>
                    <VIcon icon="tabler-users" color="primary" />
                  </template>
                  <VListItemTitle>عرض الموظفين</VListItemTitle>
                </VListItem>

                <!-- خطة الإنتاج -->
                <VListItem @click="goToBranchPlan(item.id)">
                  <template #prepend>
                    <VIcon icon="tabler-chart-bar" color="success" />
                  </template>
                  <VListItemTitle>متابعة خطة الإنتاج</VListItemTitle>
                </VListItem>

                <VDivider class="my-1" />

                <!-- تعديل -->
                <VListItem v-if="$can('update', 'Branch')" @click="openEditDrawer(item)">
                  <template #prepend>
                    <VIcon icon="tabler-pencil" />
                  </template>
                  <VListItemTitle>تعديل البيانات</VListItemTitle>
                </VListItem>

                <!-- حذف -->
                <VListItem v-if="$can('delete', 'Branch')" @click="deleteBranch(item)">
                  <template #prepend>
                    <VIcon icon="tabler-trash" color="error" />
                  </template>
                  <VListItemTitle class="text-error">حذف الفرع</VListItemTitle>
                </VListItem>
              </VList>
            </VMenu>
          </VBtn>
        </template>

        <!-- Pagination -->
        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalBranches"
          />
        </template>
      </VDataTableServer>
    </VCard>

    <!-- 👉 Drawer -->
    <AddNewBranchDrawer
      v-model:is-drawer-open="isBranchDrawerVisible"
      :branch-to-edit="selectedBranch"
      @branch-data="saveBranch"
    />

    <ExportFieldsDialog
      v-model="isExportDialogVisible"
      :fields="exportFields"
      @export="handleExport"
    />
  </section>
</template>
