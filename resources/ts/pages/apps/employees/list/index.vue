<script setup lang="ts">
import AddNewEmployeeDrawer from '@/views/apps/employees/list/AddNewEmployeeDrawer.vue'
import EmployeeProfileDialog from '@/views/apps/employees/list/EmployeeProfileDialog.vue'
import ExportFieldsDialog from '@/views/apps/employees/list/ExportFieldsDialog.vue'
import type { Employee } from '@db/apps/employees/types'
import { showPermissionError } from '@/utils/api'
import { useConfirmDelete } from '@/composables/useConfirmDelete'
import { useRoute } from 'vue-router'
import * as XLSX from 'xlsx'

const route = useRoute()

definePage({
  meta: {
    action: 'read',
    subject: 'Employee',
  },
})

// 👉 Search & Filters
const searchQuery = ref('')
const selectedGender = ref()
const selectedJobType = ref()
const selectedDegree = ref()
const selectedEmployeeType = ref()
const branchIdFromQuery = computed(() => {
  const b = route.query.branchId
  return b ? Number(b) : undefined
})

// 👉 Data Table Options
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const selectedRows = ref([])

const updateOptions = (options: any) => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

const allColumns = [
  { title: 'الاسم الكامل', key: 'name', sortable: true },
  { title: 'الجنس', key: 'gender', sortable: false },
  { title: 'الدرجة', key: 'degree', sortable: false },
  { title: 'العنوان الوظيفي', key: 'rank', sortable: false },
  { title: 'نوع الموظف', key: 'jobTrack', sortable: false },
  { title: 'الشهادة', key: 'education', sortable: false },
  { title: 'نوع الوظيفة', key: 'jobType', sortable: false },
  { title: 'الرقم الإنتاجي', key: 'productionNo', sortable: false },
  { title: 'تاريخ التعيين', key: 'hireDate', sortable: true },
  { title: 'الهاتف', key: 'phone', sortable: false },
  { title: 'المواليد', key: 'birthDate', sortable: true },
  { title: 'البطاقة الوطنية', key: 'nationalId', sortable: false },
  { title: 'العنوان', key: 'address', sortable: false },
]

const visibleColumnKeys = ref<string[]>(['name', 'gender', 'degree', 'rank', 'education', 'jobType', 'hireDate', 'actions'])

const headers = computed(() => [
  ...allColumns.filter(c => visibleColumnKeys.value.includes(c.key)),
  { title: 'الإجراءات', key: 'actions', sortable: false },
])

// 👉 Fetch Employees
const { data: employeesData, execute: fetchEmployees } = await useApi<any>(createUrl('/apps/employees', {
  query: {
    q: searchQuery,
    gender: selectedGender,
    jobType: selectedJobType,
    degree: selectedDegree,
    employeeType: selectedEmployeeType,
    branchId: branchIdFromQuery,
    itemsPerPage,
    page,
    sortBy,
    orderBy,
  },
}))

const employees = computed((): Employee[] => {
  const data = employeesData.value
  return data && Array.isArray(data.employees) ? data.employees : []
})
const totalEmployees = computed(() => {
  const data = employeesData.value
  return data && typeof data.totalEmployees === 'number' ? data.totalEmployees : 0
})

// 👉 Stats
const totalMale = computed(() => employees.value.filter(e => e.gender === 'male').length)
const totalFemale = computed(() => employees.value.filter(e => e.gender === 'female').length)
const totalPermanent = computed(() => employees.value.filter(e => e.jobType === 'permanent').length)
const totalContract = computed(() => employees.value.filter(e => e.jobType === 'contract').length)
const totalDailyWage = computed(() => employees.value.filter(e => e.jobType === 'daily_wage').length)

// 👉 Filters
const genderOptions = [
  { title: 'ذكر', value: 'male' },
  { title: 'أنثى', value: 'female' },
]

const jobTypeOptions = [
  { title: 'تعيين ملاك', value: 'permanent' },
  { title: 'عقد', value: 'contract' },
  { title: 'أجر يومي', value: 'daily_wage' },
]

const degreeOptions = [
  'الخاصة', 'الأولى', 'الثانية', 'الثالثة', 'الرابعة', 'الخامسة', 'السادسة', 'السابعة',
]

