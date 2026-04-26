<script setup lang="ts">
import RoleCards from '@/views/apps/roles/RoleCards.vue'
</script>

<template>
  <VRow>
    <VCol cols="12">
      <h4 class="text-h4 mb-1">
        قائمة المجموعات والصلاحيات
      </h4>
      <p class="text-body-1 mb-0">
        تسمح لك المجموعات بتحديد مستوى الوصول لكل مستخدم. يمكنك تعيين صلاحيات دقيقة لكل مجموعة للتحكم في (العرض، الإضافة، التعديل، الحذف).
      </p>
    </VCol>

    <!-- 👉 Roles Cards -->
    <VCol cols="12">
      <RoleCards />
    </VCol>
  </VRow>
</template>
