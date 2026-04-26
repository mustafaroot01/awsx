<script setup lang="ts">
import type { Evaluation } from '@db/apps/evaluations/types'

interface Props {
  evaluation: any
  allEvaluations?: any[] // To show the periodic table
}

const props = defineProps<Props>()

const formatDate = (date: string) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('ar-EG')
}

const periodicLabels = [
  'كانون الثاني وشباط',
  'آذار ونيسان',
  'أيار وحزيران',
  'تموز وآب',
  'أيلول وتشرين الأول',
  'تشرين الثاني وكانون الأول'
]

// Map period_no to index in our table
const getPeriodData = (periodNo: number) => {
  return props.allEvaluations?.find(e => e.periodNo === periodNo)
}
</script>

<template>
  <div class="evaluation-form-paper pa-8" id="printable-form">
    <!-- Header -->
    <div class="text-center mb-6">
      <h2 class="text-h4 font-weight-bold mb-2">استمارة تقييم كفاءة الأداء للموظفين</h2>
      <div class="text-subtitle-1">تقرير دوري لسنة {{ evaluation.year }}</div>
    </div>

    <!-- Section 1: Employee Info -->
    <div class="section-title">أولاً: معلومات الموظف والتقرير</div>
    <VRow class="mb-6 info-grid">
      <VCol cols="4"><strong>رقم التسلسل:</strong> {{ evaluation.employeeNo }}</VCol>
      <VCol cols="4"><strong>تاريخ التقرير:</strong> {{ formatDate(evaluation.createdAt) }}</VCol>
      <VCol cols="4"><strong>القسم / الفرع:</strong> {{ evaluation.branchName }}</VCol>
      
      <VCol cols="6"><strong>اسم الموظف الكامل:</strong> {{ evaluation.employeeName }}</VCol>
      <VCol cols="6"><strong>عنوان الوظيفة:</strong> {{ evaluation.rank || '-' }}</VCol>
      
      <VCol cols="4"><strong>التحصيل العلمي:</strong> {{ evaluation.education || '-' }}</VCol>
      <VCol cols="4"><strong>مدة الخدمة:</strong> {{ evaluation.serviceYears || '-' }} سنة</VCol>
      <VCol cols="4"><strong>الدرجة الوظيفية:</strong> {{ evaluation.degree || '-' }}</VCol>
    </VRow>

    <!-- Section 2: Performance Evaluation Table -->
    <div class="section-title">ثانياً: جدول تقييم الأداء</div>
    <VTable class="evaluation-table mb-6" bordered>
      <thead>
        <tr>
          <th class="text-center">ت</th>
          <th>موضوع التقييم</th>
          <th class="text-center">التقييم</th>
        </tr>
      </thead>
      <tbody>
        <tr><td class="text-center">1</td><td>الكفاءة والخبرة</td><td class="text-center">{{ evaluation.efficiencyExperience || '-' }}</td></tr>
        <tr><td class="text-center">2</td><td>سرعة الإنجاز</td><td class="text-center">{{ evaluation.speedOfAchievement || '-' }}</td></tr>
        <tr><td class="text-center">3</td><td>الشعور بالمسؤولية</td><td class="text-center">{{ evaluation.senseOfResponsibility || '-' }}</td></tr>
        <tr><td class="text-center">4</td><td>سلوكه مع رؤسائه ومرؤوسيه والمراجعين</td><td class="text-center">{{ evaluation.behaviorWithOthers || '-' }}</td></tr>
        <tr><td class="text-center">5</td><td>المحافظة على أوقات الدوام الرسمي</td><td class="text-center">{{ evaluation.attendanceCommitment || '-' }}</td></tr>
        <tr><td class="text-center">6</td><td>كتب الشكر والعقوبات</td><td class="text-center">{{ evaluation.appreciationPenalties || '-' }}</td></tr>
      </tbody>
    </VTable>

    <!-- Section 3: Periodic Points Table -->
    <div class="section-title">ثالثاً: جدول احتساب النقاط الدوري</div>
    <VTable class="periodic-table mb-8" bordered>
      <thead>
        <tr>
          <th>الفترة</th>
          <th class="text-center">ن. الكفاءة</th>
          <th class="text-center">ن. العنوان</th>
          <th class="text-center">ن. الشهادة</th>
          <th class="text-center">ن. الخدمة</th>
          <th class="text-center">المجموع</th>
          <th class="text-center">التقييم</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(label, idx) in periodicLabels" :key="idx">
          <td>{{ label }}</td>
          <template v-if="getPeriodData(idx + 1)">
            <td class="text-center">{{ getPeriodData(idx + 1).pointsCompetency }}</td>
            <td class="text-center">{{ getPeriodData(idx + 1).pointsGrade }}</td>
            <td class="text-center">{{ getPeriodData(idx + 1).pointsEducation }}</td>
            <td class="text-center">{{ getPeriodData(idx + 1).pointsService }}</td>
            <td class="text-center font-weight-bold">{{ getPeriodData(idx + 1).pointsTotal }}</td>
            <td class="text-center">{{ getPeriodData(idx + 1).grade }}</td>
          </template>
          <template v-else>
            <td v-for="n in 6" :key="n" class="text-center text-disabled">-</td>
          </template>
        </tr>
      </tbody>
    </VTable>

    <!-- Section 4: Signatures -->
    <VRow class="mt-12 pt-12">
      <VCol cols="6" class="text-center">
        <div class="signature-line mb-2"></div>
        <div class="font-weight-bold">توقيع اللجنة</div>
      </VCol>
      <VCol cols="6" class="text-center">
        <div class="signature-line mb-2"></div>
        <div class="font-weight-bold">توقيع المدير العام</div>
      </VCol>
    </VRow>
  </div>
</template>

<style scoped>
.evaluation-form-paper {
  background-color: white;
  color: black;
  font-family: 'Amiri', serif;
  border: 2px solid #333;
}

.section-title {
  background-color: #f5f5f5;
  padding: 8px 12px;
  font-weight: bold;
  border-right: 4px solid #333;
  margin-bottom: 16px;
  font-size: 1.1rem;
}

.info-grid strong {
  color: #555;
}

.evaluation-table th, .periodic-table th {
  background-color: #fafafa !important;
  font-weight: bold !important;
  color: black !important;
  border: 1px solid #ddd !important;
}

.evaluation-table td, .periodic-table td {
  border: 1px solid #ddd !important;
}

.signature-line {
  border-bottom: 1px dashed #333;
  width: 200px;
  margin: 0 auto;
  height: 40px;
}

@media print {
  body * {
    visibility: hidden;
  }
  #printable-form, #printable-form * {
    visibility: visible;
  }
  #printable-form {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
  }
}
</style>
