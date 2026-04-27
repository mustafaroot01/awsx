<script setup lang="ts">
import AddNewUserDrawer from '@/views/apps/user/list/AddNewUserDrawer.vue'

definePage({
  meta: { action: 'read', subject: 'User' },
})

// 👉 Store
const searchQuery = ref('')
const selectedRole = ref()

// Data table options
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()

// 👉 Fetching users
const { data: usersData, execute: fetchUsers } = await useApi<any>(createUrl('/apps/users', {
  query: {
    q: searchQuery,
    role: selectedRole,
    itemsPerPage,
    page,
    sortBy,
    orderBy,
  },
}))

const users = computed(() => usersData.value.users)
const totalUsers = computed(() => usersData.value.totalUsers)

const roleOptions = ref<{ title: string; value: number }[]>([])

onMounted(async () => {
  const r = await $api<any>('/apps/users/roles')
  roleOptions.value = (r ?? []).map((role: any) => ({ title: role.name, value: role.id }))
})

// Headers
const headers = [
  { title: 'المستخدم', key: 'user' },
  { title: 'المجموعة / الصلاحية', key: 'role' },
  { title: 'البريد الإلكتروني', key: 'email' },
  { title: 'الحالة', key: 'status' },
  { title: 'إجراءات', key: 'actions', sortable: false },
]

const updateOptions = (options: any) => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

// 👉 States
const isAddNewUserDrawerVisible = ref(false)
const userToEdit = ref<any>(null)
const isConfirmDeleteDialogOpen = ref(false)
const userToDeleteId = ref<number | null>(null)

// 👉 Snackbar
const isSnackbarVisible = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref('success')

const showMessage = (msg: string, color = 'success') => {
  snackbarMessage.value = msg
  snackbarColor.value = color
  isSnackbarVisible.value = true
}

// 👉 Actions
const addNewUser = async (userData: any) => {
  try {
    if (userData.id > 0) {
      // Update
      await $api(`/apps/users/${userData.id}`, {
        method: 'PUT',
        body: userData,
      })
      showMessage('تم تحديث بيانات المستخدم بنجاح')
    } else {
      // Create
      await $api('/apps/users', {
        method: 'POST',
        body: userData,
      })
      showMessage('تمت إضافة المستخدم الجديد بنجاح')
    }
    fetchUsers()
  } catch (e) {
    showMessage('فشلت العملية، يرجى المحاولة مرة أخرى', 'error')
  }
}

const openEditDrawer = (user: any) => {
  userToEdit.value = user
  isAddNewUserDrawerVisible.value = true
}

const openDeleteConfirm = (id: number) => {
  userToDeleteId.value = id
  isConfirmDeleteDialogOpen.value = true
}

const deleteUser = async () => {
  if (!userToDeleteId.value) return
  
  try {
    await $api(`/apps/users/${userToDeleteId.value}`, {
      method: 'DELETE',
    })
    showMessage('تم حذف المستخدم بنجاح', 'success')
    fetchUsers()
  } catch (e) {
    showMessage('حدث خطأ أثناء محاولة الحذف', 'error')
  } finally {
    isConfirmDeleteDialogOpen.value = false
    userToDeleteId.value = null
  }
}

// 👉 User Data & Admin Check
const userData = useCookie<any>('userData')
const isAdmin = computed(() => {
  const superAdmins = ['mus2afa30@gmail.com', 'admin@admin.com', 'mus@mus.com']
  return userData.value?.role === 'admin' || superAdmins.includes(userData.value?.email)
})

// 👉 Reset userToEdit when closing drawer
watch(isAddNewUserDrawerVisible, (val) => {
  if (!val) userToEdit.value = null
})
</script>