const employeeTypeOptions = [
  { title: 'منتج', value: 'producer' },
  { title: 'إداري', value: 'admin' },
  { title: 'إداري منتج', value: 'admin_producer' },
]

// 👉 Helpers
const getFullName = (emp: Employee) =>
  [emp.firstName, emp.secondName, emp.thirdName, emp.fourthName, emp.lastName]
    .filter(Boolean)
    .join(' ') || '-'


const resolveGenderVariant = (gender: string) =>
  gender === 'male' ? { color: 'info', label: 'ذكر', icon: 'tabler-man' }
    : { color: 'error', label: 'أنثى', icon: 'tabler-woman' }

const resolveJobTypeVariant = (jobType: string) => {
  if (jobType === 'permanent')
    return { color: 'success', label: 'تعيين ملاك' }
  if (jobType === 'contract')
    return { color: 'warning', label: 'عقد' }

  return { color: 'secondary', label: 'أجر يومي' }
}

const formatDate = (date: string) => {
  if (!date)
    return '-'
  const [year, month, day] = date.split('-')

  return `${day}/${month}/${year}`
}

// 👉 Export
const exportFields = [
  { key: 'name', title: 'الاسم الكامل', default: true },
  { key: 'gender', title: 'الجنس', default: true },
  { key: 'degree', title: 'الدرجة', default: true },
  { key: 'rank', title: 'العنوان الوظيفي', default: true },
  { key: 'jobTrack', title: 'نوع الموظف', default: true },
  { key: 'education', title: 'الشهادة', default: false },
  { key: 'jobType', title: 'نوع الوظيفة', default: true },
  { key: 'productionNo', title: 'الرقم الإنتاجي', default: true },
  { key: 'hireDate', title: 'تاريخ التعيين', default: true },
  { key: 'phone', title: 'الهاتف', default: false },
  { key: 'birthDate', title: 'المواليد', default: false },
  { key: 'nationalId', title: 'البطاقة الوطنية', default: false },
  { key: 'address', title: 'العنوان', default: false },
  { key: 'branch', title: 'الفرع', default: false },
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
  const allFieldsMap: Record<string, (e: Employee) => [string, string]> = {
    name: e => ['الاسم الكامل', [e.firstName, e.secondName, e.thirdName, e.fourthName, e.lastName].filter(Boolean).join(' ') || '-'],
    gender: e => ['الجنس', e.gender === 'male' ? 'ذكر' : 'أنثى'],
    degree: e => ['الدرجة', e.degree || ''],
    rank: e => ['العنوان الوظيفي', e.rank || ''],
    jobTrack: e => ['نوع الموظف', e.jobTrack === 'producer' ? 'منتج' : 'إداري'],
    education: e => ['الشهادة', e.education || ''],
    jobType: e => ['نوع الوظيفة', e.jobType === 'permanent' ? 'تعيين ملاك' : e.jobType === 'contract' ? 'عقد' : 'أجر يومي'],
    productionNo: e => ['الرقم الإنتاجي', e.productionNo || ''],
    hireDate: e => ['تاريخ التعيين', e.hireDate || ''],
    phone: e => ['الهاتف', e.phone || ''],
    birthDate: e => ['المواليد', e.birthDate || ''],
    nationalId: e => ['البطاقة الوطنية', e.nationalId || ''],
    address: e => ['العنوان', e.address || ''],
    branch: e => ['الفرع', e.branch?.name || ''],
  }

  const data = employees.value.map(e => {
    const obj: Record<string, string> = {}
    selectedFields.forEach(key => {
      const [label, value] = allFieldsMap[key](e)
      obj[label] = value
    })
    return obj
  })

  const ws = XLSX.utils.json_to_sheet(data)
  const wb = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(wb, ws, 'الموظفون')
  XLSX.writeFile(wb, `الموظفون_${new Date().toISOString().split('T')[0]}.xlsx`)
}

