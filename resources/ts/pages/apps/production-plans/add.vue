<script setup lang="ts">
import { nextTick } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { showPermissionError } from '@/utils/api'

definePage({
  meta: { action: 'read', subject: 'ProductionPlan' },
})

const router = useRouter()
const route = useRoute()

// ─── Edit mode: load plan if ?id= provided ───────────────────────────────────
const planId = computed(() => {
  // Fallback to window.location.search as route.query may not work with unplugin-vue-router
  const urlParams = new URLSearchParams(window.location.search)
  const raw = urlParams.get('id') ?? route.query.id ?? route.params.id
  const num = raw ? Number(Array.isArray(raw) ? raw[0] : raw) : NaN
  console.log('[Edit Mode Debug] window.search:', window.location.search, 'route.query:', route.query, 'planId:', num)
  return Number.isFinite(num) && num > 0 ? num : null
})
const isEditMode = computed(() => !!planId.value)
const pageTitle = computed(() => isEditMode.value ? 'تعديل الخطة الإنتاجية' : 'إضافة خطة إنتاجية جديدة')

// ─── Form state ───────────────────────────────────────────────────────────────
const planName = ref('')
const planYear = ref<number>(new Date().getFullYear())

// ─── Branch targets ───────────────────────────────────────────────────────────
const branchOptions = ref<{ title: string; value: number }[]>([])

interface BranchRow {
  branchId:          number | null
  _branchTarget:     number  // helper: total target for this branch
  life:              number
  group_health:      number
  general_property:  number  // single input for all property categories
}

const mkRow = (branchId: number | null = null): BranchRow => ({
  branchId,
  _branchTarget: 0,
  life: 0, group_health: 0,
  general_property: 0,
})

const branchRows = ref<BranchRow[]>([])

const addBranchRow = () => {
  const idx = branchRows.value.length
  branchRows.value.push(mkRow())
  nextTick(() => openPanel(idx))
}

const removeBranchRow = (idx: number) => branchRows.value.splice(idx, 1)

const branchRowTotal = (row: BranchRow) =>
  (Number(row.life) || 0) +
  (Number(row.group_health) || 0) +
  (Number(row.general_property) || 0)

const branchPropertyTotal = (row: BranchRow) =>
  Number(row.general_property) || 0

const grandBranchTotal = computed(() =>
  branchRows.value.reduce((s, r) => s + branchRowTotal(r), 0)
)

// ─── Per-row branch options: exclude branches already selected in other rows ──
const availableBranches = (currentRow: BranchRow) => {
  const takenIds = new Set(
    branchRows.value
      .filter(r => r !== currentRow && r.branchId !== null)
      .map(r => r.branchId)
  )
  return branchOptions.value.filter(b => !takenIds.has(b.value))
}

// ─── Expansion panel open state ─────────────────────────────────────────────
const openPanels = ref<number[]>([])

const openPanel = (idx: number) => {
  if (!openPanels.value.includes(idx)) openPanels.value.push(idx)
}

// ─── Copy distribution from one branch to another ─────────────────────────────
const copyBranch = (src: BranchRow, dst: BranchRow) => {
  dst._branchTarget  = src._branchTarget
  dst.life              = src.life
  dst.group_health      = src.group_health
  dst.general_property  = src.general_property
}

// ─── Load branches + edit data ────────────────────────────────────────────────
const loadData = async () => {
  try {
    const result = await $api<any>('/apps/branches?itemsPerPage=-1')
    branchOptions.value = (result?.branches ?? []).map((b: any) => ({ title: b.name, value: b.id }))
  } catch (e) {
    console.warn('[add.vue] Failed to load branches', e)
  }

  if (!planId.value) return

  try {
    const res = await $api<any>(`/apps/production-plans/${planId.value}`)
    const plan = res?.plan ?? res  // API may wrap in { plan, achievements }
    planName.value = plan?.title ?? ''
    planYear.value = plan?.year  ?? new Date().getFullYear()

    const grouped: Record<number, BranchRow> = {}
    ;(plan?.branchTargets ?? []).forEach((bt: any) => {
      if (!grouped[bt.branchId]) grouped[bt.branchId] = mkRow(bt.branchId)
      const cat = bt.category as string
      if (cat === 'life' || cat === 'group_health') {
        grouped[bt.branchId][cat as keyof BranchRow] = bt.targetAmount
      } else {
        // Any property sub-category adds to general_property
        grouped[bt.branchId].general_property = (grouped[bt.branchId].general_property || 0) + (Number(bt.targetAmount) || 0)
      }
    })
    branchRows.value = Object.values(grouped)
  } catch (e) {
    console.error('[add.vue] Failed to load plan', e)
    formError.value = 'فشل تحميل بيانات الخطة للتعديل'
  }
}

