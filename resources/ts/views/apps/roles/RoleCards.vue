<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'

interface Role {
  id: number
  name: string
  users_count: number
}

interface Permission {
  id: number
  name: string
  slug: string
  category: string
}

const roles = ref<Role[]>([])
const isRoleDialogVisible = ref(false)
const roleDetail = ref<any>(null)
const roleName = ref('')
const selectedPermissions = ref<number[]>([])
const allPermissions = ref<Permission[]>([])
const isFetching = ref(false)
const searchQueryAvailable = ref('')
const searchQueryGiven = ref('')
const expandedCategories = ref<string[]>([])

const isConfirmDialogVisible = ref(false)
const roleToDelete = ref<Role | null>(null)

const isSnackbarVisible = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref('success')

const showNotification = (message: string, color = 'success') => {
  snackbarMessage.value = message
  snackbarColor.value = color
  isSnackbarVisible.value = true
}

const fetchRoles = async () => {
  try {
    const data = await $api<any>('/apps/roles')
    roles.value = (data ?? []).map((r: any) => ({ ...r, users_count: r.users_count ?? 0 }))
  } catch (e) { 
    showNotification('فشل في جلب المجموعات', 'error')
  }
}

const fetchAllPermissions = async () => {
  isFetching.value = true
  try {
    const data = await $api<any>('/apps/permissions')
    const flattened: Permission[] = []
    Object.values(data).forEach((perms: any) => {
      flattened.push(...perms)
    })
    allPermissions.value = flattened
    expandedCategories.value = []
  } catch (err) { console.error(err) }
  finally { isFetching.value = false }
}

onMounted(() => {
  fetchRoles()
  fetchAllPermissions()
})

const availableGrouped = computed(() => {
  const grouped: Record<string, Permission[]> = {}
  allPermissions.value.forEach(p => {
    if (!selectedPermissions.value.includes(p.id)) {
      if (!grouped[p.category]) grouped[p.category] = []
      if (p.name.includes(searchQueryAvailable.value)) {
        grouped[p.category].push(p)
      }
    }
  })
  return grouped
})

const givenPermissions = computed(() => {
  return allPermissions.value.filter(p => 
    selectedPermissions.value.includes(p.id) &&
    p.name.includes(searchQueryGiven.value)
  )
})

const movePermission = (id: number, direction: 'give' | 'take') => {
  if (direction === 'give') {
    if (!selectedPermissions.value.includes(id)) {
      selectedPermissions.value.push(id)
    }
  } else {
    selectedPermissions.value = selectedPermissions.value.filter(pId => pId !== id)
  }
}

const giveCategory = (category: string) => {
  const categoryIds = allPermissions.value.filter(p => p.category === category).map(p => p.id)
  selectedPermissions.value = [...new Set([...selectedPermissions.value, ...categoryIds])]
}

const giveAll = () => {
  selectedPermissions.value = allPermissions.value.map(p => p.id)
}

const takeAll = () => {
  selectedPermissions.value = []
}

const editRole = async (item: Role) => {
  try {
    const data = await $api<any>(`/apps/roles/${item.id}`)
    roleDetail.value = data
    roleName.value = data.name
    selectedPermissions.value = (data.permissions || []).map((p: any) => p.id)
    isRoleDialogVisible.value = true
    expandedCategories.value = []
  } catch (e) { 
    showNotification('حدث خطأ في جلب البيانات', 'error')
  }
}

const addNewRole = () => {
  roleDetail.value = null
  roleName.value = ''
  selectedPermissions.value = []
  isRoleDialogVisible.value = true
  expandedCategories.value = []
}

const onSubmit = async () => {
  if (!roleName.value) {
    showNotification('يرجى إدخال اسم المجموعة', 'warning')
    return
  }
  const method = roleDetail.value?.id ? 'PUT' : 'POST'
  const url = roleDetail.value?.id ? `/apps/roles/${roleDetail.value.id}` : '/apps/roles'
  try {
    await $api(url, {
      method,
      body: { name: roleName.value, permissions: selectedPermissions.value },
    })
    isRoleDialogVisible.value = false
    showNotification('تم حفظ التغييرات بنجاح')
    fetchRoles()
  } catch (err) { 
    showNotification('خطأ أثناء الحفظ', 'error')
  }
}

const confirmDelete = (item: Role) => {
  roleToDelete.value = item
  isConfirmDialogVisible.value = true
}

