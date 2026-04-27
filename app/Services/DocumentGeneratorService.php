<?php

namespace App\Services;

use App\Models\Policy;
use TCPDF;

class DocumentGeneratorService
{
    public function generatePolicyDocument(Policy $policy)
    {
        $policy->load(['lifeDetails', 'fireTheftDetails', 'inspectionReports', 'beneficiaries', 'fundsSchedule', 'companyDetails', 'branch']);

        // Ensure category is cleaned for comparison
        $category = trim(strtolower($policy->category));

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator('Iraqi Insurance System');
        $pdf->SetTitle($this->getCategoryName($category) . ' - ' . $policy->policy_no);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->AddPage();
        $pdf->setRTL(true);
        $pdf->SetFont('dejavusans', '', 9);

        // --- Header Section ---
        $html = $this->renderHeader($policy);

        // --- 1. Client Personal Info ---
        $html .= $this->renderClientInfo($policy);

        // --- 2. Dynamic Plan Content ---
        switch ($category) {
            case 'life':
                $html .= $this->renderLifePlan($policy);
                break;
            case 'fire_theft':
                $html .= $this->renderFireTheftPlan($policy);
                break;
            default:
                $html .= $this->renderGenericPlan($policy);
                break;
        }

        // --- 3. Inspection Report (STRICTLY SKIP FOR LIFE) ---
        if ($category !== 'life' && $policy->inspectionReports->count() > 0) {
            $html .= $this->renderInspectionReport($policy->inspectionReports->first());
        }

        // --- 4. AML Compliance & Source of Funds ---
        $html .= $this->renderAMLSection($policy);

        // --- Footer ---
        $html .= $this->renderFooter();

        $pdf->writeHTML($html, true, false, true, false, '');
        return $pdf->Output('policy_' . $policy->policy_no . '.pdf', 'S');
    }

    private function renderHeader($policy)
    {
        $catName = $this->getCategoryName($policy->category);
        return '
        <table border="0" cellpadding="5">
            <tr>
                <td width="30%" align="center"><strong>وزارة المالية</strong><br><strong>شركة التأمين العراقية العامة</strong><br>الفرع: ' . ($policy->branch->name ?? 'المركز الرئيسي') . '</td>
                <td width="40%" align="center">
                    <h1 style="color: #1a237e; font-size: 17pt;">وثيقة ' . $catName . '</h1>
                    <span style="font-size: 12pt;">رقم الوثيقة: ' . $policy->policy_no . '</span>
                </td>
                <td width="30%" align="center">تاريخ الإصدار: ' . $policy->issue_date->format('Y-m-d') . '<br>تاريخ الانتهاء: ' . $policy->expiry_date->format('Y-m-d') . '</td>
            </tr>
        </table>
        <hr>';
    }

    private function renderClientInfo($policy)
    {
        return '
        <h3 style="background-color: #f0f4f8; color: #333; padding: 5px;">1. معلومات المؤمن له والعنوان</h3>
        <table border="1" cellpadding="4">
            <tr>
                <td width="20%" bgcolor="#f9f9f9"><strong>اسم المؤمن له:</strong></td>
                <td width="45%">' . $policy->client_name . '</td>
                <td width="15%" bgcolor="#f9f9f9"><strong>المهنة:</strong></td>
                <td width="20%">' . ($policy->occupation ?: '-') . '</td>
            </tr>
            <tr>
                <td bgcolor="#f9f9f9"><strong>العنوان الدائم:</strong></td>
                <td>' . ($policy->permanent_address ?: '-') . '</td>
                <td bgcolor="#f9f9f9"><strong>رقم الهاتف:</strong></td>
                <td>' . $policy->phone . '</td>
            </tr>
            <tr>
                <td bgcolor="#f9f9f9"><strong>الحي:</strong></td>
                <td>' . ($policy->district ?: '-') . '</td>
                <td bgcolor="#f9f9f9"><strong>المحلة:</strong></td>
                <td>' . ($policy->mahalla ?: '-') . '</td>
            </tr>
            <tr>
                <td bgcolor="#f9f9f9"><strong>الزقاق:</strong></td>
                <td>' . ($policy->zuqaq ?: '-') . '</td>
                <td bgcolor="#f9f9f9"><strong>مبلغ التأمين:</strong></td>
                <td>' . number_format($policy->amount) . ' د.ع</td>
            </tr>
            <tr>
                <td bgcolor="#f9f9f9"><strong>ملاحظات إضافية:</strong></td>
                <td colspan="3">' . ($policy->notes ?: '-') . '</td>
            </tr>
        </table>';
    }

