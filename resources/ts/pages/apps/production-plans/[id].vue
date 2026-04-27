<script setup lang="ts">
import { useRouter, useRoute } from 'vue-router'

definePage({
  meta: { action: 'read', subject: 'Auth' },
})

const router = useRouter()
const route  = useRoute()
const planId = computed(() => Number(route.params.id))

// ─── 8 Sub-categories meta ───────────────────────────────────────────────────
const SUB_META: Record<string, { label: string; icon: string }> = {
  life:             { label: 'تأمين الحياة',            icon: 'tabler-heart-rate-monitor' },
  group_health:     { label: 'الصحي الجماعي',           icon: 'tabler-stethoscope'         },
  vehicle:          { label: 'تأمين السيارات',           icon: 'tabler-car'                 },
  fire_theft:       { label: 'الحريق والسرقة',           icon: 'tabler-flame'               },
  transport_marine: { label: 'النقل / البحري',            icon: 'tabler-ship'                },
  engineering:      { label: 'التأمين الهندسي',          icon: 'tabler-building-factory'    },
  personal_accident:{ label: 'الحوادث الشخصية',         icon: 'tabler-first-aid-kit'       },
  cash:             { label: 'تأمين النقد',              icon: 'tabler-cash'                },
}

// ─── Fetch plan details ───────────────────────────────────────────────────────
const { data, execute: refresh } = await useApi<any>(`/apps/production-plans/${planId.value}`)

const plan         = computed(() => data.value?.plan ?? null)
const achievements = computed(() => (data.value?.achievements ?? []) as any[])

// ─── Helpers ──────────────────────────────────────────────────────────────────
const fmt = (v: number) =>
  new Intl.NumberFormat('ar-IQ', { style: 'decimal', maximumFractionDigits: 0 }).format(v || 0) + ' د.ع'

const pct = (achieved: number, target: number) =>
  target > 0 ? Math.min(+((achieved / target) * 100).toFixed(1), 999) : 0

const progressColor = (p: number) =>
  p >= 100 ? 'success' : p >= 70 ? 'warning' : 'error'

// ─── Category labels / colours ────────────────────────────────────────────────
const CAT_META: Record<string, { label: string; color: string; icon: string }> = {
  life:             { label: 'تأمين الحياة',       color: 'success', icon: 'tabler-heart-rate-monitor' },
  group_health:     { label: 'الصحي الجماعي',      color: 'info',    icon: 'tabler-stethoscope'        },
  general_property: { label: 'الممتلكات العامة',    color: 'warning', icon: 'tabler-building-store'     },
}

// ─── Overall totals ───────────────────────────────────────────────────────────
const totalTarget = computed(() =>
  achievements.value.reduce((s, r) =>
    s + Object.values(r.categories as Record<string, any>).reduce((cs: number, c: any) => cs + c.target, 0), 0)
)
const totalAchieved = computed(() =>
  achievements.value.reduce((s, r) =>
    s + Object.values(r.categories as Record<string, any>).reduce((cs: number, c: any) => cs + c.achieved, 0), 0)
)
const overallPct = computed(() => pct(totalAchieved.value, totalTarget.value))

// ─── Per-category totals across all branches ─────────────────────────────────
const categoryTotals = computed(() => {
  const cats: Record<string, { target: number; achieved: number }> = {
    life: { target: 0, achieved: 0 },
    group_health: { target: 0, achieved: 0 },
    general_property: { target: 0, achieved: 0 },
  }
  achievements.value.forEach(r => {
    Object.entries(r.categories as Record<string, any>).forEach(([cat, v]: [string, any]) => {
      if (cats[cat]) {
        cats[cat].target   += v.target
        cats[cat].achieved += v.achieved
      }
    })
  })
  return cats
})

// ─── Lock plan ────────────────────────────────────────────────────────────────
const locking = ref(false)
const lockPlan = async () => {
  locking.value = true
  await $api(`/apps/production-plans/${planId.value}/lock`, { method: 'POST' })
  await refresh()
  locking.value = false
}
</script>

