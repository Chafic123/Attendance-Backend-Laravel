<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attendance Report - {{ $course->name }}</title>
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

        .course-info {
            text-align: right;
            font-size: 13px;
        }

        .course-info strong {
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

        th, td {
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

        .status-present {
            color: #2f855a;
            font-weight: bold;
        }

        .status-warning {
            color: #dd6b20;
            font-weight: bold;
        }

        .status-danger {
            color: #c53030;
            font-weight: bold;
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
        <div class="title">Course Attendance Report</div>
        <div class="course-info">
            <p><strong>{{ $course->name }} ({{ $course->Code }})</strong></p>
            <p>Instructor: {{ optional(optional($instructor)->user)->first_name }} {{ optional(optional($instructor)->user)->last_name }}</p>
            <p>Section: {{ $course->Section ?? 'N/A' }}</p>
            <p>Day: {{ ucfirst($course->day_of_week) ?? 'N/A' }}</p>
            <p>Time: 
                {{ \Carbon\Carbon::parse($course->start_time)->format('g:i A') ?? 'N/A' }} - 
                {{ \Carbon\Carbon::parse($course->end_time)->format('g:i A') ?? 'N/A' }}
            </p>
        </div>
    </div>

    <div class="section-title">Student Attendance</div>
    <table>
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Department</th>
                <th>Major</th>
                <th>Absence %</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($students as $student)
                <tr>
                    <td>{{ $student['student_id'] }}</td>
                    <td>{{ $student['first_name'] }} {{ $student['last_name'] }}</td>
                    <td>{{ $student['email'] }}</td>
                    <td>{{ $student['department'] }}</td>
                    <td>{{ $student['major'] }}</td>
                    <td>{{ $student['absence_percentage'] }}%</td>
                    <td class="status-{{ 
                        $student['absence_percentage'] < 10 ? 'present' : 
                        ($student['absence_percentage'] < 20 ? 'warning' : 'danger') 
                    }}">
                        {{ $student['status'] }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
            <div class="summary-box">
                Average Course Absence: <span class="summary-value">{{ $averageAbsence }}%</span>
            </div>
        Generated on {{ now()->format('Y-m-d H:i') }} — FYP team members
    </div>

</body>
</html>
