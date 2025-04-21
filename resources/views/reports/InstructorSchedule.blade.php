<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Instructor Schedule Report</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 14px;
            margin: 40px;
            color: #333;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }

        .header .title {
            font-size: 22px;
            font-weight: bold;
            color: #1e4a6b;
        }

        .instructor-info {
            text-align: right;
            font-size: 13px;
        }

        .instructor-info strong {
            color: #1e4a6b;
        }

        .section-title {
            font-size: 16px;
            margin-bottom: 10px;
            color: #1e4a6b;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px 10px;
            text-align: left;
        }

        th {
            background-color: #e4eef7;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .footer {
            position: absolute;
            bottom: 30px;
            left: 40px;
            right: 40px;
            text-align: center;
            font-size: 15px;
            color: #999;
        }

        @page {
            size: A4 landscape;
            margin: 0;
        }
    </style>
</head>

<body>

    <div class="header">
        <div class="title">Instructor Schedule Report</div>
        <div class="instructor-info">
            <strong>{{ $instructor['first_name'] }} {{ $instructor['last_name'] }}</strong><br>
            Email: {{ $instructor['email'] }}<br>
            Department: {{ $instructor['department'] ?? 'N/A' }}
        </div>
    </div>

    <div class="section-title">Teaching Schedule</div>
    <table>
        <thead>
            <tr>
                <th>Course Name</th>
                <th>Code</th>
                <th>Credits</th>
                <th>Room</th>
                <th>Term</th>
                <th>Year</th>
                <th>Days</th>
                <th>Time</th>
                <th>Section</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($courses as $course)
                <tr>
                    <td>{{ $course['course_name'] }}</td>
                    <td>{{ $course['course_code'] }}</td>
                    <td>{{ $course['credits'] }}</td>
                    <td>{{ $course['room_name'] }}</td>
                    <td>{{ $course['term'] }}</td>
                    <td>{{ $course['year'] }}</td>
                    <td>{{ implode('', $course['day_of_week']) }}</td>
                    <td>{{ $course['time_start'] }} - {{ $course['time_end'] }}</td>
                    <td>{{ $course['section_name'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Report generated on {{ \Carbon\Carbon::now()->format('F j Y \a\t g:i A') }}
    </div>

</body>

</html>