<template>
  <section>
    <VCard class="mb-6">
      <VCardItem class="pb-4">
        <VCardTitle>فلاتر البحث</VCardTitle>
      </VCardItem>

      <VCardText>
        <VRow>
          <VCol cols="12" sm="6">
            <AppSelect
              v-model="selectedRole"
              placeholder="تصفية حسب المجموعة"
              :items="roleOptions"
              clearable
              clear-icon="tabler-x"
            />
          </VCol>
          <VCol cols="12" sm="6">
            <AppTextField
              v-model="searchQuery"
              placeholder="بحث عن مستخدم..."
              prepend-inner-icon="tabler-search"
            />
          </VCol>
        </VRow>
      </VCardText>

      <VDivider />

      <VCardText class="d-flex flex-wrap gap-4">
        <div class="me-3 d-flex gap-3">
          <AppSelect
            :model-value="itemsPerPage"
            :items="[10, 25, 50, 100]"
            style="inline-size: 6.25rem;"
            @update:model-value="itemsPerPage = parseInt($event, 10)"
          />
        </div>
        <VSpacer />

        <div class="d-flex align-center flex-wrap gap-4">
          <VBtn
            v-if="$can('create', 'User')"
            prepend-icon="tabler-plus"
            @click="isAddNewUserDrawerVisible = true"
          >
            إضافة مستخدم جديد
          </VBtn>
        </div>
      </VCardText>

      <VDivider />

      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:page="page"
        :items="users"
        item-value="id"
        :items-length="totalUsers"
        :headers="headers"
        class="text-no-wrap"
        @update:options="updateOptions"
      >
        <!-- User -->
        <template #item.user="{ item }">
          <div class="d-flex align-center gap-x-4">
            <VAvatar size="34" color="primary" variant="tonal">
              <span>{{ avatarText(item.fullName) }}</span>
            </VAvatar>
            <div class="d-flex flex-column">
              <h6 class="text-base font-weight-medium">{{ item.fullName }}</h6>
            </div>
          </div>
        </template>

        <!-- Role -->
        <template #item.role="{ item }">
          <div class="d-flex gap-1 flex-wrap">
            <VChip
              v-if="item.role === 'إدارة النظام'"
              color="primary"
              size="small"
              label
              variant="elevated"
            >
              <VIcon start icon="tabler-shield-check" size="14" />
              {{ item.role }}
            </VChip>
            
            <template v-else>
              <VChip
                v-for="role in item.roles"
                :key="role.id"
                color="info"
                size="small"
                label
              >
                {{ role.name }}
              </VChip>
              <span v-if="!item.roles || item.roles.length === 0" class="text-caption text-medium-emphasis">بدون مجموعة</span>
            </template>
          </div>
        </template>

        <!-- Status -->
        <template #item.status="{ item }">
          <VChip color="success" size="small" label>نشط</VChip>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <div class="d-flex gap-1" v-if="item.role !== 'إدارة النظام' || isAdmin">
            <IconBtn v-if="$can('update', 'User')" color="primary" @click="openEditDrawer(item)">
              <VIcon icon="tabler-edit" />
              <VTooltip activator="parent" location="top">تعديل</VTooltip>
            </IconBtn>
            <IconBtn v-if="$can('delete', 'User')" color="error" @click="openDeleteConfirm(item.id)">
              <VIcon icon="tabler-trash" />
              <VTooltip activator="parent" location="top">حذف</VTooltip>
            </IconBtn>
          </div>
          <span v-else class="text-caption text-disabled px-2 italic">حساب محمي</span>
        </template>

        <!-- pagination -->
        <template #bottom>
          <VDivider />
          <div class="d-flex justify-end pa-4">
            <VPagination
              v-model="page"
              :length="Math.ceil(totalUsers / itemsPerPage)"
              :total-visible="$vuetify.display.smAndDown ? 3 : 5"
            />
          </div>
        </template>
      </VDataTableServer>
    </VCard>

    <!-- 👉 Add/Edit User Drawer -->
    <AddNewUserDrawer
      v-model:is-drawer-open="isAddNewUserDrawerVisible"
      :user="userToEdit"
      @user-data="addNewUser"
    />

    <!-- 👉 Delete Confirmation Dialog -->
    <VDialog
      v-model="isConfirmDeleteDialogOpen"
      max-width="500px"
    >
      <VCard class="pa-4 text-center">
        <VCardText class="pt-6">
          <VIcon
            icon="tabler-alert-circle"
            size="80"
            color="warning"
            class="mb-4"
          />
          <h4 class="text-h4 mb-2">هل أنت متأكد من الحذف؟</h4>
          <p class="text-body-1">لا يمكن التراجع عن هذه العملية بعد التأكيد.</p>
        </VCardText>

        <VCardActions class="justify-center gap-4 pb-6">
          <VBtn
            color="error"
            variant="elevated"
            @click="deleteUser"
          >
            نعم، احذف المستخدم
          </VBtn>
          <VBtn
            color="secondary"
            variant="tonal"
            @click="isConfirmDeleteDialogOpen = false"
          >
            إلغاء التراجع
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- 👉 Snackbar Notifications -->
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
