<script setup lang="ts">
import type { ProductionPlan, PlanCategoryItem, BranchTarget } from '@db/apps/production-plans/types'
import type { VForm } from 'vuetify/components/VForm'

interface Emit {
  (e: 'update:isDrawerOpen', value: boolean): void
  (e: 'planData', value: any): void
}
interface Props {
  isDrawerOpen: boolean
  planToEdit?: ProductionPlan | null
}

const props = withDefaults(defineProps<Props>(), { planToEdit: null })
const emit = defineEmits<Emit>()

const isEditMode = computed(() => !!props.planToEdit)
const isFormValid = ref(false)
const refForm = ref<VForm>()

const year = ref<number>(new Date().getFullYear())
const title = ref('')
const totalAmount = ref<number>(0)

const categoryLabels: Record<string, string> = {
  life: 'تأمين الحياة',
  group_health: 'الصحي الجماعي',
  general_property: 'الممتلكات العامة',
}

const categories = ref<PlanCategoryItem[]>([
  { category: 'life', targetAmount: 0 },
  { category: 'group_health', targetAmount: 0 },
  { category: 'general_property', targetAmount: 0 },
])

const branchOptions = ref<{ title: string; value: number }[]>([])
const branchTargets = ref<BranchTarget[]>([])

const addBranchTarget = () => {
  branchTargets.value.push({ branchId: 0, category: 'life', targetAmount: 0 })
}

const removeBranchTarget = (idx: number) => {
  branchTargets.value.splice(idx, 1)
}

const categoryOptions = [
  { title: 'تأمين الحياة', value: 'life' },
  { title: 'الصحي الجماعي', value: 'group_health' },
  { title: 'الممتلكات العامة', value: 'general_property' },
]

const fillForm = (plan: ProductionPlan) => {
  year.value = plan.year
  title.value = plan.title
  totalAmount.value = plan.totalAmount
  if (plan.categories?.length) {
    categories.value = plan.categories.map(c => ({ category: c.category, targetAmount: c.targetAmount }))
  }
  branchTargets.value = (plan.branchTargets ?? []).map(bt => ({
    branchId: bt.branchId,
    category: bt.category,
    targetAmount: bt.targetAmount,
  }))
}

watch(() => props.isDrawerOpen, async open => {
  if (open) {
    const result = await $api<any>('/apps/branches?itemsPerPage=-1')
    branchOptions.value = (result?.branches ?? []).map((b: any) => ({ title: b.name, value: b.id }))
    if (props.planToEdit) fillForm(props.planToEdit)
  }
})

watch(() => props.planToEdit, plan => {
  if (plan) fillForm(plan)
})

const closeDrawer = () => {
  emit('update:isDrawerOpen', false)
  nextTick(() => {
    refForm.value?.reset()
    branchTargets.value = []
    categories.value = [
      { category: 'life', targetAmount: 0 },
      { category: 'group_health', targetAmount: 0 },
      { category: 'general_property', targetAmount: 0 },
    ]
  })
}

const onSubmit = () => {
  refForm.value?.validate().then(({ valid }) => {
    if (!valid) return
    emit('planData', {
      id: props.planToEdit?.id ?? 0,
      year: year.value,
      title: title.value,
      totalAmount: totalAmount.value,
      categories: categories.value,
      branchTargets: branchTargets.value.filter(bt => bt.branchId > 0),
    })
    closeDrawer()
  })
}

const formatCurrency = (v: number) =>
  new Intl.NumberFormat('ar-IQ', { style: 'decimal', maximumFractionDigits: 0 }).format(v) + ' د.ع'

// Bulk distribution
const bulkDistributionMode = ref<'custom' | 'uniform'>('custom')
const bulkTargets = ref({
  life: 0,
  group_health: 0,
  general_property: 0,
})

