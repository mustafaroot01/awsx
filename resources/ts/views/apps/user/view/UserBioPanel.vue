<script setup lang="ts">
interface Props {
  userData: {
    id: number
    fullName: string
    firstName: string
    lastName: string
    company: string
    username: string
    role: string
    country: string
    contact: string
    email: string
    currentPlan: string
    status: string
    avatar: string
    taskDone: number
    projectDone: number
    taxId: string
    language: string
  }
}

const props = defineProps<Props>()

// 👉 Role variant resolver
const resolveUserRoleVariant = (role: string) => {
  const roleLower = (role || '').toLowerCase()
  if (roleLower.includes('admin') || roleLower.includes('عام'))
    return { color: 'error', icon: 'tabler-shield-check' }
  if (roleLower.includes('مدير'))
    return { color: 'primary', icon: 'tabler-user-check' }
  
  return { color: 'success', icon: 'tabler-user' }
}
</script>

<template>
  <VCard class="overflow-hidden profile-card h-100">
    <!-- Header Background -->
    <div class="profile-header-bg" />

    <VCardText class="position-relative pt-0">
      <!-- Avatar & Basic Info -->
      <div class="text-center mt-n12">
        <VAvatar
          size="120"
          rounded="circle"
          class="elevation-4 border-white border-4 mb-4"
          :color="!props.userData.avatar ? 'primary' : undefined"
        >
          <VImg v-if="props.userData.avatar" :src="props.userData.avatar" />
          <span v-else class="text-4xl font-weight-bold">{{ avatarText(props.userData.fullName) }}</span>
        </VAvatar>

        <h3 class="text-h3 mb-2 font-weight-bold">
          {{ props.userData.fullName }}
        </h3>
        
        <VChip
          label
          size="small"
          :color="resolveUserRoleVariant(props.userData.role).color"
          variant="tonal"
          class="font-weight-medium px-4 mb-6"
        >
          <VIcon start size="16" :icon="resolveUserRoleVariant(props.userData.role).icon" />
          {{ props.userData.role }}
        </VChip>
      </div>

      <VDivider class="mb-6" />

      <!-- Quick Info -->
      <div class="d-flex flex-column gap-y-4">
        <div class="d-flex align-center gap-x-3">
          <VAvatar size="34" color="primary" variant="tonal" rounded="lg">
            <VIcon icon="tabler-mail" size="18" />
          </VAvatar>
          <div class="flex-grow-1">
            <div class="text-caption text-disabled leading-tight">البريد الإلكتروني</div>
            <div class="text-body-2 font-weight-medium text-truncate" style="max-width: 180px;">{{ props.userData.email }}</div>
          </div>
        </div>

        <div class="d-flex align-center gap-x-3">
          <VAvatar size="34" color="success" variant="tonal" rounded="lg">
            <VIcon icon="tabler-phone" size="18" />
          </VAvatar>
          <div class="flex-grow-1">
            <div class="text-caption text-disabled leading-tight">رقم الهاتف</div>
            <div class="text-body-2 font-weight-medium">{{ props.userData.contact }}</div>
          </div>
        </div>

        <div class="d-flex align-center gap-x-3">
          <VAvatar size="34" color="info" variant="tonal" rounded="lg">
            <VIcon icon="tabler-building" size="18" />
          </VAvatar>
          <div class="flex-grow-1">
            <div class="text-caption text-disabled leading-tight">الفرع</div>
            <div class="text-body-2 font-weight-medium">المكتب الرئيسي</div>
          </div>
        </div>
      </div>
    </VCardText>
  </VCard>
</template>

<style lang="scss" scoped>
.profile-card {
  border-radius: 24px;
}

.profile-header-bg {
  height: 100px;
  background: linear-gradient(135deg, rgb(var(--v-theme-primary)) 0%, rgb(var(--v-theme-secondary)) 100%);
  opacity: 0.15;
}

.border-white {
  border-color: #fff !important;
}

.leading-tight {
  line-height: 1.2;
}
</style>
