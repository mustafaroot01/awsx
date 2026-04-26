<script setup lang="ts">
definePage({
  meta: { action: 'read', subject: 'User' },
})

const { data: logsData, loading } = await useApi<any>('/apps/activity-logs')

const headers = [
  { title: 'المستخدم', key: 'user' },
  { title: 'العملية', key: 'action' },
  { title: 'التفاصيل', key: 'description' },
  { title: 'IP', key: 'ip_address' },
  { title: 'التوقيت', key: 'created_at' },
]

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
        </VDataTable>
      </VCard>
    </VCol>
  </VRow>
</template>
