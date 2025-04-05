<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Instructor Schedule Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 15px;
            margin: 20px;
            color: #333;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }

        .header h2 {
            text-align: center;
            margin: 0;
            font-size: 22px;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        .instructor-info {
            text-align: right;
            font-size: 12px;
        }

        .instructor-info strong {
            font-size: 14px;
            color: #2c3e50;
        }

        .separator {
            border-top: 2px solid #2c3e50;
            margin: 15px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: left;
            font-size: 12px;
        }

        th {
            background-color: #ecf0f1;
            color: #2c3e50;
        }

        .footer {
            margin-top: 30px;
            font-size: 15px;
            text-align: center;
            color: #555;
        }
    </style>
</head>
<body>

<div class="header">
    <h2>Instructor Schedule Report</h2>
    <div class="instructor-info">
        <strong>{{ $instructor['first_name'] }} {{ $instructor['last_name'] }}</strong><br>
        Email: {{ $instructor['email'] }}<br>
        Department: {{ $instructor['department'] ?? 'N/A' }}
    </div>
</div>

<div class="separator"></div>

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
