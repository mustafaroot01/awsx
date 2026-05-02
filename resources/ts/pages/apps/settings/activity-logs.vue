<script setup lang="ts">
definePage({
  meta: { action: 'read', subject: 'Log' },
})

const { data: logsData, loading } = await useApi<any>('/apps/activity-logs')

const headers = [
  { title: 'المستخدم', key: 'user' },
  { title: 'العملية', key: 'action' },
  { title: 'التفاصيل', key: 'description' },
  { title: 'IP', key: 'ip_address' },
  { title: 'التوقيت', key: 'created_at' },
  { title: 'الإجراءات', key: 'actions', sortable: false },
]

const selectedLog = ref<any>(null)
const isDetailsDialogOpen = ref(false)

const showDetails = (item: any) => {
  selectedLog.value = item
  isDetailsDialogOpen.value = true
}

const getActionColor = (action: string) => {
  switch (action) {
    case 'created': return 'success'
    case 'updated': return 'info'
    case 'deleted': return 'error'
    case 'login': return 'warning'
    default: return 'secondary'
  }
}

const getActionText = (action: string) => {
  switch (action) {
    case 'created': return 'إضافة'
    case 'updated': return 'تعديل'
    case 'deleted': return 'حذف'
    case 'login': return 'دخول'
    default: return action
  }
}

const attributeLabels: Record<string, string> = {
  name: 'الاسم',
  first_name: 'الاسم الأول',
  second_name: 'اسم الأب',
  third_name: 'اسم الجد',
  fourth_name: 'اسم الجد الثاني',
  last_name: 'اللقب',
  email: 'البريد الإلكتروني',
  branch_id: 'الفرع',
  location: 'الموقع / العنوان',
  governorate: 'المحافظة',
  manager_id: 'المدير',
  deputy_id: 'المعاون',
  phone: 'الهاتف',
  address: 'العنوان السكني',
  degree: 'الدرجة الوظيفية',
  rank: 'العنوان الوظيفي',
  education: 'التحصيل الدراسي',
  gender: 'الجنس',
  hire_date: 'تاريخ التعيين',
  birth_date: 'تاريخ الميلاد',
  national_id: 'رقم البطاقة الوطنية',
  production_no: 'الرقم الإنتاجي',
  job_type: 'صفة التوظيف',
  job_track: 'المسار الوظيفي',
  status: 'الحالة',
  amount: 'المبلغ / القسط',
  issue_date: 'تاريخ الإصدار',
  expiry_date: 'تاريخ الانتهاء',
  notes: 'ملاحظات',
  updated_at: 'تاريخ التحديث',
  created_at: 'تاريخ الإضافة',
  total_score: 'المجموع الكلي',
  password: 'كلمة المرور',
  short_name: 'الاسم المختصر',
  id: 'الرقم التسلسلي',
}

const formatValue = (val: any, key: string) => {
  if (val === null || val === undefined) return '—'
  
  // Format dates
  if (typeof val === 'string' && (key.endsWith('_at') || key.endsWith('_date') || /^\d{4}-\d{2}-\d{2}/.test(val))) {
    const date = new Date(val)
    if (!isNaN(date.getTime())) {
      return date.toLocaleString('ar-IQ', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
      })
    }
  }
  
  return val
}
</script>