onMounted(loadData)
watch(() => route.query.id, (newId, oldId) => {
  if (newId !== oldId && newId) loadData()
})

// ─── Submit & Confirm ─────────────────────────────────────────────────────────
const isSaving          = ref(false)
const formError         = ref('')
const showConfirmDialog = ref(false)

const submit = () => {
  if (!planName.value || !planYear.value) {
    formError.value = 'الرجاء تعبئة اسم الخطة والسنة'
    return
  }
  if (branchRows.value.filter(r => r.branchId).length === 0) {
    formError.value = 'الرجاء إضافة فرع واحد على الأقل وتعيين توزيعاته'
    return
  }
  formError.value = ''
  showConfirmDialog.value = true
}

const confirmAndSave = async () => {
  isSaving.value = true
  const validRows = branchRows.value.filter(r => r.branchId)

  const payload = {
    title:       planName.value,
    year:        planYear.value,
    totalAmount: grandBranchTotal.value,
    categories: [
      { category: 'life',             targetAmount: validRows.reduce((s, r) => s + (Number(r.life) || 0), 0) },
      { category: 'group_health',     targetAmount: validRows.reduce((s, r) => s + (Number(r.group_health) || 0), 0) },
      { category: 'general_property', targetAmount: validRows.reduce((s, r) => s + branchPropertyTotal(r), 0) },
    ],
    branchTargets: validRows.flatMap(r => {
      const propertyValue = Number(r.general_property) || 0
      // Distribute general_property equally across the 6 sub-categories
      const perCat = propertyValue / 6
      return [
        { branchId: r.branchId!, category: 'life',              targetAmount: Number(r.life) },
        { branchId: r.branchId!, category: 'group_health',      targetAmount: Number(r.group_health) },
        { branchId: r.branchId!, category: 'vehicle',           targetAmount: perCat },
        { branchId: r.branchId!, category: 'fire_theft',        targetAmount: perCat },
        { branchId: r.branchId!, category: 'transport_marine',  targetAmount: perCat },
        { branchId: r.branchId!, category: 'engineering',       targetAmount: perCat },
        { branchId: r.branchId!, category: 'personal_accident', targetAmount: perCat },
        { branchId: r.branchId!, category: 'cash',              targetAmount: perCat },
      ]
    }),
  }

  try {
    if (isEditMode.value) {
      await $api(`/apps/production-plans/${planId.value}`, { method: 'PUT', body: payload })
    } else {
      await $api('/apps/production-plans', { method: 'POST', body: payload })
    }
    showConfirmDialog.value = false
    router.push('/apps/production-plans/list')
  } catch (e: any) {
    if (!showPermissionError(e))
      formError.value = e?.data?.message ?? 'حدث خطأ أثناء الحفظ'
  } finally {
    isSaving.value = false
  }
}

const formatCurrency = (v: number) =>
  new Intl.NumberFormat('ar-IQ', { style: 'decimal', maximumFractionDigits: 0 }).format(v || 0) + ' د.ع'

const pct = (part: number, total: number) =>
  total > 0 ? ((part / total) * 100).toFixed(1) + '%' : '0%'
</script>

