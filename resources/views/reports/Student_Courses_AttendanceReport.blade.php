<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Attendance Report - {{ $student->user->first_name }} {{ $student->user->last_name }}</title>
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

        .student-info {
            text-align: right;
            font-size: 13px;
        }

        .student-info strong {
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
            text-align: center;
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

        .summary-box {
            margin-top: 20px;
            font-size: 15px;
            text-align: center;
        }

        .summary-value {
            font-weight: bold;
            color: #1e4a6b;
        }

        .status-present {
            color: green;
        }

        .status-warning {
            color: orange;
        }

        .status-danger {
            color: red;
        }

        @page {
            size: A4 landscape;
            margin: 0;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="title">Student Attendance Report</div>
        <div class="student-info">
            <p><strong>{{ $student->user->first_name }} {{ $student->user->last_name }}</strong></p>
            <p>ID: {{ $student->student_id }}</p>
            <p>Email: {{ $student->user->email ?? 'N/A' }}</p>
            <p>Department: {{ $student->department->name ?? 'N/A' }}</p>
        </div>
    </div>

    <div class="section-title">Attendance Summary</div>

    <table>
        <thead>
            <tr>
                <th>Course Code</th>
                <th>Course Name</th>
                <th>Section</th>
                <th>Credits</th>
                <th>Instructor</th>
                <th>Absence %</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($courses as $course)
                <tr>
                    <td>{{ $course['course_code'] }}</td>
                    <td>{{ $course['course_name'] }}</td>
                    <td>{{ $course['section'] }}</td>
                    <td>{{ $course['credits'] }}</td>
                    <td>{{ $course['instructor'] }}</td>
                    <td>{{ $course['absence_percentage'] }}%</td>
                    <td class="status-{{ 
                        $course['absence_percentage'] < 10 ? 'present' : 
                        ($course['absence_percentage'] < 25 ? 'warning' : 'danger') 
                    }}">
                        {{ $course['status'] }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Generated on {{ now()->format('Y-m-d H:i') }} — FYP team members
        <div class="summary-box">
            <br>
            Average Absence: <span class="summary-value">{{ $averageAbsence }}%</span><br>
        </div>
    </div>

</body>
</html>
