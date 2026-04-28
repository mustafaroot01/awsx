<script setup lang="ts">
import UserBioPanel from '@/views/apps/user/view/UserBioPanel.vue'
import UserEditForm from '@/views/apps/user/view/UserEditForm.vue'

definePage({
  meta: { action: 'read', subject: 'Auth' },
})

const route = useRoute('apps-user-view-id')

const { data: userData } = await useApi<any>(`/apps/users/${route.params.id}`)
</script>

<template>
  <VRow v-if="userData">
    <!-- Sidebar: Profile Summary -->
    <VCol
      cols="12"
      md="4"
      lg="3"
    >
      <UserBioPanel :user-data="userData" />
    </VCol>

    <!-- Main Content: Edit Form -->
    <VCol
      cols="12"
      md="8"
      lg="9"
    >
      <UserEditForm :user-data="userData" />
    </VCol>
  </VRow>
  <div v-else>
    <VAlert
      type="error"
      variant="tonal"
    >
      المستخدم ذو المعرف {{ route.params.id }} غير موجود!
    </VAlert>
  </div>
</template>
