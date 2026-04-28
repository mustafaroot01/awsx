<script setup lang="ts">
import AddPolicyDrawer from '@/views/apps/policies/list/AddPolicyDrawer.vue'
import type { Policy } from '@db/apps/policies/types'
import { showPermissionError } from '@/utils/api'

definePage({
  meta: { action: 'read', subject: 'Policy' },
})

const router = useRouter()

const searchQuery = ref('')
const selectedCategory = ref<string | undefined>(undefined)
const selectedBranchId = ref<number | undefined>(undefined)
const selectedYear = ref<number | undefined>(undefined)

const itemsPerPage = ref(10)
const page = ref(1)

const { data: policiesData, execute: fetchPolicies } = await useApi<any>(createUrl('/apps/policies', {
  query: { q: searchQuery, category: selectedCategory, branchId: selectedBranchId, year: selectedYear, itemsPerPage, page },
}))

const { data: statsData } = await useApi<any>(createUrl('/apps/policies/stats', {
  query: { year: computed(() => selectedYear.value ?? new Date().getFullYear()) },
}))

const policies = computed((): Policy[] => policiesData.value?.policies ?? [])
const totalPolicies = computed(() => policiesData.value?.totalPolicies ?? 0)
const totalPages = computed(() => policiesData.value?.totalPages ?? 1)

const categoryLabels: Record<string, string> = {
  vehicle: 'تأمين السيارات',
  fire_theft: 'الحريق والسرقة',
  group_health: 'الصحي الجماعي',
  transport_marine: 'النقل / البحري',
  engineering: 'التأمين الهندسي',
  life: 'تأمين الحياة',
  personal_accident: 'الحوادث الشخصية',
  cash: 'تأمين النقد',
}

const categoryColors: Record<string, string> = {
  vehicle: 'primary',
  fire_theft: 'error',
  group_health: 'info',
  transport_marine: 'secondary',
  engineering: 'warning',
  life: 'success',
  personal_accident: 'orange',
  cash: 'purple',
}

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

const branchOptions = ref<{ title: string; value: number }[]>([])
const yearOptions = Array.from({ length: 6 }, (_, i) => {
  const y = new Date().getFullYear() - i
  return { title: String(y), value: y }
})

onMounted(async () => {
  const result = await $api<any>('/apps/branches?itemsPerPage=-1')
  branchOptions.value = (result?.branches ?? []).map((b: any) => ({ title: b.name, value: b.id }))
})

const openAddDrawer  = () => { router.push('/apps/policies/add') }
const openEditDrawer = (p: Policy) => { router.push(`/apps/policies/add?id=${p.id}`) }

const savePolicy = async (policyData: any) => {
  try {
    if (policyData.id) {
      await $api(`/apps/policies/${policyData.id}`, { method: 'PUT', body: policyData })
    } else {
      await $api('/apps/policies', { method: 'POST', body: policyData })
    }
    fetchPolicies()
  } catch (e) {
    showPermissionError(e)
  }
}

const deletePolicy = async (id: number) => {
  try {
    await $api(`/apps/policies/${id}`, { method: 'DELETE' })
    fetchPolicies()
  } catch (e) {
    showPermissionError(e)
  }
}

const downloadPdf = (policy: Policy) => {
  // Direct link to the PDF download route
  const url = `/api/apps/policies/${policy.id}/download-pdf`
  window.open(url, '_blank')
}

const formatCurrency = (v: number) =>
  new Intl.NumberFormat('ar-IQ', { style: 'decimal', maximumFractionDigits: 0 }).format(v) + ' د.ع'

const formatDate = (d: string) => {
  if (!d) return '-'
  const [y, m, day] = d.split('-')
  return `${day}/${m}/${y}`
}

const totalAmountThisYear = computed(() => statsData.value?.totalAmount ?? 0)
const totalCountThisYear = computed(() => statsData.value?.totalCount ?? 0)

const widgetData = computed(() => [
  { title: 'إجمالي الوثائق', value: totalPolicies.value, icon: 'tabler-file-certificate', color: 'primary' },
  { title: `وثائق ${selectedYear.value ?? new Date().getFullYear()}`, value: totalCountThisYear.value, icon: 'tabler-calendar-stats', color: 'info' },
  { title: 'إجمالي الأقساط', value: formatCurrency(totalAmountThisYear.value), icon: 'tabler-cash', color: 'success' },
])
</script>

