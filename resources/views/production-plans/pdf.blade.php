<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>الخطط الإنتاجية</title>
    <style>
        body {
            font-family: 'dejavusans', sans-serif;
            font-size: 10px;
            direction: rtl;
            text-align: right;
        }
        h2 {
            text-align: center;
            margin-bottom: 12px;
            font-size: 16px;
            border-bottom: 2px solid #333;
            padding-bottom: 6px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        th {
            background-color: #4a4a4a;
            color: #fff;
            padding: 6px 4px;
            font-weight: bold;
            border: 1px solid #333;
            text-align: center;
            font-size: 9px;
        }
        td {
            padding: 5px 4px;
            border: 1px solid #999;
            text-align: center;
            font-size: 9px;
        }
        tr:nth-child(even) {
            background-color: #f5f5f5;
        }
        .footer {
            margin-top: 12px;
            text-align: center;
            font-size: 8px;
            color: #666;
        }
    </style>
</head>
<body>
    <h2>قائمة الخطط الإنتاجية</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                @if(in_array('year', $fields))<th>السنة</th>@endif
                @if(in_array('title', $fields))<th>العنوان</th>@endif
                @if(in_array('total_amount', $fields))<th>إجمالي الخطة</th>@endif
                @if(in_array('is_locked', $fields))<th>الحالة</th>@endif
                @if(in_array('branches', $fields))<th>تفاصيل الفروع</th>@endif
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                @if(in_array('year', $fields))<td>{{ $item->year }}</td>@endif
                @if(in_array('title', $fields))<td style="text-align: right;">{{ $item->title }}</td>@endif
                @if(in_array('total_amount', $fields))<td>{{ number_format($item->total_amount, 0) }} د.ع</td>@endif
                @if(in_array('is_locked', $fields))<td>{{ $item->is_locked ? 'مقفلة' : 'مفتوحة' }}</td>@endif
                @if(in_array('branches', $fields))
                <td style="text-align: right; font-size: 8px;">
                    @php
                        $branchSummary = $item->branchTargets->groupBy('branch_id')->map(function($targets) {
                            return [
                                'name'     => $targets->first()->branch?->name ?? 'فرع',
                                'total'    => $targets->sum('target_amount'),
                                'life'     => $targets->where('category', 'life')->sum('target_amount'),
                                'health'   => $targets->where('category', 'group_health')->sum('target_amount'),
                                'property' => $targets->whereNotIn('category', ['life', 'group_health'])->sum('target_amount'),
                            ];
                        });
                    @endphp
                    @foreach($branchSummary as $bs)
                        <div style="margin-bottom: 4px; border-bottom: 1px dashed #eee; padding-bottom: 2px;">
                            <strong>{{ $bs['name'] }}: {{ number_format($bs['total'], 0) }} د.ع</strong>
                            <div style="color: #666; margin-right: 8px;">
                                (حياة: {{ number_format($bs['life'], 0) }} | 
                                صحي: {{ number_format($bs['health'], 0) }} | 
                                ممتلكات: {{ number_format($bs['property'], 0) }})
                            </div>
                        </div>
                    @endforeach
                </td>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer">
        تم إنشاء هذا التقرير بتاريخ: {{ now()->format('Y-m-d H:i') }}
    </div>
</body>
</html>
