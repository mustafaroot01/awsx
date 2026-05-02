<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>تفاصيل الخطة الإنتاجية</title>
    <style>
        body {
            font-family: 'dejavusans', sans-serif;
            font-size: 9px;
            direction: rtl;
            text-align: right;
        }
        .header-table {
            width: 100%;
            margin-bottom: 15px;
            border-bottom: 2px solid #333;
            padding-bottom: 8px;
        }
        .plan-title {
            font-size: 16px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        th {
            background-color: #f0f0f0;
            padding: 5px;
            border: 1px solid #999;
            text-align: center;
        }
        td {
            padding: 5px;
            border: 1px solid #ccc;
            text-align: center;
        }
        .branch-name {
            text-align: right;
            font-weight: bold;
            background-color: #fafafa;
        }
        .bg-light { background-color: #f9f9f9; }
        .text-success { color: #28a745; }
        .text-primary { color: #007bff; }
        .footer {
            margin-top: 15px;
            text-align: center;
            font-size: 8px;
            color: #777;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="border:none; text-align:right;">
                <div class="plan-title">{{ $plan->title }}</div>
                <div>الخطة الإنتاجية لعام: {{ $plan->year }}</div>
            </td>
            <td style="border:none; text-align:left;">
                <div>تاريخ التقرير: {{ $date }}</div>
                <div>إجمالي المستهدف: {{ number_format($plan->total_amount, 0) }} د.ع</div>
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th rowspan="2">الفرع</th>
                <th colspan="3">تأمين الحياة</th>
                <th colspan="3">الصحي الجماعي</th>
                <th colspan="3">الممتلكات العامة</th>
                <th colspan="3">الإجمالي</th>
            </tr>
            <tr>
                <th>هدف</th><th>منجز</th><th>%</th>
                <th>هدف</th><th>منجز</th><th>%</th>
                <th>هدف</th><th>منجز</th><th>%</th>
                <th>هدف</th><th>منجز</th><th>%</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totals = [
                    'life' => ['t'=>0, 'a'=>0],
                    'group_health' => ['t'=>0, 'a'=>0],
                    'general_property' => ['t'=>0, 'a'=>0],
                    'grand' => ['t'=>0, 'a'=>0]
                ];
            @endphp
            @foreach ($achievements as $row)
            @php
                $branchTotalTarget = 0;
                $branchTotalAchieved = 0;
            @endphp
            <tr>
                <td class="branch-name">{{ $row['branchName'] }}</td>
                
                @foreach (['life', 'group_health', 'general_property'] as $cat)
                @php
                    $t = $row['categories'][$cat]['target'];
                    $a = $row['categories'][$cat]['achieved'];
                    $p = $row['categories'][$cat]['percentage'];
                    $branchTotalTarget += $t;
                    $branchTotalAchieved += $a;
                    $totals[$cat]['t'] += $t;
                    $totals[$cat]['a'] += $a;
                @endphp
                <td>{{ number_format($t, 0) }}</td>
                <td class="text-success">{{ number_format($a, 0) }}</td>
                <td>{{ $p }}%</td>
                @endforeach

                @php
                    $totals['grand']['t'] += $branchTotalTarget;
                    $totals['grand']['a'] += $branchTotalAchieved;
                    $bp = $branchTotalTarget > 0 ? round(($branchTotalAchieved / $branchTotalTarget) * 100, 1) : 0;
                @endphp
                <td class="bg-light"><strong>{{ number_format($branchTotalTarget, 0) }}</strong></td>
                <td class="bg-light text-primary"><strong>{{ number_format($branchTotalAchieved, 0) }}</strong></td>
                <td class="bg-light"><strong>{{ $bp }}%</strong></td>
            </tr>
            @endforeach
        </tbody>
        <tfoot style="background-color: #eee; font-weight:bold;">
            <tr>
                <td>المجموع الكلي</td>
                @foreach (['life', 'group_health', 'general_property', 'grand'] as $cat)
                @php
                    $tt = $totals[$cat]['t'];
                    $aa = $totals[$cat]['a'];
                    $pp = $tt > 0 ? round(($aa / $tt) * 100, 1) : 0;
                @endphp
                <td>{{ number_format($tt, 0) }}</td>
                <td>{{ number_format($aa, 0) }}</td>
                <td>{{ $pp }}%</td>
                @endforeach
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        تم إنشاء هذا التقرير آلياً من نظام إدارة التأمين.
    </div>
</body>
</html>