const exportToPDF = async (selectedFields: string[]) => {
  const baseUrl = import.meta.env.VITE_API_BASE_URL || '/api'
  const params = new URLSearchParams()

  if (searchQuery.value)
    params.append('q', searchQuery.value)
  if (selectedGender.value)
    params.append('gender', selectedGender.value)
  if (selectedJobType.value)
    params.append('jobType', selectedJobType.value)
  if (selectedDegree.value)
    params.append('degree', selectedDegree.value)
  if (selectedEmployeeType.value)
    params.append('employeeType', selectedEmployeeType.value)

  params.append('fields', selectedFields.join(','))

  const accessToken = useCookie('accessToken').value
  const url = `${baseUrl}/apps/employees/export-pdf?${params.toString()}`

  try {
    const response = await fetch(url, {
      headers: accessToken ? { Authorization: `Bearer ${accessToken}` } : {},
    })

    if (!response.ok) {
      showSnackbar('فشل تصدير PDF', 'error')
      return
    }

    const blob = await response.blob()
    const downloadUrl = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = downloadUrl
    a.download = `قائمة_الموظفين_${new Date().toISOString().split('T')[0]}.pdf`
    document.body.appendChild(a)
    a.click()
    a.remove()
    window.URL.revokeObjectURL(downloadUrl)
  }
  catch {
    showSnackbar('فشل تصدير PDF', 'error')
  }
}

// 👉 Widget Data
const widgetData = computed(() => [
  { title: 'إجمالي الموظفين', value: totalEmployees.value, icon: 'tabler-users', iconColor: 'primary' },
  { title: 'الموظفون الذكور', value: totalMale.value, icon: 'tabler-man', iconColor: 'info' },
  { title: 'الموظفات الإناث', value: totalFemale.value, icon: 'tabler-woman', iconColor: 'error' },
])

// 👉 Drawer
const isEmployeeDialogVisible = ref(false)
const selectedEmployee = ref<Employee | null>(null)

const openAddDrawer = () => {
  selectedEmployee.value = null
  isEmployeeDialogVisible.value = true
}

const openEditDrawer = (emp: Employee) => {
  selectedEmployee.value = emp
  isEmployeeDialogVisible.value = true
}

const isProfileDialogVisible = ref(false)

const openProfileDialog = (emp: Employee) => {
  selectedEmployee.value = emp
  isProfileDialogVisible.value = true
}

const isSnackbarVisible = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref('success')

const showSnackbar = (message: string, color = 'success') => {
  snackbarMessage.value = message
  snackbarColor.value = color
  isSnackbarVisible.value = true
}

const saveEmployee = async (employeeData: Employee) => {
  try {
    if (employeeData.id) {
      await $api(`/apps/employees/${employeeData.id}`, {
        method: 'POST',
        body: { ...employeeData, _method: 'PUT' },
      })
      showSnackbar('تم تحديث بيانات الموظف بنجاح')
    }
    else {
      await $api('/apps/employees', {
        method: 'POST',
        body: employeeData,
      })
      showSnackbar('تم إضافة الموظف بنجاح')
    }
  } catch (error: any) {
    if (!showPermissionError(error))
      showSnackbar('حدث خطأ أثناء الحفظ: ' + (error.message || ''), 'error')
    return
  }

  try {
    await fetchEmployees()
  } catch (fetchError: any) {
    console.error('Fetch error after save:', fetchError)
    showSnackbar('تم الحفظ ولكن فشل تحديث القائمة. أعد تحميل الصفحة.', 'warning')
  }
}

const { open: openConfirmDelete } = useConfirmDelete()

const deleteEmployee = (employee: Employee) => {
  openConfirmDelete({
    id: employee.id,
    name: employee.name,
    title: 'حذف موظف',
    confirmLabel: 'نعم، احذف الموظف',
    async onConfirm() {
      await $api(`/apps/employees/${employee.id}`, { method: 'DELETE' })
      const index = selectedRows.value.findIndex((row: any) => row === employee.id)
      if (index !== -1)
        selectedRows.value.splice(index, 1)
      showSnackbar('تم حذف الموظف بنجاح')
      fetchEmployees()
    },
  })
}
</script>