<template>
  <section>
    <!-- Page Header -->
    <div class="d-flex align-center justify-space-between mb-6">
      <div class="d-flex align-center gap-3">
        <VBtn
          icon
          variant="tonal"
          color="secondary"
          size="small"
          @click="router.push('/apps/production-plans/list')"
        >
          <VIcon icon="tabler-arrow-right" />
        </VBtn>
        <div>
          <h4 class="text-h4 font-weight-bold">{{ pageTitle }}</h4>
          <p class="text-body-2 text-medium-emphasis mb-0">أدخل بيانات الخطة ووزّع المبالغ على فئات التأمين</p>
        </div>
      </div>
      <div class="d-flex gap-3">
        <VBtn
          variant="tonal"
          color="secondary"
          @click="router.push('/apps/production-plans/list')"
        >
          إلغاء
        </VBtn>
        <VBtn
          color="primary"
          prepend-icon="tabler-device-floppy"
          :loading="isSaving"
          @click="submit"
        >
          {{ isEditMode ? 'تحديث الخطة' : 'حفظ الخطة' }}
        </VBtn>
      </div>
    </div>

    <!-- Error Alert -->
    <VAlert
      v-if="formError"
      type="error"
      variant="tonal"
      class="mb-6"
      closable
      @click:close="formError = ''"
    >
      {{ formError }}
    </VAlert>

    <VRow>
      <!-- ── RIGHT COLUMN: Plan Info + Category Distribution ── -->
      <VCol cols="12" lg="8">

        <!-- 1. Plan Info Card -->
        <VCard class="mb-6">
          <VCardItem>
            <template #prepend>
              <VAvatar color="primary" variant="tonal" rounded>
                <VIcon icon="tabler-clipboard-list" />
              </VAvatar>
            </template>
            <VCardTitle>بيانات الخطة الأساسية</VCardTitle>
          </VCardItem>
          <VDivider />
          <VCardText>
            <VRow>
              <VCol cols="12" md="8">
                <AppTextField
                  v-model="planName"
                  label="اسم الخطة الإنتاجية"
                  placeholder="الخطة الإنتاجية لعام 2025"
                  :rules="[requiredValidator]"
                  prepend-inner-icon="tabler-file-description"
                />
              </VCol>
              <VCol cols="12" md="4">
                <AppTextField
                  v-model="planYear"
                  label="السنة"
                  type="number"
                  placeholder="2025"
                  :rules="[requiredValidator]"
                  prepend-inner-icon="tabler-calendar"
                />
              </VCol>
              <VCol cols="12">
                <VAlert type="info" variant="tonal" density="compact" icon="tabler-calculator">
                  <span class="text-caption">
                    إجمالي الخطة يُحسب تلقائياً من مجموع مستهدفات الفروع:
                    <strong class="ms-1 text-primary">{{ formatCurrency(grandBranchTotal) }}</strong>
                  </span>
                </VAlert>
              </VCol>
            </VRow>
          </VCardText>
        </VCard>

        <!-- 3. Branch Targets Card (was Section 3) -->
        <VCard>
          <VCardItem>
            <template #prepend>
              <VAvatar color="secondary" variant="tonal" rounded>
                <VIcon icon="tabler-building" />
              </VAvatar>
            </template>
            <VCardTitle>مستهدفات الفروع</VCardTitle>
            <VCardSubtitle>لكل فرع مبلغه وتوزيعاته الخاصة — الإجمالي: <strong class="text-primary">{{ formatCurrency(grandBranchTotal) }}</strong></VCardSubtitle>
            <template #append>
              <VBtn size="small" variant="tonal"
                prepend-icon="tabler-plus" @click="addBranchRow">
                إضافة فرع
              </VBtn>
            </template>
          </VCardItem>
          <VDivider />

          <!-- Empty state -->
          <VCardText v-if="branchRows.length === 0" class="text-center text-medium-emphasis py-10">
            <VIcon icon="tabler-building-off" size="40" class="mb-3 d-block mx-auto" />
            <p class="mb-1">لم يتم إضافة مستهدفات الفروع بعد</p>
            <p class="text-caption">اضغط «إضافة فرع» لإضافة فرع بمبلغه وتوزيعاته الخاصة</p>
          </VCardText>

          <!-- Branch cards -->
          <VExpansionPanels v-else v-model="openPanels" multiple>
            <VExpansionPanel v-for="(row, idx) in branchRows" :key="idx">

              <!-- Header -->
              <VExpansionPanelTitle class="py-3 px-5">
                <div class="d-flex align-center justify-space-between w-100 pe-4 flex-wrap gap-2">
                  <!-- Branch selector -->
                  <div class="d-flex align-center gap-3" style="min-width:220px" @click.stop>
                    <VAvatar color="secondary" variant="tonal" rounded size="32">
                      <VIcon icon="tabler-building" size="16" />
                    </VAvatar>
                    <AppSelect
                      v-model="row.branchId"
                      :items="availableBranches(row)"
                      density="compact"
                      hide-details
                      placeholder="اختر الفرع"
                      style="min-width:180px"
                      @update:model-value="val => val && openPanel(idx)"
                    />
                  </div>
                  <!-- Totals summary + actions -->
                  <div class="d-flex align-center gap-3 flex-wrap">
                    <div class="text-center">
                      <div class="text-caption text-medium-emphasis">الإجمالي</div>
                      <div class="text-body-2 font-weight-bold text-primary">{{ formatCurrency(branchRowTotal(row)) }}</div>
                    </div>
                    <div class="text-center">
                      <div class="text-caption text-success">حياة</div>
                      <div class="text-caption font-weight-bold">{{ formatCurrency(Number(row.life)||0) }}</div>
                    </div>
                    <div class="text-center">
                      <div class="text-caption text-info">صحي</div>
                      <div class="text-caption font-weight-bold">{{ formatCurrency(Number(row.group_health)||0) }}</div>
                    </div>
                    <div class="text-center">
                      <div class="text-caption text-warning">ممتلكات</div>
                      <div class="text-caption font-weight-bold">{{ formatCurrency(branchPropertyTotal(row)) }}</div>
                    </div>
                    <!-- Copy from another branch (use object ref, not idx, to avoid teleport scope issues) -->
                    <div @click.stop>
                      <VBtn size="x-small" variant="tonal" color="info" icon>
                        <VIcon icon="tabler-copy" size="14" />
                        <VTooltip activator="parent">نسخ التوزيع من فرع آخر</VTooltip>
                        <VMenu activator="parent" :close-on-content-click="true">
                          <VList density="compact" min-width="200">
                            <VListSubheader>نسخ من،</VListSubheader>
                            <template v-for="other in branchRows" :key="other.branchId">
                              <VListItem
                                v-if="other !== row && other.branchId"
                                @click="copyBranch(other, row)"
                              >
                                <VListItemTitle class="text-body-2">
                                  {{ branchOptions.find(b => b.value === other.branchId)?.title || 'فرع' }}
                                </VListItemTitle>
                                <template #append>
                                  <span class="text-caption text-primary ms-3">{{ formatCurrency(branchRowTotal(other)) }}</span>
                                </template>
                              </VListItem>
                            </template>
                            <VListItem v-if="!branchRows.some(r => r !== row && r.branchId)" disabled>
                              <VListItemTitle class="text-caption text-medium-emphasis">لا توجد فروع أخرى مضافة</VListItemTitle>
                            </VListItem>
                          </VList>
                        </VMenu>
                      </VBtn>
                    </div>
                    <VBtn icon="tabler-trash" size="x-small" color="error" variant="text"
                      @click.stop="removeBranchRow(idx)" />
                  </div>
                </div>
              </VExpansionPanelTitle>

              <!-- Body -->
              <VExpansionPanelText class="pa-0">
                <VDivider />
                <div class="pa-5">
                  <VRow>

                    <!-- ── Branch Total Target ── -->
                    <VCol cols="12">
                      <VCard variant="tonal" color="primary" class="mb-1">
                        <VCardText class="py-3 px-4">
                          <VRow align="center" no-gutters>
                            <VCol cols="12" sm="5">
                              <AppTextField
                                v-model="row._branchTarget"
                                type="number"
                                hide-details
                                label="مجموع مستهدف الفرع (د.ع)"
                                placeholder="0"
                                density="compact"
                              />
                            </VCol>
                            <VCol cols="12" sm="7" class="ps-sm-4 pt-3 pt-sm-0">
                              <div class="d-flex flex-column gap-1">
                                <div class="d-flex justify-space-between text-caption">
                                  <span>موزّع:</span>
                                  <span class="font-weight-bold">{{ formatCurrency(branchRowTotal(row)) }}</span>
                                </div>
                                <div class="d-flex justify-space-between text-caption">
                                  <span>المتبقي:</span>
                                  <span
                                    class="font-weight-bold"
                                    :class="(Number(row._branchTarget)||0) - branchRowTotal(row) < 0 ? 'text-error' : (Number(row._branchTarget)||0) - branchRowTotal(row) === 0 && branchRowTotal(row) > 0 ? 'text-success' : ''"
                                  >
                                    {{ formatCurrency(Math.abs((Number(row._branchTarget)||0) - branchRowTotal(row))) }}
                                    <span v-if="(Number(row._branchTarget)||0) - branchRowTotal(row) < 0"> (زيادة)</span>
                                    <span v-else-if="(Number(row._branchTarget)||0) - branchRowTotal(row) === 0 && branchRowTotal(row) > 0"> ✓</span>
                                  </span>
                                </div>
                                <VProgressLinear
                                  v-if="Number(row._branchTarget) > 0"
                                  :model-value="Math.min((branchRowTotal(row) / Number(row._branchTarget)) * 100, 100)"
                                  :color="branchRowTotal(row) > Number(row._branchTarget) ? 'error' : branchRowTotal(row) === Number(row._branchTarget) ? 'success' : 'primary'"
                                  rounded height="6"
                                />
                              </div>
                            </VCol>
                          </VRow>
                        </VCardText>
                      </VCard>
                    </VCol>

                    <!-- ── Group 1 + 2: حياة وصحي جماعي جنباً إلى جنب ── -->
                    <VCol cols="12" sm="6">
                      <div class="d-flex align-center gap-2 mb-2">
                        <VIcon icon="tabler-heart-rate-monitor" color="success" size="16" />
                        <span class="text-caption font-weight-bold text-success">تأمين الحياة</span>
                      </div>
                      <AppTextField
                        v-model="row.life"
                        type="number" hide-details
                        label="وثيقة التأمين على الحياة (د.ع)"
                        placeholder="0"
                      />
                    </VCol>
                    <VCol cols="12" sm="6">
                      <div class="d-flex align-center gap-2 mb-2">
                        <VIcon icon="tabler-stethoscope" color="info" size="16" />
                        <span class="text-caption font-weight-bold text-info">الصحي الجماعي</span>
                      </div>
                      <AppTextField
                        v-model="row.group_health"
                        type="number" hide-details
                        label="وثيقة التأمين الصحي الجماعي (د.ع)"
                        placeholder="0"
                      />
                    </VCol>

                    <!-- ── Group 3: الممتلكات العامة ── -->
                    <VCol cols="12"><VDivider class="my-1" /></VCol>
                    <VCol cols="12">
                      <div class="d-flex align-center gap-2 mb-2">
                        <VIcon icon="tabler-building-store" color="warning" size="16" />
                        <span class="text-caption font-weight-bold text-warning">الممتلكات العامة</span>
                      </div>
                      <AppTextField
                        v-model="row.general_property"
                        type="number" hide-details
                        label="إجمالي الممتلكات العامة (د.ع)"
                        placeholder="0"
                      />
                      <VAlert type="info" variant="tonal" density="compact" class="mt-2" icon="tabler-info-circle">
                        <div class="text-caption font-weight-bold mb-1">تشمل الممتلكات العامة:</div>
                        <VRow dense class="mt-0">
                          <VCol cols="6" sm="4"><div class="d-flex align-center gap-1 text-caption"><VIcon icon="tabler-car" size="14" color="warning" /> تأمين السيارات</div></VCol>
                          <VCol cols="6" sm="4"><div class="d-flex align-center gap-1 text-caption"><VIcon icon="tabler-flame" size="14" color="warning" /> الحريق والسرقة</div></VCol>
                          <VCol cols="6" sm="4"><div class="d-flex align-center gap-1 text-caption"><VIcon icon="tabler-ship" size="14" color="warning" /> النقل / البحري</div></VCol>
                          <VCol cols="6" sm="4"><div class="d-flex align-center gap-1 text-caption"><VIcon icon="tabler-building-bridge" size="14" color="warning" /> التأمين الهندسي</div></VCol>
                          <VCol cols="6" sm="4"><div class="d-flex align-center gap-1 text-caption"><VIcon icon="tabler-bandage" size="14" color="warning" /> الحوادث الشخصية</div></VCol>
                          <VCol cols="6" sm="4"><div class="d-flex align-center gap-1 text-caption"><VIcon icon="tabler-cash" size="14" color="warning" /> تأمين النقد</div></VCol>
                        </VRow>
                      </VAlert>
                    </VCol>

                  </VRow>
                </div>
              </VExpansionPanelText>
            </VExpansionPanel>
          </VExpansionPanels>

          <!-- Grand total footer -->
          <VDivider v-if="branchRows.length > 0" />
          <VCardText v-if="branchRows.length > 0" class="pa-4">
            <VRow align="center" no-gutters>
              <VCol cols="auto" class="me-auto">
                <span class="text-body-2 font-weight-bold">مجموع جميع الفروع:</span>
              </VCol>
              <VCol cols="auto" class="text-center px-4">
                <div class="text-caption text-success">الحياة</div>
                <div class="text-body-2 font-weight-bold">{{ formatCurrency(branchRows.reduce((s,r)=>s+(Number(r.life)||0),0)) }}</div>
              </VCol>
              <VCol cols="auto" class="text-center px-4">
                <div class="text-caption text-info">الصحي</div>
                <div class="text-body-2 font-weight-bold">{{ formatCurrency(branchRows.reduce((s,r)=>s+(Number(r.group_health)||0),0)) }}</div>
              </VCol>
              <VCol cols="auto" class="text-center px-4">
                <div class="text-caption text-warning">الممتلكات</div>
                <div class="text-body-2 font-weight-bold">{{ formatCurrency(branchRows.reduce((s,r)=>s+branchPropertyTotal(r),0)) }}</div>
              </VCol>
              <VCol cols="auto" class="text-center px-4">
                <div class="text-caption text-medium-emphasis">الإجمالي الكلي</div>
                <div class="text-body-1 font-weight-black text-primary">{{ formatCurrency(grandBranchTotal) }}</div>
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </VCol>

      <!-- ── LEFT COLUMN: Summary Sidebar ── -->
      <VCol cols="12" lg="4">
        <VCard class="position-sticky" style="top: 80px;">
          <VCardItem>
            <template #prepend>
              <VAvatar color="primary" variant="tonal" rounded>
                <VIcon icon="tabler-report-analytics" />
              </VAvatar>
            </template>
            <VCardTitle>ملخص الخطة</VCardTitle>
          </VCardItem>
          <VDivider />
          <VCardText>
            <div class="d-flex flex-column gap-3 mb-4">
              <div class="d-flex justify-space-between">
                <span class="text-body-2 text-medium-emphasis">اسم الخطة</span>
                <span class="text-body-2 font-weight-medium">{{ planName || '—' }}</span>
              </div>
              <div class="d-flex justify-space-between">
                <span class="text-body-2 text-medium-emphasis">السنة</span>
                <span class="text-body-2 font-weight-medium">{{ planYear }}</span>
              </div>
              <div class="d-flex justify-space-between">
                <span class="text-body-2 text-medium-emphasis">عدد الفروع</span>
                <VChip size="small" color="secondary" label>
                  {{ branchRows.filter(r => r.branchId).length }} فرع
                </VChip>
              </div>
            </div>

            <VDivider class="mb-4" />

            <!-- Per-branch mini list -->
            <div v-if="branchRows.length > 0" class="d-flex flex-column gap-2 mb-4">
              <div
                v-for="(row, i) in branchRows.filter(r => r.branchId)"
                :key="i"
                class="d-flex justify-space-between align-center"
              >
                <span class="text-body-2 text-truncate" style="max-width:160px">
                  {{ branchOptions.find(b => b.value === row.branchId)?.title || '—' }}
                </span>
                <span class="text-body-2 font-weight-bold text-primary">
                  {{ formatCurrency(branchRowTotal(row)) }}
                </span>
              </div>
              <VDivider class="mt-1" />
              <div class="d-flex justify-space-between align-center">
                <span class="text-body-2 font-weight-bold">الإجمالي الكلي</span>
                <span class="text-h6 font-weight-black text-primary">
                  {{ formatCurrency(grandBranchTotal) }}
                </span>
              </div>
            </div>

            <VAlert
              v-if="branchRows.filter(r => r.branchId).length === 0"
              type="info" variant="tonal" density="compact" icon="tabler-building"
            >
              أضف فروعاً لتظهر هنا
            </VAlert>
          </VCardText>
          <VDivider />
          <VCardActions class="pa-4">
            <VBtn
              block color="primary" size="large"
              prepend-icon="tabler-device-floppy"
              @click="submit"
            >
              {{ isEditMode ? 'مراجعة وتحديث' : 'مراجعة وحفظ' }}
            </VBtn>
          </VCardActions>
        </VCard>
      </VCol>
    </VRow>

    <!-- ── Confirmation Dialog ── -->
    <VDialog v-model="showConfirmDialog" max-width="720" persistent>
      <VCard>
        <VCardItem class="py-3 px-4" density="compact">
          <template #prepend>
            <VAvatar color="primary" variant="tonal" rounded size="38" class="me-2">
              <VIcon icon="tabler-clipboard-check" size="20" />
            </VAvatar>
          </template>
          <VCardTitle class="text-subtitle-1 font-weight-bold">تأكيد {{ isEditMode ? 'تحديث' : 'حفظ' }} الخطة الإنتاجية</VCardTitle>
          <VCardSubtitle class="text-caption">{{ planName }} — {{ planYear }}</VCardSubtitle>
        </VCardItem>

        <VDivider thickness="1" />

        <VCardText class="pt-5">
          <VAlert type="info" variant="tonal" class="mb-4" icon="tabler-cash">
            <div class="d-flex justify-space-between align-center">
              <span>إجمالي الخطة (مجموع جميع الفروع)</span>
              <strong class="text-h6">{{ formatCurrency(grandBranchTotal) }}</strong>
            </div>
          </VAlert>

          <VTable density="comfortable" class="rounded border">
            <thead>
              <tr class="bg-var-theme-background">
                <th class="font-weight-bold">الفرع</th>
                <th class="text-center text-success font-weight-bold">حياة</th>
                <th class="text-center text-info font-weight-bold">صحي</th>
                <th class="text-center text-warning font-weight-bold">ممتلكات</th>
                <th class="text-center text-primary font-weight-bold">الإجمالي</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, i) in branchRows.filter(r => r.branchId)" :key="i">
                <td class="font-weight-medium">
                  {{ branchOptions.find(b => b.value === row.branchId)?.title || '—' }}
                </td>
                <td class="text-center text-success">{{ formatCurrency(Number(row.life)||0) }}</td>
                <td class="text-center text-info">{{ formatCurrency(Number(row.group_health)||0) }}</td>
                <td class="text-center text-warning">{{ formatCurrency(branchPropertyTotal(row)) }}</td>
                <td class="text-center font-weight-bold text-primary">{{ formatCurrency(branchRowTotal(row)) }}</td>
              </tr>
            </tbody>
            <tfoot>
              <tr class="bg-var-theme-background font-weight-black">
                <td class="font-weight-black">المجموع الكلي</td>
                <td class="text-center text-success font-weight-black">
                  {{ formatCurrency(branchRows.reduce((s,r)=>s+(Number(r.life)||0),0)) }}
                </td>
                <td class="text-center text-info font-weight-black">
                  {{ formatCurrency(branchRows.reduce((s,r)=>s+(Number(r.group_health)||0),0)) }}
                </td>
                <td class="text-center text-warning font-weight-black">
                  {{ formatCurrency(branchRows.reduce((s,r)=>s+branchPropertyTotal(r),0)) }}
                </td>
                <td class="text-center text-primary font-weight-black text-h6">
                  {{ formatCurrency(grandBranchTotal) }}
                </td>
              </tr>
            </tfoot>
          </VTable>
        </VCardText>
        <VDivider v-if="formError" />
        <VCardText v-if="formError" class="pb-0">
          <VAlert type="error" variant="tonal" closable @click:close="formError = ''">
            {{ formError }}
          </VAlert>
        </VCardText>

        <VDivider thickness="1" />

        <VCardActions class="justify-center gap-2 pa-3">
          <VBtn variant="tonal" color="secondary" class="px-4" @click="showConfirmDialog = false; formError = ''">
            <VIcon start icon="tabler-pencil" size="16" />
            تعديل
          </VBtn>
          <VBtn color="primary" class="px-4" :loading="isSaving" @click="confirmAndSave">
            <VIcon start icon="tabler-check" size="16" />
            تأكيد وحفظ الخطة
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

  </section>
</template>

<style scoped>
.hover-row:hover {
  background-color: rgba(var(--v-theme-surface-variant), 0.4);
}
</style>