<template>
  <section>
    <!-- Widgets -->
    <VRow class="mb-6">
      <VCol v-for="(w, i) in widgetData" :key="i" cols="12" sm="6" lg="4">
        <VCard>
          <VCardText>
            <div class="d-flex justify-space-between">
              <div class="d-flex flex-column gap-y-1">
                <span class="text-body-1 text-high-emphasis">{{ w.title }}</span>
                <h4 class="text-h4">{{ w.value }}</h4>
              </div>
              <VAvatar :color="w.color" variant="tonal" rounded size="42">
                <VIcon :icon="w.icon" size="26" />
              </VAvatar>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Table Card -->
    <VCard>
      <VCardItem class="pb-4">
        <VCardTitle>وثائق التأمين</VCardTitle>
      </VCardItem>

      <VCardText>
        <VRow>
          <VCol cols="12" sm="3">
            <AppSelect
              v-model="selectedCategory"
              placeholder="فلتر: الفئة"
              :items="categoryOptions"
              clearable
              clear-icon="tabler-x"
            />
          </VCol>
          <VCol cols="12" sm="3">
            <AppSelect
              v-model="selectedBranchId"
              placeholder="فلتر: الفرع"
              :items="branchOptions"
              clearable
              clear-icon="tabler-x"
            />
          </VCol>
          <VCol cols="12" sm="3">
            <AppSelect
              v-model="selectedYear"
              placeholder="فلتر: السنة"
              :items="yearOptions"
              clearable
              clear-icon="tabler-x"
            />
          </VCol>
        </VRow>
      </VCardText>

      <VDivider />

      <VCardText class="d-flex flex-wrap gap-4">
        <div class="me-3">
          <AppSelect
            :model-value="itemsPerPage"
            :items="[{value:10,title:'10'},{value:25,title:'25'},{value:50,title:'50'},{value:-1,title:'الكل'}]"
            style="inline-size:6.25rem"
            @update:model-value="itemsPerPage = parseInt($event,10)"
          />
        </div>
        <VSpacer />
        <div class="d-flex align-center flex-wrap gap-4">
          <div style="inline-size:15.625rem">
            <AppTextField
              v-model="searchQuery"
              placeholder="بحث: رقم الوثيقة أو اسم العميل"
              prepend-inner-icon="tabler-search"
            />
          </div>
          <VBtn v-if="$can('create', 'Policy')" prepend-icon="tabler-plus" @click="openAddDrawer">إضافة وثيقة</VBtn>
        </div>
      </VCardText>

      <VDivider />

      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:page="page"
        :items="policies"
        item-value="id"
        :items-length="totalPolicies"
        :headers="[
          { title: 'رقم الوثيقة', key: 'policyNo' },
          { title: 'الفئة', key: 'category' },
          { title: 'اسم العميل', key: 'clientName' },
          { title: 'المبلغ', key: 'amount' },
          { title: 'الفرع', key: 'branchName' },
          { title: 'تاريخ الإصدار', key: 'issueDate' },
          { title: 'تاريخ الانتهاء', key: 'expiryDate' },
          { title: 'الإجراءات', key: 'actions', sortable: false },
        ]"
        class="text-no-wrap"
      >
        <template #item.policyNo="{ item }">
          <div class="d-flex align-center gap-2">
            <VAvatar
              :color="categoryColors[item.category] ?? 'primary'"
              variant="tonal"
              size="32"
              rounded
            >
              <VIcon icon="tabler-file-certificate" size="16" />
            </VAvatar>
            <span class="font-weight-medium">{{ item.policyNo }}</span>
          </div>
        </template>

        <template #item.category="{ item }">
          <VChip
            :color="categoryColors[item.category] ?? 'secondary'"
            size="small"
            label
          >
            {{ categoryLabels[item.category] ?? item.category }}
          </VChip>
        </template>

        <template #item.amount="{ item }">
          <span class="font-weight-medium text-success">{{ formatCurrency(item.amount) }}</span>
        </template>

        <template #item.issueDate="{ item }">
          {{ formatDate(item.issueDate) }}
        </template>

        <template #item.expiryDate="{ item }">
          {{ formatDate(item.expiryDate) }}
        </template>

        <template #item.actions="{ item }">
          <div class="d-flex align-center gap-1">
            <VBtn
              v-if="$can('print', 'Policy')"
              icon
              variant="text"
              color="primary"
              size="small"
              @click="downloadPdf(item)"
            >
              <VIcon icon="tabler-printer" size="22" />
              <VTooltip activator="parent">طباعة الوثيقة</VTooltip>
            </VBtn>

            <VBtn icon variant="text" color="medium-emphasis" size="small">
              <VIcon icon="tabler-dots-vertical" />
              <VMenu activator="parent">
                <VList>
                  <VListItem v-if="$can('update', 'Policy')" @click="openEditDrawer(item)">
                    <template #prepend><VIcon icon="tabler-pencil" color="warning" /></template>
                    <VListItemTitle>تعديل الوثيقة</VListItemTitle>
                  </VListItem>
                  <VListItem v-if="$can('delete', 'Policy')" @click="deletePolicy(item.id)">
                    <template #prepend><VIcon icon="tabler-trash" /></template>
                    <VListItemTitle>حذف</VListItemTitle>
                  </VListItem>
                </VList>
              </VMenu>
            </VBtn>
          </div>
        </template>

        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalPolicies"
          />
        </template>
      </VDataTableServer>
    </VCard>

    <AddPolicyDrawer
      v-model:is-drawer-open="isDrawerOpen"
      :policy-to-edit="selectedPolicy"
      @policy-data="savePolicy"
    />
  </section>
</template>
