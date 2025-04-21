<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Student Attendance Report - {{ $student->user->first_name }} {{ $student->user->last_name }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 13px;
            margin: 40px;
            color: #333;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 10px;
        }

        .logo-container img {
            width: 120px;
        }

        .main-title {
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            color: #1e4a6b;
            margin-bottom: 25px;
        }

        .student-info {
            text-align: left;
            margin-bottom: 20px;
            font-size: 15px;
            background-color: #f3f7fa;
            border-left: 4px solid #1e4a6b;
            padding: 15px 20px;
            width: fit-content;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            font-size: 15px;
            line-height: 1.6;
        }

        .student-info p {
            margin: 4px 0;
        }

        .section-title {
            font-weight: bold;
            text-align: center;
            font-size: 16px;
            color: #1e4a6b;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #e4eef7;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .summary {
            margin-top: 30px;
            text-align: center;
            font-size: 14px;
        }

        .summary span {
            font-weight: bold;
            color: #1e4a6b;
        }

        .footer {
            margin-top: 60px;
            text-align: center;
            font-size: 12px;
            color: #777;
            border-top: 1px dashed #ccc;
            padding-top: 10px;
        }

        .status-present {
            color: green;
            font-weight: bold;
        }

        .status-warning {
            color: orange;
            font-weight: bold;
        }

        .status-danger {
            color: red;
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

    <div class="logo-container">
        <img src="{{ public_path('docs/images/RHU-Logo.jpg') }}" alt="RHU Logo" style="width: 150px;">
    </div>

    <div class="main-title">Student Attendance Report</div>

    <div class="student-info">
        <p><strong>Full Name:</strong> {{ $student->user->first_name }} {{ $student->user->last_name }}</p>
        <p><strong>Student ID:</strong> {{ $student->student_id }}</p>
        <p><strong>Email:</strong> {{ $student->user->email ?? 'N/A' }}</p>
        <p><strong>Department:</strong> {{ $student->department->name ?? 'N/A' }}</p>
    </div>

    <div class="section-title">Attendance Details</div>

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
                    <td
                        class="status-{{ $course['absence_percentage'] < 10 ? 'present' : ($course['absence_percentage'] < 25 ? 'warning' : 'danger') }}">
                        {{ $course['status'] }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <div class="summary-box"
            style="margin-top: 10px; padding: 10px; background-color: #f3f7fa; border-left: 4px solid #1e4a6b;">
            Average Course Absence: <span class="summary-value" style="font-weight: 600">{{ $averageAbsence }}%</span>
        </div>
        Generated on {{ now()->format('Y-m-d H:i') }} — FYP team members
    </div>

</body>

</html>
