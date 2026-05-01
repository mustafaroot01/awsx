<script setup lang="ts">
import { useConfirmDelete } from '@/composables/useConfirmDelete'

const { state, close, confirm } = useConfirmDelete()
</script>

<template>
  <VDialog v-model="state.visible" max-width="420" persistent>
    <VCard>
      <!-- ── HEADER ── -->
      <VCardItem class="py-3 px-4" density="compact">
        <template #prepend>
          <VAvatar color="error" variant="tonal" size="38" rounded class="me-2">
            <VIcon icon="tabler-alert-triangle" size="20" />
          </VAvatar>
        </template>
        <VCardTitle class="text-subtitle-1 font-weight-bold">
          {{ state.title }}
        </VCardTitle>
      </VCardItem>

      <VDivider thickness="1" />

      <!-- ── BODY ── -->
      <VCardText class="py-4 text-body-2 text-medium-emphasis text-center">
        {{ state.message }}
      </VCardText>

      <VDivider thickness="1" />

      <!-- ── FOOTER ── -->
      <VCardActions class="justify-center gap-2 pa-3">
        <VBtn variant="tonal" color="secondary" class="px-4" @click="close">
          <VIcon start icon="tabler-x" size="16" />
          إلغاء
        </VBtn>
        <VBtn color="error" variant="elevated" class="px-4" @click="confirm">
          <VIcon start icon="tabler-trash" size="16" />
          {{ state.confirmLabel }}
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
