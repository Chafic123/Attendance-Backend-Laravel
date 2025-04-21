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

        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo-container img {
            width: 120px;
            height: auto;
        }

        .course-info-container {
            display: flex;
            justify-content: flex-end;
            margin-top: 10px;
        }

        .course-info-box {
            background-color: #f3f7fa;
            border-left: 4px solid #1e4a6b;
            padding: 15px 20px;
            width: fit-content;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            font-size: 15px;
            line-height: 1.6;
        }

        .course-info-box p {
            margin: 2px 0;
        }

        .course-info-box strong {
            color: #1e4a6b;
        }

        .section-title {
            margin-top: 5px;
            text-align: center;
            font-size: 18px;
            margin-bottom: 10px;
            color: #1e4a6b;
            border-bottom: 1px solid #ccc;
            padding-bottom: 10px;
            font-weight: bold;
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
            border-top: 1px dashed #ccc;
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

    <div class="course-info-container">
        <div class="course-info-box">
            <p><strong>Course:</strong> {{ $course->name }} ({{ $course->Code }})</p>
            <p><strong>Instructor:</strong> {{ optional(optional($instructor)->user)->first_name }}
                {{ optional(optional($instructor)->user)->last_name }}</p>
            <p><strong>Section:</strong> {{ $course->Section ?? 'N/A' }}</p>
            <p><strong>Day:</strong> {{ ucfirst($course->day_of_week) ?? 'N/A' }}</p>
            <p><strong>Time:</strong>
                {{ \Carbon\Carbon::parse($course->start_time)->format('g:i A') ?? 'N/A' }} -
                {{ \Carbon\Carbon::parse($course->end_time)->format('g:i A') ?? 'N/A' }}
            </p>
        </div>
    </div>


    <!-- Attendance Table -->
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
                    <td
                        class="status-{{ $student['absence_percentage'] < 10
                            ? 'present'
                            : ($student['absence_percentage'] < 20
                                ? 'warning'
                                : 'danger') }}">
                        {{ $student['status'] }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <div class="summary-box"
            style="margin-top: 10px; padding: 10px; background-color: #f3f7fa; border-left: 4px solid #1e4a6b;">
            Average Course Absence: <span class="summary-value" style="font-weight: bold">{{ $averageAbsence }}%</span>
        </div>
        Generated on {{ now()->format('Y-m-d H:i') }} — FYP team members
    </div>

</body>

</html>