<template>
  <section v-if="plan">

    <!-- ══════════════════════════════════════════════════════════════════════
         HEADER
    ══════════════════════════════════════════════════════════════════════ -->
    <VCard class="mb-5">
      <VCardText class="pa-4">
        <div class="d-flex flex-wrap align-center justify-space-between gap-3">
          <!-- Title block -->
          <div class="d-flex align-center gap-3">
            <VBtn icon variant="tonal" color="secondary" size="small"
              @click="router.push('/apps/production-plans/list')">
              <VIcon icon="tabler-arrow-right" />
            </VBtn>
            <VAvatar color="primary" variant="tonal" rounded size="46">
              <VIcon icon="tabler-clipboard-list" size="24" />
            </VAvatar>
            <div>
              <div class="d-flex align-center gap-2 flex-wrap">
                <span class="text-h5 font-weight-bold">{{ plan.title }}</span>
                <VChip :color="plan.isLocked ? 'error' : 'success'" size="small" label>
                  <VIcon start :icon="plan.isLocked ? 'tabler-lock' : 'tabler-lock-open'" size="13" />
                  {{ plan.isLocked ? 'مقفلة' : 'مفتوحة' }}
                </VChip>
              </div>
              <div class="text-body-2 text-medium-emphasis">
                الخطة الإنتاجية — عام {{ plan.year }}
                &nbsp;·&nbsp; {{ achievements.length }} فرع
                &nbsp;·&nbsp; أُنشئت {{ plan.createdAt }}
              </div>
            </div>
          </div>
          <!-- Actions -->
          <div class="d-flex gap-2 flex-wrap">
            <VBtn v-if="!plan.isLocked" size="small" variant="tonal" color="warning"
              prepend-icon="tabler-lock" :loading="locking" @click="lockPlan">
              قفل الخطة
            </VBtn>
            <VBtn v-if="!plan.isLocked" size="small" variant="tonal" prepend-icon="tabler-pencil"
              @click="router.push({ path: '/apps/production-plans/add', query: { id: plan.id } })">
              تعديل
            </VBtn>
            <VBtn size="small" variant="tonal" color="secondary" prepend-icon="tabler-list"
              @click="router.push('/apps/production-plans/list')">
              القائمة
            </VBtn>
          </div>
        </div>
      </VCardText>
    </VCard>

    <!-- ══════════════════════════════════════════════════════════════════════
         KPI STRIP  (4 stats in one card)
    ══════════════════════════════════════════════════════════════════════ -->
    <VCard class="mb-5">
      <VCardText class="pa-0">
        <VRow no-gutters>
          <VCol cols="6" sm="3" class="pa-4 border-e">
            <p class="text-caption text-medium-emphasis mb-1">إجمالي المستهدف</p>
            <p class="text-body-1 font-weight-bold text-primary mb-0">{{ fmt(plan.totalAmount) }}</p>
          </VCol>
          <VCol cols="6" sm="3" class="pa-4 border-e">
            <p class="text-caption text-medium-emphasis mb-1">إجمالي المُنجز</p>
            <p class="text-body-1 font-weight-bold text-success mb-0">{{ fmt(totalAchieved) }}</p>
          </VCol>
          <VCol cols="6" sm="3" class="pa-4 border-e">
            <p class="text-caption text-medium-emphasis mb-1">المتبقي</p>
            <p class="text-body-1 font-weight-bold text-warning mb-0">
              {{ fmt(Math.max(plan.totalAmount - totalAchieved, 0)) }}
            </p>
          </VCol>
          <VCol cols="6" sm="3" class="pa-4">
            <p class="text-caption text-medium-emphasis mb-1">نسبة الإنجاز الكلية</p>
            <div class="d-flex align-center gap-2">
              <p class="text-body-1 font-weight-bold mb-0"
                :class="`text-${progressColor(overallPct)}`">{{ overallPct }}%</p>
              <VProgressLinear
                :model-value="overallPct"
                :color="progressColor(overallPct)"
                rounded height="6"
                class="flex-1-1"
              />
            </div>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>

    <!-- ══════════════════════════════════════════════════════════════════════
         CATEGORY SUMMARY (3 horizontal cards)
    ══════════════════════════════════════════════════════════════════════ -->
    <VRow class="mb-5">
      <VCol
        v-for="(meta, cat) in CAT_META"
        :key="cat"
        cols="12" sm="4"
      >
        <VCard>
          <VCardText class="pa-4">
            <div class="d-flex justify-space-between align-center mb-3">
              <div class="d-flex align-center gap-2">
                <VAvatar :color="meta.color" variant="tonal" rounded size="34">
                  <VIcon :icon="meta.icon" size="18" />
                </VAvatar>
                <span class="font-weight-bold text-body-1">{{ meta.label }}</span>
              </div>
              <VChip
                :color="progressColor(pct(categoryTotals[cat].achieved, categoryTotals[cat].target))"
                size="small" label
              >
                {{ pct(categoryTotals[cat].achieved, categoryTotals[cat].target) }}%
              </VChip>
            </div>
            <VProgressLinear
              :model-value="pct(categoryTotals[cat].achieved, categoryTotals[cat].target)"
              :color="meta.color" rounded height="8"
              bg-color="rgba(0,0,0,0.07)" class="mb-2"
            />
            <div class="d-flex justify-space-between text-caption text-medium-emphasis">
              <span>منجز: <strong :class="`text-${meta.color}`">{{ fmt(categoryTotals[cat].achieved) }}</strong></span>
              <span>من: {{ fmt(categoryTotals[cat].target) }}</span>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- ══════════════════════════════════════════════════════════════════════
         BRANCHES  (collapsible accordion)
    ══════════════════════════════════════════════════════════════════════ -->
    <div class="d-flex align-center justify-space-between mb-3">
      <span class="text-h6 font-weight-bold">إنجازات الفروع</span>
      <span class="text-caption text-medium-emphasis">{{ achievements.length }} فرع — اضغط لعرض التفاصيل</span>
    </div>

    <div v-if="achievements.length === 0" class="text-center text-medium-emphasis py-12">
      <VIcon icon="tabler-building-off" size="48" class="mb-3 d-block mx-auto" />
      <p class="text-body-1">لا توجد بيانات فروع لهذه الخطة</p>
    </div>

    <VExpansionPanels v-else variant="accordion" class="mb-5">
      <VExpansionPanel
        v-for="row in achievements"
        :key="row.branchId"
      >
        <!-- ── Panel Header (always visible) ── -->
        <VExpansionPanelTitle class="py-3 px-5">
          <div class="d-flex align-center justify-space-between w-100 pe-4 flex-wrap gap-2">
            <!-- Branch name -->
            <div class="d-flex align-center gap-3">
              <VAvatar color="primary" variant="tonal" rounded size="34">
                <VIcon icon="tabler-building" size="18" />
              </VAvatar>
              <span class="font-weight-bold text-body-1">{{ row.branchName }}</span>
            </div>

            <!-- Quick stats chips -->
            <div class="d-flex align-center gap-3 flex-wrap">
              <span class="text-caption text-medium-emphasis">
                {{ fmt(Object.values(row.categories as Record<string,any>).reduce((s:number,c:any)=>s+c.achieved,0)) }}
                /
                {{ fmt(Object.values(row.categories as Record<string,any>).reduce((s:number,c:any)=>s+c.target,0)) }}
              </span>

              <!-- Mini progress per group -->
              <div
                v-for="(gm, gk) in CAT_META" :key="gk"
                class="d-flex align-center gap-1"
              >
                <VIcon :icon="gm.icon" :color="gm.color" size="14" />
                <span class="text-caption font-weight-medium"
                  :class="`text-${progressColor(row.categories[gk]?.percentage ?? 0)}`">
                  {{ row.categories[gk]?.percentage ?? 0 }}%
                </span>
              </div>

              <!-- Overall chip -->
              <VChip
                :color="progressColor(pct(
                  Object.values(row.categories as Record<string,any>).reduce((s:number,c:any)=>s+c.achieved,0),
                  Object.values(row.categories as Record<string,any>).reduce((s:number,c:any)=>s+c.target,0)
                ))"
                size="small" label
              >
                {{ pct(
                  Object.values(row.categories as Record<string,any>).reduce((s:number,c:any)=>s+c.achieved,0),
                  Object.values(row.categories as Record<string,any>).reduce((s:number,c:any)=>s+c.target,0)
                ) }}%
              </VChip>
            </div>
          </div>
        </VExpansionPanelTitle>

        <!-- ── Panel Body (expanded details) ── -->
        <VExpansionPanelText class="pa-0">
          <VDivider />

          <!-- Overall progress bar -->
          <div class="px-5 py-3 bg-var-theme-background">
            <div class="d-flex justify-space-between text-caption text-medium-emphasis mb-1">
              <span>الإجمالي الكلي للفرع</span>
              <span>
                {{ fmt(Object.values(row.categories as Record<string,any>).reduce((s:number,c:any)=>s+c.achieved,0)) }}
                من
                {{ fmt(Object.values(row.categories as Record<string,any>).reduce((s:number,c:any)=>s+c.target,0)) }}
              </span>
            </div>
            <VProgressLinear
              :model-value="pct(
                Object.values(row.categories as Record<string,any>).reduce((s:number,c:any)=>s+c.achieved,0),
                Object.values(row.categories as Record<string,any>).reduce((s:number,c:any)=>s+c.target,0)
              )"
              :color="progressColor(pct(
                Object.values(row.categories as Record<string,any>).reduce((s:number,c:any)=>s+c.achieved,0),
                Object.values(row.categories as Record<string,any>).reduce((s:number,c:any)=>s+c.target,0)
              ))"
              rounded height="10"
            />
          </div>

          <VDivider />

          <!-- 3 groups side-by-side -->
          <VRow no-gutters>
            <VCol
              v-for="(groupMeta, groupKey) in CAT_META"
              :key="groupKey"
              cols="12" md="4"
              :class="groupKey !== 'general_property' ? 'border-e' : ''"
            >
              <!-- Group header bar -->
              <div class="px-5 py-3 d-flex align-center justify-space-between"
                :class="`bg-light-${groupMeta.color}`">
                <div class="d-flex align-center gap-2">
                  <VIcon :icon="groupMeta.icon" :color="groupMeta.color" size="18" />
                  <span class="font-weight-bold text-body-2" :class="`text-${groupMeta.color}`">
                    {{ groupMeta.label }}
                  </span>
                </div>
                <VChip
                  :color="progressColor(row.categories[groupKey]?.percentage ?? 0)"
                  size="x-small" label
                >
                  {{ row.categories[groupKey]?.percentage ?? 0 }}%
                </VChip>
              </div>

              <!-- Group progress -->
              <div class="px-5 py-2">
                <VProgressLinear
                  :model-value="Math.min(row.categories[groupKey]?.percentage ?? 0, 100)"
                  :color="groupMeta.color" rounded height="6"
                  bg-color="rgba(0,0,0,0.07)" class="mb-1"
                />
                <div class="d-flex justify-space-between text-caption text-medium-emphasis">
                  <span>منجز: <strong :class="`text-${groupMeta.color}`">{{ fmt(row.categories[groupKey]?.achieved ?? 0) }}</strong></span>
                  <span>من: {{ fmt(row.categories[groupKey]?.target ?? 0) }}</span>
                </div>
              </div>

              <VDivider />

              <!-- Sub-categories list -->
              <div
                v-for="sub in row.categories[groupKey]?.breakdown ?? []"
                :key="sub.key"
                class="d-flex align-center justify-space-between px-5 py-2"
                style="border-bottom: 1px solid rgba(var(--v-border-color), var(--v-border-opacity))"
              >
                <div class="d-flex align-center gap-2">
                  <VIcon :icon="SUB_META[sub.key]?.icon ?? 'tabler-file'" size="16" color="medium-emphasis" />
                  <div>
                    <div class="text-body-2">{{ SUB_META[sub.key]?.label ?? sub.key }}</div>
                    <div class="text-caption text-medium-emphasis">{{ sub.count }} وثيقة</div>
                  </div>
                </div>
                <span
                  class="text-body-2 font-weight-bold"
                  :class="sub.achieved > 0 ? 'text-success' : 'text-disabled'"
                >
                  {{ fmt(sub.achieved) }}
                </span>
              </div>
            </VCol>
          </VRow>
        </VExpansionPanelText>
      </VExpansionPanel>
    </VExpansionPanels>

    <!-- ── Grand Total Summary ── -->
    <VCard v-if="achievements.length > 0" variant="tonal" color="primary">
      <VCardText>
        <VRow align="center" justify="space-between">
          <VCol cols="12" sm="auto">
            <p class="text-body-1 font-weight-bold mb-0">المجموع الكلي — {{ achievements.length }} فرع</p>
          </VCol>
          <VCol
            v-for="(meta, cat) in CAT_META"
            :key="cat"
            cols="auto"
            class="text-center"
          >
            <VIcon :icon="meta.icon" :color="meta.color" size="16" class="mb-1" />
            <p class="text-caption text-medium-emphasis mb-0">{{ meta.label }}</p>
            <p class="text-body-2 font-weight-bold mb-0">
              <span class="text-success">{{ fmt(categoryTotals[cat].achieved) }}</span>
              <span class="text-medium-emphasis"> / {{ fmt(categoryTotals[cat].target) }}</span>
            </p>
            <VChip
              :color="progressColor(pct(categoryTotals[cat].achieved, categoryTotals[cat].target))"
              size="x-small" class="mt-1"
            >
              {{ pct(categoryTotals[cat].achieved, categoryTotals[cat].target) }}%
            </VChip>
          </VCol>
          <VCol cols="auto" class="text-center">
            <p class="text-caption text-medium-emphasis mb-0">الإجمالي</p>
            <p class="text-body-1 font-weight-black text-success mb-0">{{ fmt(totalAchieved) }}</p>
            <p class="text-caption text-medium-emphasis mb-1">من {{ fmt(totalTarget) }}</p>
            <VChip :color="progressColor(overallPct)" size="small">{{ overallPct }}%</VChip>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>

  </section>

  <div v-else class="d-flex justify-center align-center" style="min-height:400px">
    <VProgressCircular indeterminate color="primary" />
  </div>
</template>
