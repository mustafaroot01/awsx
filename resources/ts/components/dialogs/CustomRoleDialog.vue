<script setup lang="ts">
interface Props {
  rolePermissions?: any
  isDialogVisible: boolean
}
interface Emit {
  (e: 'update:isDialogVisible', value: boolean): void
}

const props = withDefaults(defineProps<Props>(), {
  rolePermissions: () => ({
    name: '',
    permissions: [],
  }),
})

const emit = defineEmits<Emit>()

const roleName = ref('')
const selectedPermissions = ref<number[]>([])
const permissionsGrouped = ref<Record<string, any[]>>({})
const isFetching = ref(false)

const fetchPermissions = async () => {
  if (Object.keys(permissionsGrouped.value).length > 0) return
  
  isFetching.value = true
  try {
    const data = await $api<any>('/apps/permissions')
    if (data) {
      permissionsGrouped.value = data
    }
  } catch (err) {
    console.error('Failed to fetch permissions:', err)
  } finally {
    isFetching.value = false
  }
}

onMounted(fetchPermissions)

watch(() => props.isDialogVisible, (val) => {
  if (val) {
    fetchPermissions()
    roleName.value = props.rolePermissions?.name || ''
    selectedPermissions.value = (props.rolePermissions?.permissions || []).map((p: any) => p.id)
  }
})

const onSubmit = async () => {
  if (!roleName.value) return

  const method = props.rolePermissions?.id ? 'PUT' : 'POST'
  const url = props.rolePermissions?.id ? `/apps/roles/${props.rolePermissions.id}` : '/apps/roles'

  try {
    await $api(url, {
      method,
      body: {
        name: roleName.value,
        permissions: selectedPermissions.value,
      },
    })

    alert('تم حفظ المجموعة بنجاح')
    emit('update:isDialogVisible', false)
    window.location.reload()
  } catch (err: any) {
    alert('حدث خطأ أثناء الحفظ')
  }
}

const onReset = () => {
  emit('update:isDialogVisible', false)
}

const toggleSelectAll = (category: string, checked: boolean) => {
  const ids = permissionsGrouped.value[category].map(p => p.id)
  if (checked) {
    selectedPermissions.value = [...new Set([...selectedPermissions.value, ...ids])]
  } else {
    selectedPermissions.value = selectedPermissions.value.filter(id => !ids.includes(id))
  }
}

const isCategoryFull = (category: string) => {
  const ids = permissionsGrouped.value[category].map(p => p.id)
  return ids.every(id => selectedPermissions.value.includes(id))
}
</script>

<template>
  <VDialog
    :width="800"
    :model-value="props.isDialogVisible"
    @update:model-value="onReset"
  >
    <VCard class="pa-10">
      <VCardText>
        <h4 class="text-h4 text-center mb-6">
          {{ props.rolePermissions?.id ? 'تعديل المجموعة' : 'إضافة مجموعة جديدة' }}
        </h4>

        <VTextField
          v-model="roleName"
          label="اسم المجموعة"
          placeholder="مثال: لجنة التقييم"
          variant="outlined"
          class="mb-6"
        />

        <h5 class="text-h5 mb-4 mt-8">صلاحيات المجموعة</h5>

        <div v-if="isFetching" class="text-center py-10">
          <VProgressCircular indeterminate color="primary" />
        </div>

        <div v-else v-for="(perms, category) in permissionsGrouped" :key="category" class="mb-6 border rounded-lg pa-4">
          <div class="d-flex align-center justify-space-between mb-4 bg-light pa-2 rounded">
            <h6 class="text-h6 mb-0">{{ category }}</h6>
            <VCheckbox
              :model-value="isCategoryFull(category)"
              label="تحديد الكل"
              density="compact"
              hide-details
              @update:model-value="toggleSelectAll(category, $event)"
            />
          </div>
          
          <VRow no-gutters>
            <VCol v-for="p in perms" :key="p.id" cols="12" sm="6" md="4" class="py-1">
              <VCheckbox
                v-model="selectedPermissions"
                :value="p.id"
                :label="p.name"
                density="compact"
                hide-details
              />
            </VCol>
          </VRow>
        </div>

        <div class="d-flex align-center justify-center gap-4 mt-8">
          <VBtn color="primary" @click="onSubmit">حفظ التغييرات</VBtn>
          <VBtn color="secondary" variant="tonal" @click="onReset">إلغاء</VBtn>
        </div>
      </VCardText>
    </VCard>
  </VDialog>
</template>

<style scoped>
.bg-light {
  background-color: #f8f9fa;
}
</style>