<template>
  <section>
    <!-- 👉 Widgets -->
    <VRow class="mb-6">
      <VCol
        v-for="(widget, idx) in widgetData"
        :key="idx"
        cols="12"
        sm="6"
        lg="3"
      >
        <VCard class="h-100">
          <VCardText class="d-flex flex-column justify-space-between h-100">
            <div class="d-flex justify-space-between">
              <div class="d-flex flex-column gap-y-1">
                <span class="text-body-1 text-high-emphasis">{{ widget.title }}</span>
                <h4 class="text-h4">
                  {{ widget.value }}
                </h4>
              </div>
              <VAvatar
                :color="widget.iconColor"
                variant="tonal"
                rounded
                size="42"
              >
                <VIcon
                  :icon="widget.icon"
                  size="26"
                />
              </VAvatar>
            </div>
          </VCardText>
        </VCard>
      </VCol>

      <!-- صنف الوظيفة -->
      <VCol
        cols="12"
        sm="6"
        lg="3"
      >
        <VCard class="h-100">
          <VCardText class="d-flex flex-column justify-space-between h-100">
            <div class="d-flex justify-space-between align-center">
              <span class="text-body-1 text-high-emphasis">صنف الوظيفة</span>
              <VAvatar
                color="success"
                variant="tonal"
                rounded
                size="42"
              >
                <VIcon icon="tabler-id-badge" size="26" />
              </VAvatar>
            </div>
            <div class="d-flex justify-center gap-6">
              <div class="d-flex flex-column align-center">
                <span class="text-caption text-medium-emphasis">تعيين ملاك</span>
                <strong class="text-h6 text-success">{{ totalPermanent }}</strong>
              </div>
              <div class="d-flex flex-column align-center">
                <span class="text-caption text-medium-emphasis">عقد</span>
                <strong class="text-h6 text-warning">{{ totalContract }}</strong>
              </div>
              <div class="d-flex flex-column align-center">
                <span class="text-caption text-medium-emphasis">أجر يومي</span>
                <strong class="text-h6 text-info">{{ totalDailyWage }}</strong>
              </div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- 👉 Filters & Table -->
    <VCard>
      <VCardItem class="pb-4">
        <VCardTitle>الموظفون</VCardTitle>
      </VCardItem>

      <VCardText>
        <VRow align="end">
          <!-- Filter: Gender -->
          <VCol
            cols="12"
            sm="6"
            md="3"
          >
            <AppSelect
              v-model="selectedGender"
              placeholder="فلتر: الجنس"
              :items="genderOptions"
              clearable
              clear-icon="tabler-x"
            />
          </VCol>

          <!-- Filter: Job Type -->
          <VCol
            cols="12"
            sm="6"
            md="3"
          >
            <AppSelect
              v-model="selectedJobType"
              placeholder="فلتر: نوع الوظيفة"
              :items="jobTypeOptions"
              clearable
              clear-icon="tabler-x"
            />
          </VCol>

          <!-- Filter: Employee Type (Producer/Admin) -->
          <VCol
            cols="12"
            sm="6"
            md="3"
          >
            <AppSelect
              v-model="selectedEmployeeType"
              placeholder="فلتر: نوع الموظف"
              :items="employeeTypeOptions"
              clearable
              clear-icon="tabler-x"
            />
          </VCol>

          <!-- Filter: Degree -->
          <VCol
            cols="12"
            sm="6"
            md="3"
          >
            <AppSelect
              v-model="selectedDegree"
              placeholder="فلتر: الدرجة"
              :items="degreeOptions"
              clearable
              clear-icon="tabler-x"
            />
          </VCol>
        </VRow>

        <VRow class="mt-3" align="center">
          <VCol cols="12" md="6" sm="6">
            <!-- Search below filters -->
            <AppTextField
              v-model="searchQuery"
              placeholder="بحث بالاسم"
              prepend-inner-icon="tabler-search"
              density="compact"
            />
          </VCol>
          <VCol cols="12" md="6" sm="6" class="d-flex justify-end gap-2">
            <AppSelect
              :model-value="itemsPerPage"
              :items="[
                { value: 10, title: '10' },
                { value: 25, title: '25' },
                { value: 50, title: '50' },
                { value: 100, title: '100' },
                { value: -1, title: 'الكل' },
              ]"
              style="inline-size: 6.25rem;"
              density="compact"
              @update:model-value="itemsPerPage = parseInt($event, 10)"
            />

            <!-- Column Selector -->
            <VMenu location="bottom end" :close-on-content-click="false">
              <template #activator="{ props }">
                <VBtn
                  v-bind="props"
                  icon
                  size="small"
                  variant="text"
                  color="secondary"
                  title="الأعمدة"
                >
                  <VIcon icon="tabler-columns" size="20" />
                </VBtn>
              </template>
              <VCard min-width="220">
                <VCardText class="pa-2">
                  <div
                    v-for="col in allColumns"
                    :key="col.key"
                    class="d-flex align-center py-1"
                  >
                    <VCheckbox
                      v-model="visibleColumnKeys"
                      :value="col.key"
                      :label="col.title"
                      density="compact"
                      hide-details
                    />
                  </div>
                </VCardText>
              </VCard>
            </VMenu>

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
              v-if="$can('create', 'Employee')"
              size="small"
              prepend-icon="tabler-plus"
              @click="openAddDrawer"
            >
              إضافة موظف
            </VBtn>
          </VCol>
        </VRow>
      </VCardText>

      <VDivider />

      <!-- Data Table -->
      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:model-value="selectedRows"
        v-model:page="page"
        :items="employees"
        item-value="id"
        :items-length="totalEmployees"
        :headers="headers"
        class="text-no-wrap"
        show-select
        @update:options="updateOptions"
      >
        <!-- الاسم الكامل -->
        <template #item.name="{ item }">
          <span class="font-weight-medium text-high-emphasis">{{ getFullName(item) }}</span>
        </template>

        <!-- الجنس -->
        <template #item.gender="{ item }">
          <VChip
            :color="resolveGenderVariant(item.gender).color"
            size="small"
            label
          >
            <VIcon
              start
              :icon="resolveGenderVariant(item.gender).icon"
              size="14"
            />
            {{ resolveGenderVariant(item.gender).label }}
          </VChip>
        </template>

        <!-- نوع الوظيفة -->
        <template #item.jobType="{ item }">
          <VChip
            :color="resolveJobTypeVariant(item.jobType).color"
            size="small"
            label
          >
            {{ resolveJobTypeVariant(item.jobType).label }}
          </VChip>
        </template>

        <!-- نوع الموظف -->
        <template #item.jobTrack="{ item }">
          <span>{{ item.jobTrack === 'producer' ? 'منتج' : 'إداري' }}</span>
        </template>

        <!-- الرقم الإنتاجي -->
        <template #item.productionNo="{ item }">
          <span>{{ item.productionNo || '-' }}</span>
        </template>

        <!-- الهاتف -->
        <template #item.phone="{ item }">
          <span>{{ item.phone || '-' }}</span>
        </template>

        <!-- المواليد -->
        <template #item.birthDate="{ item }">
          <span>{{ formatDate(item.birthDate) }}</span>
        </template>

        <!-- البطاقة الوطنية -->
        <template #item.nationalId="{ item }">
          <span>{{ item.nationalId || '-' }}</span>
        </template>

        <!-- العنوان -->
        <template #item.address="{ item }">
          <span class="text-truncate" style="max-inline-size: 150px;">{{ item.address || '-' }}</span>
        </template>

        <!-- تاريخ التعيين -->
        <template #item.hireDate="{ item }">
          <span>{{ formatDate(item.hireDate) }}</span>
        </template>

        <!-- الإجراءات -->
        <template #item.actions="{ item }">
          <div class="d-flex gap-1 justify-center">
            <VBtn
              icon
              size="small"
              variant="text"
              color="info"
              @click="openProfileDialog(item)"
              title="عرض البيانات"
            >
              <VIcon icon="tabler-eye" size="18" />
            </VBtn>
            <VBtn
              v-if="$can('update', 'Employee')"
              icon
              size="small"
              variant="text"
              color="primary"
              @click="openEditDrawer(item)"
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
              @click="deleteEmployee(item)"
              title="حذف"
            >
              <VIcon icon="tabler-trash" size="18" />
            </VBtn>
          </div>
        </template>

        <!-- Pagination -->
        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalEmployees"
          />
        </template>
      </VDataTableServer>
    </VCard>

    <!-- 👉 Dialog -->
    <AddNewEmployeeDrawer
      v-model:is-dialog-visible="isEmployeeDialogVisible"
      :employee-to-edit="selectedEmployee"
      @employee-data="saveEmployee"
    />

    <EmployeeProfileDialog
      v-model:is-dialog-visible="isProfileDialogVisible"
      :employee="selectedEmployee"
    />

    <ExportFieldsDialog
      v-model="isExportDialogVisible"
      :fields="exportFields"
      @export="handleExport"
    />

    <!-- 👉 Snackbar -->
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
