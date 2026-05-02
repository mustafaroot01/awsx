<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>قائمة الفروع</title>
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
    <h2>قائمة الفروع</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                @if(in_array('name', $fields))<th>اسم الفرع</th>@endif
                @if(in_array('location', $fields))<th>الموقع / العنوان</th>@endif
                @if(in_array('governorate', $fields))<th>المحافظة</th>@endif
                @if(in_array('managerName', $fields))<th>المدير</th>@endif
                @if(in_array('deputyName', $fields))<th>المعاون</th>@endif
            </tr>
        </thead>
        <tbody>
            @foreach ($branches as $index => $branch)
            <tr>
                <td>{{ $index + 1 }}</td>
                @if(in_array('name', $fields))<td style="text-align: right;">{{ $branch->name }}</td>@endif
                @if(in_array('location', $fields))<td>{{ $branch->location ?: '-' }}</td>@endif
                @if(in_array('governorate', $fields))<td>{{ $branch->governorate ?: '-' }}</td>@endif
                @if(in_array('managerName', $fields))<td>{{ $branch->manager?->name ?: '-' }}</td>@endif
                @if(in_array('deputyName', $fields))<td>{{ $branch->deputy?->name ?: '-' }}</td>@endif
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer">
        تم إنشاء هذا التقرير بتاريخ: {{ now()->format('Y-m-d H:i') }}
    </div>
</body>
</html>