    private function renderLifePlan($policy)
    {
        $ld = $policy->lifeDetails;
        if (!$ld)
            return '';

        $html = '<h3 style="background-color: #f0f4f8; color: #333; padding: 5px;">2. تفاصيل خطة التأمين (تأمين الحياة)</h3>
        <table border="1" cellpadding="4">
            <tr>
                <td width="20%" bgcolor="#f9f9f9"><strong>الحالة الاجتماعية:</strong></td>
                <td width="30%">' . $this->resolveMaritalStatus($ld->marital_status) . '</td>
                <td width="20%" bgcolor="#f9f9f9"><strong>رقم الهوية/البطاقة:</strong></td>
                <td width="30%">' . ($ld->id_card_no ?: '-') . '</td>
            </tr>
            <tr>
                <td bgcolor="#f9f9f9"><strong>جهة الإصدار:</strong></td>
                <td>' . ($ld->issue_authority_date ?: '-') . '</td>
                <td bgcolor="#f9f9f9"><strong>اسم الزوج/الزوجة:</strong></td>
                <td>' . ($ld->spouse_name ?: '-') . '</td>
            </tr>
            <tr>
                <td bgcolor="#f9f9f9"><strong>عنوان العمل:</strong></td>
                <td>' . ($ld->work_address ?: '-') . '</td>
                <td bgcolor="#f9f9f9"><strong>عنوان السكن:</strong></td>
                <td>' . ($ld->home_address_detail ?: '-') . '</td>
            </tr>
            <tr>
                <td bgcolor="#f9f9f9"><strong>الطول (سم):</strong></td>
                <td>' . ($ld->height_cm ?: '-') . '</td>
                <td bgcolor="#f9f9f9"><strong>الوزن (كغم):</strong></td>
                <td>' . ($ld->weight_kg ?: '-') . '</td>
            </tr>
            <tr>
                <td bgcolor="#f9f9f9"><strong>مدة التأمين:</strong></td>
                <td colspan="3">' . ($ld->duration_years ?: '1') . ' سنة</td>
            </tr>
        </table>';

        if ($policy->beneficiaries->count() > 0) {
            $html .= '<h4>قائمة المستفيدين:</h4>
            <table border="1" cellpadding="4">
                <tr bgcolor="#eeeeee" align="center">
                    <th width="40%">الاسم الرباعي</th>
                    <th width="20%">الصلة</th>
                    <th width="20%">حصة البقاء</th>
                    <th width="20%">حصة الوفاة</th>
                </tr>';
            foreach ($policy->beneficiaries as $ben) {
                $html .= '<tr align="center">
                    <td>' . ($ben->name_quad ?: $ben->name) . '</td>
                    <td>' . ($ben->relationship ?: $ben->relation) . '</td>
                    <td>' . ($ben->share_survival ?: '-') . '%</td>
                    <td>' . ($ben->share_death ?: $ben->percentage) . '%</td>
                </tr>';
            }
            $html .= '</table>';
        }

        if ($ld->health_questionnaire) {
            $html .= '<h4>الاستبيان الصحي:</h4>
            <table border="1" cellpadding="4">
                <tr bgcolor="#f9f9f9"><th>الفقرة الصحية</th><th width="15%" align="center">الإجابة</th></tr>';
            $qs = [
                "هل سبق واستلمت تقاعداً أو تعويض عجز؟",
                "تعاطي المشروبات الكحولية؟",
                "التدخين أو تعاطي المخدرات؟",
                "علاقة مع مصابين بالسل أو الصرع؟",
                "هل أنت بصحة جيدة اعتيادياً؟",
                "إصابات سابقة (قلب، سكري، شلل)؟",
                "فحوصات سابقة (أشعة، تخطيط)؟"
            ];
            foreach ($qs as $idx => $q) {
                $ans = ($ld->health_questionnaire[$idx] ?? '-') === 'yes' ? 'نعم' : 'لا';
                $html .= '<tr><td>' . ($idx + 1) . '. ' . $q . '</td><td align="center">' . $ans . '</td></tr>';
            }
            $html .= '</table>';
        }

        return $html;
    }

