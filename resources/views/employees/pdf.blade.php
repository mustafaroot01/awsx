<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>قائمة الموظفين</title>
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
    <h2>قائمة الموظفين</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                @if(in_array('name', $fields))<th>الاسم الكامل</th>@endif
                @if(in_array('gender', $fields))<th>الجنس</th>@endif
                @if(in_array('degree', $fields))<th>الدرجة</th>@endif
                @if(in_array('rank', $fields))<th>العنوان الوظيفي</th>@endif
                @if(in_array('jobTrack', $fields))<th>نوع الموظف</th>@endif
                @if(in_array('education', $fields))<th>الشهادة</th>@endif
                @if(in_array('jobType', $fields))<th>نوع الوظيفة</th>@endif
                @if(in_array('productionNo', $fields))<th>الرقم الإنتاجي</th>@endif
                @if(in_array('hireDate', $fields))<th>تاريخ التعيين</th>@endif
                @if(in_array('phone', $fields))<th>الهاتف</th>@endif
                @if(in_array('birthDate', $fields))<th>المواليد</th>@endif
                @if(in_array('nationalId', $fields))<th>البطاقة الوطنية</th>@endif
                @if(in_array('address', $fields))<th>العنوان</th>@endif
                @if(in_array('branch', $fields))<th>الفرع</th>@endif
            </tr>
        </thead>
        <tbody>
            @foreach ($employees as $index => $emp)
            <tr>
                <td>{{ $index + 1 }}</td>
                @if(in_array('name', $fields))<td style="text-align: right;">{{ $emp->full_name }}</td>@endif
                @if(in_array('gender', $fields))<td>{{ $emp->gender === 'male' ? 'ذكر' : 'أنثى' }}</td>@endif
                @if(in_array('degree', $fields))<td>{{ $emp->degree }}</td>@endif
                @if(in_array('rank', $fields))<td>{{ $emp->rank }}</td>@endif
                @if(in_array('jobTrack', $fields))<td>{{ $emp->job_track === 'producer' ? 'منتج' : 'إداري' }}</td>@endif
                @if(in_array('education', $fields))<td>{{ $emp->education }}</td>@endif
                @if(in_array('jobType', $fields))
                <td>
                    @if($emp->job_type === 'permanent')
                        تعيين ملاك
                    @elseif($emp->job_type === 'contract')
                        عقد
                    @else
                        أجر يومي
                    @endif
                </td>
                @endif
                @if(in_array('productionNo', $fields))<td>{{ $emp->production_no ?: '-' }}</td>@endif
                @if(in_array('hireDate', $fields))<td>{{ $emp->hire_date ? $emp->hire_date->format('Y-m-d') : '-' }}</td>@endif
                @if(in_array('phone', $fields))<td>{{ $emp->phone ?: '-' }}</td>@endif
                @if(in_array('birthDate', $fields))<td>{{ $emp->birth_date ? $emp->birth_date->format('Y-m-d') : '-' }}</td>@endif
                @if(in_array('nationalId', $fields))<td>{{ $emp->national_id ?: '-' }}</td>@endif
                @if(in_array('address', $fields))<td>{{ $emp->address ?: '-' }}</td>@endif
                @if(in_array('branch', $fields))<td>{{ $emp->branch?->name ?: '-' }}</td>@endif
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer">
        تم إنشاء هذا التقرير بتاريخ: {{ now()->format('Y-m-d H:i') }}
    </div>
</body>
</html>
