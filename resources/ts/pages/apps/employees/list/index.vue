<script setup lang="ts">
import AddNewEmployeeDrawer from '@/views/apps/employees/list/AddNewEmployeeDrawer.vue'
import IncentiveCalculatorDialog from '@/views/apps/employees/list/IncentiveCalculatorDialog.vue'
import type { Employee } from '@db/apps/employees/types'

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

const headers = [
  { title: 'الرقم الوظيفي', key: 'employeeNo', sortable: true },
  { title: 'الاسم الكامل', key: 'name', sortable: true },
  { title: 'الجنس', key: 'gender', sortable: false },
  { title: 'الدرجة', key: 'degree', sortable: false },
  { title: 'العنوان الوظيفي', key: 'rank', sortable: false },
  { title: 'الشهادة', key: 'education', sortable: false },
  { title: 'نوع الوظيفة', key: 'jobType', sortable: false },
  { title: 'تاريخ التعيين', key: 'hireDate', sortable: true },
  { title: 'الإجراءات', key: 'actions', sortable: false },
]

// 👉 Fetch Employees
const { data: employeesData, execute: fetchEmployees } = await useApi<any>(createUrl('/apps/employees', {
  query: {
    q: searchQuery,
    gender: selectedGender,
    jobType: selectedJobType,
    degree: selectedDegree,
    itemsPerPage,
    page,
    sortBy,
    orderBy,
  },
}))

const employees = computed((): Employee[] => employeesData.value?.employees ?? [])
const totalEmployees = computed(() => employeesData.value?.totalEmployees ?? 0)

// 👉 Stats
const totalMale = computed(() => employees.value.filter(e => e.gender === 'male').length)
const totalFemale = computed(() => employees.value.filter(e => e.gender === 'female').length)
const totalPermanent = computed(() => employees.value.filter(e => e.jobType === 'permanent').length)

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

// 👉 Helpers
const getFullName = (emp: Employee) =>
  `${emp.firstName} ${emp.secondName} ${emp.thirdName} ${emp.fourthName} ${emp.lastName}`

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

// 👉 Widget Data
const widgetData = computed(() => [
  { title: 'إجمالي الموظفين', value: totalEmployees.value, icon: 'tabler-users', iconColor: 'primary' },
  { title: 'الموظفون الذكور', value: totalMale.value, icon: 'tabler-man', iconColor: 'info' },
  { title: 'الموظفات الإناث', value: totalFemale.value, icon: 'tabler-woman', iconColor: 'error' },
  { title: 'تعيين ملاك', value: totalPermanent.value, icon: 'tabler-id-badge', iconColor: 'success' },
])

// 👉 Drawer
const isEmployeeDialogVisible = ref(false)
const isIncentiveDialogVisible = ref(false)
const selectedEmployee = ref<Employee | null>(null)

const openAddDrawer = () => {
  selectedEmployee.value = null
  isEmployeeDialogVisible.value = true
}

const openEditDrawer = (emp: Employee) => {
  selectedEmployee.value = emp
  isEmployeeDialogVisible.value = true
}

const openIncentiveDialog = (emp: Employee) => {
  selectedEmployee.value = emp
  isIncentiveDialogVisible.value = true
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
    fetchEmployees()
  } catch (error: any) {
    showSnackbar('حدث خطأ أثناء الحفظ: ' + (error.message || ''), 'error')
  }
}

const deleteEmployee = async (id: number) => {
  try {
    await $api(`/apps/employees/${id}`, {
      method: 'DELETE',
    })
    const index = selectedRows.value.findIndex((row: any) => row === id)
    if (index !== -1)
      selectedRows.value.splice(index, 1)
    
    showSnackbar('تم حذف الموظف بنجاح')
    fetchEmployees()
  } catch (error: any) {
    showSnackbar('حدث خطأ أثناء الحذف', 'error')
  }
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
        <VCard>
          <VCardText>
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
    </VRow>

    <!-- 👉 Filters & Table -->
    <VCard>
      <VCardItem class="pb-4">
        <VCardTitle>الموظفون</VCardTitle>
      </VCardItem>

      <VCardText>
        <VRow>
          <!-- Filter: Gender -->
          <VCol
            cols="12"
            sm="4"
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
            sm="4"
          >
            <AppSelect
              v-model="selectedJobType"
              placeholder="فلتر: نوع الوظيفة"
              :items="jobTypeOptions"
              clearable
              clear-icon="tabler-x"
            />
          </VCol>

          <!-- Filter: Degree -->
          <VCol
            cols="12"
            sm="4"
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
              { value: 100, title: '100' },
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
              placeholder="بحث: الاسم أو الرقم الوظيفي"
              prepend-inner-icon="tabler-search"
            />
          </div>

          <!-- Export -->
          <VBtn
            variant="tonal"
            color="secondary"
            prepend-icon="tabler-upload"
          >
            تصدير
          </VBtn>

          <!-- Add New -->
          <VBtn
            v-if="$can('create', 'Employee')"
            prepend-icon="tabler-plus"
            @click="openAddDrawer"
          >
            إضافة موظف
          </VBtn>
        </div>
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
          <div class="d-flex flex-column">
            <span class="font-weight-medium text-high-emphasis">{{ getFullName(item) }}</span>
            <span class="text-sm text-medium-emphasis">{{ item.employeeNo }}</span>
          </div>
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

        <!-- تاريخ التعيين -->
        <template #item.hireDate="{ item }">
          <span>{{ formatDate(item.hireDate) }}</span>
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
                <VListItem @click="openIncentiveDialog(item)">
                  <template #prepend>
                    <VIcon icon="tabler-calculator" />
                  </template>
                  <VListItemTitle>احتساب الحوافز</VListItemTitle>
                </VListItem>
                <VListItem v-if="$can('update', 'Employee')" @click="openEditDrawer(item)">
                  <template #prepend>
                    <VIcon icon="tabler-pencil" />
                  </template>
                  <VListItemTitle>تعديل</VListItemTitle>
                </VListItem>
                <VListItem v-if="$can('delete', 'Employee')" @click="deleteEmployee(item.id)">
                  <template #prepend>
                    <VIcon icon="tabler-trash" />
                  </template>
                  <VListItemTitle>حذف</VListItemTitle>
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

    <IncentiveCalculatorDialog
      v-model:is-dialog-visible="isIncentiveDialogVisible"
      :employee="selectedEmployee"
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