    private function renderFireTheftPlan($policy)
    {
        $html = '<h3 style="background-color: #f0f4f8; color: #333; padding: 5px;">2. تفاصيل خطة الحريق والسرقة</h3>
        <table border="1" cellpadding="4">
            <tr>
                <td width="20%" bgcolor="#f9f9f9"><strong>الاسم التجاري:</strong></td>
                <td width="30%">' . ($policy->trade_name ?: '-') . '</td>
                <td width="20%" bgcolor="#f9f9f9"><strong>هاتف الموقع:</strong></td>
                <td width="30%">' . ($policy->shop_phone ?: '-') . '</td>
            </tr>
            <tr>
                <td bgcolor="#f9f9f9"><strong>موقع المال:</strong></td>
                <td colspan="3">المنطقة: ' . ($policy->district ?: '-') . ' | الشارع: ' . ($policy->street_region ?: '-') . ' | المحل: ' . ($policy->shop_no ?: '-') . '</td>
            </tr>
        </table>';

        if ($policy->fundsSchedule->count() > 0) {
            $html .= '<h4>جدول مبالغ التأمين التفصيلي:</h4>
            <table border="1" cellpadding="4">
                <tr bgcolor="#eeeeee" align="center"><th>الصنف</th><th>المبلغ المطلوب</th><th>ملاحظات</th></tr>';
            $map = ['building' => 'البناء', 'goods' => 'البضائع', 'machinery' => 'المكائن', 'furniture' => 'الأثاث', 'others' => 'أخرى'];
            foreach ($policy->fundsSchedule as $f) {
                $html .= '<tr><td>' . ($map[$f->category] ?? $f->category) . '</td><td align="center">' . number_format($f->value) . '</td><td>' . $f->description . '</td></tr>';
            }
            $html .= '</table>';
        }

        return $html;
    }

    private function renderGenericPlan($policy)
    {
        return '<p align="center">خطة تأمين: ' . $this->getCategoryName($policy->category) . '</p>';
    }

    private function renderInspectionReport($report)
    {
        return '
        <h3 style="background-color: #f0f4f8; color: #333; padding: 5px;">3. تقرير الكشف الفني</h3>
        <table border="1" cellpadding="4">
            <tr><td width="20%" bgcolor="#f9f9f9"><strong>وصف البناء:</strong></td><td width="80%">' . ($report->construction_description ?: '-') . '</td></tr>
            <tr><td bgcolor="#f9f9f9"><strong>المواد:</strong></td><td>الجدران: ' . $report->wall_material . ' | السقف: ' . $report->roof_material . '</td></tr>
            <tr><td bgcolor="#f9f9f9"><strong>الأمان:</strong></td><td>الأقفال: ' . $report->doors_locks_type . ' | المطافئ: ' . $report->extinguishers . '</td></tr>
        </table>';
    }