const applyBulkDistribution = () => {
  if (branchOptions.value.length === 0) return

  // Confirmation or just do it? Let's do it and allow manual cleanup
  branchTargets.value = [] // Clear existing

  branchOptions.value.forEach(branch => {
    // Add 3 rows for each branch (one per category)
    if (bulkTargets.value.life > 0)
      branchTargets.value.push({ branchId: branch.value, category: 'life', targetAmount: bulkTargets.value.life })
    
    if (bulkTargets.value.group_health > 0)
      branchTargets.value.push({ branchId: branch.value, category: 'group_health', targetAmount: bulkTargets.value.group_health })
    
    if (bulkTargets.value.general_property > 0)
      branchTargets.value.push({ branchId: branch.value, category: 'general_property', targetAmount: bulkTargets.value.general_property })
  })

  bulkDistributionMode.value = 'custom' // Switch back to see the results
}
</script>

<template>
  <VNavigationDrawer
    :model-value="props.isDrawerOpen"
    temporary
    location="end"
    width="500"
    @update:model-value="emit('update:isDrawerOpen', $event)"
  >
    <AppDrawerHeaderSection
      :title="isEditMode ? 'تعديل الخطة الإنتاجية' : 'إضافة خطة إنتاجية'"
      @cancel="closeDrawer"
    />
    <VDivider />

    <VForm ref="refForm" v-model="isFormValid" class="d-flex flex-column h-100" @submit.prevent="onSubmit">
      <!-- Fixed Header is already handled by AppDrawerHeaderSection -->
      
      <!-- Scrollable Content Section -->
      <div class="flex-grow-1 overflow-y-auto pa-5 custom-scrollbar">
        <VRow>
          <!-- السنة -->
          <VCol cols="12">
            <AppTextField
              v-model="year"
              :rules="[requiredValidator]"
              label="السنة"
              type="number"
              placeholder="2025"
            />
          </VCol>

          <!-- العنوان -->
          <VCol cols="12">
            <AppTextField
              v-model="title"
              :rules="[requiredValidator]"
              label="عنوان الخطة"
              placeholder="الخطة الإنتاجية لعام 2025"
            />
          </VCol>

          <!-- الإجمالي -->
          <VCol cols="12">
            <AppTextField
              v-model="totalAmount"
              :rules="[requiredValidator]"
              label="إجمالي مبلغ الخطة (د.ع)"
              type="number"
              placeholder="300000000"
            />
            <div v-if="totalAmount > 0" class="text-caption text-primary mt-1 font-weight-bold">
              {{ formatCurrency(Number(totalAmount)) }}
            </div>
          </VCol>

          <VCol cols="12">
            <VDivider class="my-4" />
          </VCol>

          <!-- توزيع الفئات -->
          <VCol cols="12">
            <h6 class="text-h6 mb-4">توزيع الفئات الرئيسية</h6>
            <VCard border flat class="pa-4 bg-var-theme-background">
              <VRow v-for="cat in categories" :key="cat.category" align="center" class="mb-3">
                <VCol cols="5">
                  <span class="text-body-2 font-weight-medium">{{ categoryLabels[cat.category] }}</span>
                </VCol>
                <VCol cols="7">
                  <AppTextField
                    v-model="cat.targetAmount"
                    :rules="[requiredValidator]"
                    type="number"
                    density="compact"
                    hide-details
                  />
                </VCol>
              </VRow>
            </VCard>
          </VCol>

          <VCol cols="12">
            <VDivider class="my-4" />
          </VCol>

          <!-- مستهدفات الفروع -->
          <VCol cols="12">
            <div class="d-flex flex-column gap-4 mb-6">
              <h6 class="text-h6">مستهدفات الفروع</h6>
              
              <!-- Clean Distribution Action Choice -->
              <div class="d-flex gap-2">
                <VBtn 
                  :variant="bulkDistributionMode === 'uniform' ? 'elevated' : 'tonal'"
                  :color="bulkDistributionMode === 'uniform' ? 'primary' : 'secondary'"
                  size="small"
                  class="flex-grow-1"
                  @click="bulkDistributionMode = 'uniform'"
                >
                  توزيع متساوي
                </VBtn>
                <VBtn 
                  :variant="bulkDistributionMode === 'custom' ? 'elevated' : 'tonal'"
                  :color="bulkDistributionMode === 'custom' ? 'primary' : 'secondary'"
                  size="small"
                  class="flex-grow-1"
                  @click="bulkDistributionMode = 'custom'"
                >
                  تخصيص يدوي
                </VBtn>
              </div>
            </div>

            <!-- Uniform Distribution UI -->
            <VExpandTransition>
              <div v-if="bulkDistributionMode === 'uniform'">
                <VCard border flat class="pa-4 mb-6 bg-light-primary">
                  <div class="text-subtitle-2 mb-4 text-primary">إدخال مبالغ موحدة لجميع الفروع</div>
                  <VRow dense>
                    <VCol cols="12" class="mb-2">
                      <AppTextField v-model="bulkTargets.life" label="تأمين الحياة" type="number" density="compact" />
                    </VCol>
                    <VCol cols="12" class="mb-2">
                      <AppTextField v-model="bulkTargets.group_health" label="الصحي الجماعي" type="number" density="compact" />
                    </VCol>
                    <VCol cols="12" class="mb-4">
                      <AppTextField v-model="bulkTargets.general_property" label="الممتلكات العامة" type="number" density="compact" />
                    </VCol>
                    <VCol cols="12">
                      <VBtn block color="primary" prepend-icon="tabler-check" @click="applyBulkDistribution">
                        تطبيق على {{ branchOptions.length }} فرع
                      </VBtn>
                    </VCol>
                  </VRow>
                </VCard>
              </div>
            </VExpandTransition>

            <!-- Custom List UI -->
            <div class="d-flex align-center justify-space-between mb-4">
              <span class="text-subtitle-2">السجلات الحالية ({{ branchTargets.length }})</span>
              <VBtn size="small" variant="outlined" prepend-icon="tabler-plus" @click="addBranchTarget">
                إضافة سجل
              </VBtn>
            </div>

            <div v-for="(bt, idx) in branchTargets" :key="idx" class="mb-4 pa-3 border rounded position-relative bg-surface">
              <VBtn
                icon="tabler-x"
                variant="tonal"
                color="error"
                size="x-small"
                class="position-absolute"
                style="top: -10px; left: -10px; z-index: 2;"
                @click="removeBranchTarget(idx)"
              />
              <VRow dense>
                <VCol cols="12" class="mb-2">
                  <AppSelect v-model="bt.branchId" :items="branchOptions" label="الفرع" density="compact" hide-details />
                </VCol>
                <VCol cols="6">
                  <AppSelect v-model="bt.category" :items="categoryOptions" label="الفئة" density="compact" hide-details />
                </VCol>
                <VCol cols="6">
                  <AppTextField v-model="bt.targetAmount" label="المبلغ" type="number" density="compact" hide-details />
                </VCol>
              </VRow>
            </div>
            
            <div v-if="branchTargets.length === 0 && bulkDistributionMode === 'custom'" class="text-center py-10 border-dashed rounded text-disabled">
              لم يتم إضافة أي مستهدفات بعد
            </div>
          </VCol>
        </VRow>
      </div>

      <!-- Fixed Footer Section -->
      <VDivider />
      <div class="pa-5 d-flex gap-4 bg-surface">
        <VBtn type="submit" size="large" class="flex-grow-1">{{ isEditMode ? 'تحديث الخطة' : 'حفظ الخطة' }}</VBtn>
        <VBtn color="secondary" variant="tonal" size="large" @click="closeDrawer">إلغاء</VBtn>
      </div>
    </VForm>
  </VNavigationDrawer>
</template>