<template>
  <VRow>
    <VCol cols="12">
      <VCard title="سجل نشاطات النظام" subtitle="مراقبة كافة العمليات التي قام بها المستخدمون على النظام">
        <VDataTable
          :items="logsData?.data ?? []"
          :headers="headers"
          :loading="loading"
          class="text-no-wrap"
        >
          <template #item.user="{ item }">
            <div class="d-flex align-center gap-3">
              <VAvatar size="32" :color="item.user ? 'primary' : 'secondary'" variant="tonal">
                {{ item.user ? item.user.name.charAt(0) : '?' }}
              </VAvatar>
              <span class="font-weight-medium">{{ item.user ? item.user.name : 'غير معروف' }}</span>
            </div>
          </template>

          <template #item.action="{ item }">
            <VChip :color="getActionColor(item.action)" size="small" class="font-weight-bold">
              {{ getActionText(item.action) }}
            </VChip>
          </template>

          <template #item.created_at="{ item }">
            <span class="text-caption">{{ new Date(item.created_at).toLocaleString('ar-IQ') }}</span>
          </template>

          <template #item.actions="{ item }">
            <VBtn
              icon
              variant="text"
              color="primary"
              size="small"
              @click="showDetails(item)"
              v-if="item.properties"
            >
              <VIcon icon="tabler-eye" />
              <VTooltip activator="parent">عرض التفاصيل</VTooltip>
            </VBtn>
          </template>
        </VDataTable>
      </VCard>
    </VCol>
  </VRow>

  <!-- Activity Details Dialog -->
  <VDialog
    v-model="isDetailsDialogOpen"
    max-width="800"
  >
    <VCard v-if="selectedLog">
      <VCardTitle class="d-flex justify-space-between align-center bg-light-primary pa-4">
        <div class="d-flex align-center gap-2">
          <VIcon icon="tabler-history" color="primary" />
          <span>تفاصيل العملية</span>
          <VChip :color="getActionColor(selectedLog.action)" size="small" variant="elevated">
            {{ getActionText(selectedLog.action) }}
          </VChip>
        </div>
        <VBtn icon="tabler-x" variant="text" size="small" @click="isDetailsDialogOpen = false" />
      </VCardTitle>

      <VDivider />

      <VCardText class="pa-6">
        <div class="mb-6">
          <div class="text-caption text-secondary mb-1">وصف العملية:</div>
          <div class="text-h6">{{ selectedLog.description }}</div>
        </div>

        <!-- Case: Updated -->
        <div v-if="selectedLog.action === 'updated' && selectedLog.properties">
          <div class="text-subtitle-2 mb-3 font-weight-bold text-primary">تعديلات الحقول:</div>
          <VCard variant="outlined">
            <VTable density="comfortable">
              <thead>
                <tr>
                  <th class="bg-light-secondary">الحقل</th>
                  <th class="bg-light-secondary text-error">من (القيمة القديمة)</th>
                  <th class="bg-light-secondary text-success">إلى (القيمة الجديدة)</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(val, key) in selectedLog.properties.new" :key="key">
                  <td class="font-weight-medium" style="width: 150px;">{{ attributeLabels[key] || key }}</td>
                  <td class="text-disabled text-decoration-line-through text-sm">
                    {{ formatValue(selectedLog.properties.old[key], key) }}
                  </td>
                  <td class="text-success font-weight-bold">
                    {{ formatValue(val, key) }}
                  </td>
                </tr>
              </tbody>
            </VTable>
          </VCard>
        </div>

        <!-- Case: Created / Deleted -->
        <div v-else-if="selectedLog.properties">
          <div class="text-subtitle-2 mb-3 font-weight-bold text-primary">البيانات الكاملة:</div>
          <VCard variant="outlined">
            <VTable density="comfortable">
              <tbody>
                <tr v-for="(val, key) in selectedLog.properties" :key="key">
                  <td class="bg-light-secondary font-weight-bold text-primary" style="inline-size: 200px;">
                    {{ attributeLabels[key] || key }}
                  </td>
                  <td class="text-high-emphasis">
                    {{ formatValue(val, key) }}
                  </td>
                </tr>
              </tbody>
            </VTable>
          </VCard>
        </div>
      </VCardText>

      <VDivider />

      <VCardActions class="pa-4 bg-light-secondary border-top">
        <div class="d-flex flex-wrap gap-4 text-caption text-secondary align-center">
          <div class="d-flex align-center gap-1">
            <VIcon icon="tabler-user" size="14" />
            <span>بواسطة:</span>
            <strong class="text-high-emphasis">{{ selectedLog.user?.name || 'النظام' }}</strong>
          </div>
          
          <div class="d-flex align-center gap-1" v-if="selectedLog.user?.email">
            <VIcon icon="tabler-mail" size="14" />
            <span class="text-high-emphasis">{{ selectedLog.user.email }}</span>
          </div>

          <VDivider vertical class="mx-2 d-none d-sm-block" />

          <div class="d-flex align-center gap-1">
            <VIcon icon="tabler-network" size="14" />
            <span>IP:</span>
            <strong class="text-high-emphasis">{{ selectedLog.ip_address }}</strong>
          </div>
        </div>
        <VSpacer />
        <VBtn
          color="secondary"
          variant="tonal"
          @click="isDetailsDialogOpen = false"
        >
          إغلاق النافذة
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