    private function renderAMLSection($policy)
    {
        $html = '
        <h3 style="background-color: #f0f4f8; color: #333; padding: 5px;">4. مكافحة غسل الأموال ومعلومات الشركة (AML/KYB)</h3>
        <table border="1" cellpadding="4">
            <tr>
                <td width="20%" bgcolor="#f9f9f9"><strong>مصدر الأموال:</strong></td>
                <td width="30%">' . $this->translateSourceOfFunds($policy->source_of_funds) . '</td>
                <td width="20%" bgcolor="#f9f9f9"><strong>الدخل الشهري:</strong></td>
                <td width="30%">' . $this->resolveIncomeLabel($policy->monthly_income) . '</td>
            </tr>
            <tr>
                <td bgcolor="#f9f9f9"><strong>نوع العمل:</strong></td>
                <td>' . ($policy->business_type ?: '-') . '</td>
                <td bgcolor="#f9f9f9"><strong>موظف الالتزام (AML):</strong></td>
                <td>' . ($policy->aml_officer_name ?: '-') . '</td>
            </tr>
        </table>';

        // --- KYB: Company Details (إن وجد) ---
        $co = $policy->companyDetails;
        if ($co) {
            $html .= '
        <h4 style="background-color: #f9f9f9; padding: 4px;">بيانات الشركة (إن وجد)</h4>
        <table border="1" cellpadding="4">
            <tr>
                <td width="20%" bgcolor="#f9f9f9"><strong>المفوض بإدارة الشركة:</strong></td>
                <td width="30%">' . ($co->manager_name ?: ($co->authorized_name ?: '-')) . '</td>
                <td width="20%" bgcolor="#f9f9f9"><strong>نوع نشاط الشركة:</strong></td>
                <td width="30%">' . ($co->activity_type ?: '-') . '</td>
            </tr>
            <tr>
                <td bgcolor="#f9f9f9"><strong>تاريخ التأسيس:</strong></td>
                <td>' . ($co->founding_date ? $co->founding_date->format('Y-m-d') : '-') . '</td>
                <td bgcolor="#f9f9f9"><strong>رأس المال المدفوع:</strong></td>
                <td>' . ($co->capital ? number_format($co->capital) . ' د.ع' : '-') . '</td>
            </tr>
            <tr>
                <td bgcolor="#f9f9f9"><strong>مكان التأسيس:</strong></td>
                <td>' . ($co->founding_place ?: '-') . '</td>
                <td bgcolor="#f9f9f9"><strong>المدقق الخارجي:</strong></td>
                <td>' . ($co->external_auditor_name ?: '-') . '</td>
            </tr>';

            if ($co->authorized_name && $co->authorized_name !== $co->manager_name) {
                $html .= '
            <tr>
                <td bgcolor="#f9f9f9"><strong>المفوض المعتمد:</strong></td>
                <td colspan="3">' . $co->authorized_name . '</td>
            </tr>';
            }

            if ($co->founder_names) {
                $html .= '
            <tr>
                <td bgcolor="#f9f9f9"><strong>أسماء المؤسسين:</strong></td>
                <td colspan="3">' . $co->founder_names . '</td>
            </tr>';
            }

            if ($co->shareholder_names) {
                $html .= '
            <tr>
                <td bgcolor="#f9f9f9"><strong>أسماء المساهمين:</strong></td>
                <td colspan="3">' . $co->shareholder_names . '</td>
            </tr>';
            }

            $html .= '</table>';
        }

        return $html;
    }

    private function renderFooter()
    {
        return '
        <br><br>
        <table border="0">
            <tr><td align="center">توقيع المؤمن له<br><br>________________</td><td align="center">ختم وتوقيع الشركة<br><br><strong>  </strong></td></tr>
        </table>';
    }

    private function getCategoryName($cat)
    {
        $map = [
            'life' => 'تأمين على الحياة',
            'fire_theft' => 'تأمين الحريق والسرقة',
            'vehicle' => 'تأمين السيارات',
            'group_health' => 'التأمين الصحي الجماعي',
            'transport_marine' => 'التأمين البحري والنقل',
            'engineering' => 'التأمين الهندسي',
            'personal_accident' => 'تأمين الحوادث الشخصية',
            'cash' => 'تأمين النقدية'
        ];
        return $map[trim(strtolower($cat))] ?? $cat;
    }

    private function resolveIncomeLabel($val)
    {
        $map = ['under_1m' => 'أقل من مليون', '1m_3m' => '1-3 مليون', '3m_5m' => '3-5 مليون', 'over_5m' => 'أكثر من 5 مليون'];
        return $map[$val] ?? $val;
    }

    private function resolveMaritalStatus($val)
    {
        $map = ['married' => 'متزوج', 'single' => 'أعزب', 'widowed' => 'أرمل', 'divorced' => 'مطلق'];
        return $map[$val] ?? $val;
    }

    private function translateSourceOfFunds($str)
    {
        if (!$str)
            return '-';
        $map = [
            'salary' => 'راتب',
            'business' => 'عمل خاص',
            'savings' => 'مدخرات',
            'inheritance' => 'ميراث',
            'others' => 'أخرى',
        ];
        $keys = explode(',', $str);
        $translated = array_map(fn($k) => $map[trim(strtolower($k))] ?? $k, $keys);
        return implode('، ', $translated);
    }
}