const deleteRole = async () => {
  if (!roleToDelete.value) return
  try {
    await $api(`/apps/roles/${roleToDelete.value.id}`, { method: 'DELETE' })
    isConfirmDialogVisible.value = false
    showNotification('تم حذف المجموعة بنجاح')
    fetchRoles()
  } catch (err: any) {
    const msg = err.response?._data?.message || 'حدث خطأ أثناء الحذف'
    showNotification(msg, 'error')
    isConfirmDialogVisible.value = false
  }
}
</script>

<template>
  <section>
    <!-- 👉 Notification -->
    <VSnackbar v-model="isSnackbarVisible" :color="snackbarColor" location="top end" variant="flat">
      <div class="d-flex align-center gap-2">
        <VIcon :icon="snackbarColor === 'success' ? 'tabler-circle-check' : 'tabler-alert-triangle'" color="white" />
        <span>{{ snackbarMessage }}</span>
      </div>
    </VSnackbar>

    <!-- 👉 Confirmation Delete Dialog -->
    <VDialog v-model="isConfirmDialogVisible" width="400">
      <VCard class="pa-4 text-center">
        <VCardText>
          <VIcon icon="tabler-alert-triangle" color="warning" size="64" class="mb-4" />
          <h5 class="text-h5 mb-2">تأكيد الحذف</h5>
          <p class="text-body-1 mb-6">هل أنت متأكد من حذف مجموعة "{{ roleToDelete?.name }}"؟</p>
          <div class="d-flex justify-center gap-4">
            <VBtn color="error" @click="deleteRole">نعم، احذف</VBtn>
            <VBtn color="secondary" variant="tonal" @click="isConfirmDialogVisible = false">إلغاء</VBtn>
          </div>
        </VCardText>
      </VCard>
    </VDialog>

    <!-- 👉 Role Editor Dialog -->
    <VDialog v-model="isRoleDialogVisible" width="1100">
      <VCard class="pa-sm-8 pa-4">
        <VCardText>
          <h4 class="text-h4 text-center mb-2">{{ roleDetail?.id ? 'تعديل المجموعة' : 'إضافة مجموعة جديدة' }}</h4>
          <p class="text-body-1 text-center mb-8">إدارة الصلاحيات والوصول للنظام</p>

          <VTextField v-model="roleName" label="اسم المجموعة" placeholder="مثال: الموارد البشرية" variant="outlined" class="mb-8" />

          <VRow>
            <VCol cols="12" md="5">
              <div class="list-container border rounded overflow-hidden">
                <div class="list-header bg-secondary text-white pa-3 d-flex align-center justify-space-between">
                  <span>الصلاحيات المتاحة</span>
                  <VIcon icon="tabler-list" size="20" />
                </div>
                <div class="pa-2 bg-light border-bottom">
                  <VTextField v-model="searchQueryAvailable" placeholder="بحث..." density="compact" hide-details variant="plain" prepend-inner-icon="tabler-search" />
                </div>
                <div class="list-body scrollable">
                  <div v-if="isFetching" class="text-center py-10"><VProgressCircular indeterminate size="24" /></div>
                  <VExpansionPanels v-else v-model="expandedCategories" multiple variant="accordion">
                    <VExpansionPanel v-for="(perms, category) in availableGrouped" :key="category" :value="category">
                      <VExpansionPanelTitle class="pa-2 py-3">
                        <div class="d-flex align-center justify-space-between w-100 me-4">
                          <span class="font-weight-bold text-sm">{{ category }}</span>
                          <VBtn variant="tonal" color="primary" size="x-small" @click.stop="giveCategory(category)">منح القسم</VBtn>
                        </div>
                      </VExpansionPanelTitle>
                      <VExpansionPanelText class="pa-0">
                        <div v-for="p in perms" :key="p.id" class="list-item-sub pa-2 px-4 d-flex align-center justify-space-between" @click="movePermission(p.id, 'give')">
                          <span class="text-sm">{{ p.name }}</span>
                          <VIcon icon="tabler-plus" size="16" color="primary" />
                        </div>
                      </VExpansionPanelText>
                    </VExpansionPanel>
                  </VExpansionPanels>
                </div>
              </div>
            </VCol>

            <VCol cols="12" md="2" class="d-flex flex-md-column align-center justify-center gap-4 py-4">
               <VBtn color="primary" variant="tonal" @click="giveAll" class="w-100" prepend-icon="tabler-chevrons-right">منح الكل</VBtn>
               <VBtn color="error" variant="tonal" @click="takeAll" class="w-100" prepend-icon="tabler-chevrons-left">سحب الكل</VBtn>
            </VCol>

            <VCol cols="12" md="5">
              <div class="list-container border rounded overflow-hidden shadow-sm">
                <div class="list-header bg-primary text-white pa-3 d-flex align-center justify-space-between">
                  <span>الصلاحيات الممنوحة</span>
                  <VIcon icon="tabler-user-check" size="20" />
                </div>
                <div class="pa-2 bg-light border-bottom">
                  <VTextField v-model="searchQueryGiven" placeholder="بحث..." density="compact" hide-details variant="plain" prepend-inner-icon="tabler-search" />
                </div>
                <div class="list-body scrollable">
                  <div v-if="givenPermissions.length === 0" class="text-center py-10 text-muted text-sm">لم يتم اختيار أي صلاحيات</div>
                  <div v-for="p in givenPermissions" :key="p.id" class="list-item pa-3 d-flex align-center justify-space-between bg-given mb-1" @click="movePermission(p.id, 'take')">
                    <div>
                      <div class="text-sm font-weight-medium">{{ p.name }}</div>
                      <div class="text-xs text-muted">{{ p.category }}</div>
                    </div>
                    <VIcon icon="tabler-x" size="18" color="error" />
                  </div>
                </div>
              </div>
            </VCol>
          </VRow>

          <div class="d-flex align-center justify-center gap-4 mt-10">
            <VBtn color="primary" @click="onSubmit" size="large" prepend-icon="tabler-device-floppy">حفظ التغييرات</VBtn>
            <VBtn color="secondary" variant="tonal" @click="isRoleDialogVisible = false" size="large">إلغاء</VBtn>
          </div>
        </VCardText>
      </VCard>
    </VDialog>

    <!-- 👉 Header Actions -->
    <div class="d-flex flex-wrap align-center justify-space-between gap-4 mb-6 mt-4">
      <div>
        <h3 class="text-h3 mb-1">مجموعات الصلاحيات</h3>
        <p class="text-sm text-muted mb-0">إدارة وتخصيص مستويات الوصول لمستخدمي النظام</p>
      </div>
      <div class="d-flex gap-3">
        <VBtn v-if="$can('create', 'Role')" color="primary" prepend-icon="tabler-plus" @click="addNewRole">إضافة مجموعة جديدة</VBtn>
        <VBtn variant="tonal" color="secondary" prepend-icon="tabler-refresh" @click="fetchRoles">تحديث</VBtn>
      </div>
    </div>

    <!-- 👉 Roles Cards Grid -->
    <VRow>
      <VCol v-for="item in roles" :key="item.id" cols="12" sm="6" lg="4">
        <VCard elevation="2" class="role-card overflow-visible">
          <VCardText class="d-flex align-center pb-4">
            <div class="text-body-1 font-weight-medium text-muted">إجمالي {{ item.users_count }} مستخدمين</div>
            <VSpacer />
            <IconBtn v-if="$can('delete', 'Role')" color="error" variant="text" size="small" @click="confirmDelete(item)" title="حذف المجموعة">
              <VIcon icon="tabler-trash" size="20" />
            </IconBtn>
          </VCardText>
          
          <VCardText>
            <div class="d-flex justify-space-between align-end">
              <div>
                <h5 class="text-h5 mb-1">{{ item.name }}</h5>
                <a v-if="$can('update', 'Role')" href="javascript:void(0)" class="text-primary font-weight-bold text-sm" @click="editRole(item)">تعديل الصلاحيات</a>
              </div>
              <VAvatar color="primary" variant="tonal" size="42" class="rounded">
                <VIcon icon="tabler-shield-lock" size="24" />
              </VAvatar>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </section>
</template>

<style scoped>
.list-container { height: 500px; display: flex; flex-direction: column; }
.list-body { flex-grow: 1; overflow-y: auto; background: #fff; }
.list-item, .list-item-sub { cursor: pointer; transition: all 0.2s; border-bottom: 1px solid #f8f9fa; }
.list-item:hover, .list-item-sub:hover { background-color: #f5f8ff; }
.bg-given { background-color: #f0fdf4; border-right: 4px solid #4ade80; }
.scrollable::-webkit-scrollbar { width: 5px; }
.scrollable::-webkit-scrollbar-thumb { background: #e0e0e0; border-radius: 10px; }
.w-100 { width: 100%; }
.role-card { border-top: 4px solid #7367f0; transition: all 0.2s; }
.role-card:hover { transform: translateY(-5px); box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important; }
</style>
