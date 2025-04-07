<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Course Attendance Report - {{ $course->name }}</title>
    <style>
        body {
            font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
            font-size: 14px;
            margin: 0;
            padding: 30px;
            color: #2d3748;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        .report-header {
            border-bottom: 2px solid #2b6cb0;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        h2 {
            color: #2c5282;
            margin: 0 0 8px 0;
            font-size: 26px;
            font-weight: 600;
        }

        .subtitle {
            color: #4a5568;
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 20px;
        }

        .course-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-direction: column; 
            gap: 20px;
            margin-bottom: 30px;
        }

        .info-item {
            margin-bottom: 5px;
        }

        .info-label {
            font-weight: 600;
            color: #4299e1;
            display: block;
            margin-bottom: 3px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-size: 15px;
            color: #1a202c;
            font-weight: 500;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin: 30px 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        th {
            background-color: #2b6cb0;
            color: white;
            padding: 14px 20px;
            text-align: center;
            font-size: 15px;
            font-weight: 300;
            letter-spacing: 0.5px;
        }

        td {
            padding: 12px 20px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
            vertical-align: middle;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:nth-child(even) {
            background-color: #f8fafc;
        }

        tr:hover {
            background-color: #ebf8ff;
        }

        .status-present {
            color: #38a169;
            font-weight: 600;
        }

        .status-warning {
            color: #dd6b20;
            font-weight: 600;
        }

        .status-danger {
            color: #e53e3e;
            font-weight: 600;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            text-align: right;
        }

        .summary-box {
            display: inline-block;
            background-color: #ebf8ff;
            padding: 14px 24px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            color: #2b6cb0;
        }

        .summary-value {
            font-size: 18px;
            font-weight: 700;
        }

        .logo {
            text-align: right;
            margin-bottom: 20px;
        }

        .logo img {
            height: 60px;
        }

        .instructor-list {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        .instructor-list li {
            margin-bottom: 5px;
        }

        @page {
            size: A4 landscape;
            margin: 0;
        }
    </style>
</head>
<body>

<div class="logo">
{{-- logo --}}
</div>

<div class="report-header">
    <h2>Attendance Report</h2>
    <div class="subtitle">{{ $course->name }} ({{ $course->Code }})</div>
</div>

<div class="course-info">
    <div class="info-item">
        <span class="info-label">Instructor</span>
        <span class="info-value">
            {{ optional($instructor->user)->first_name }} {{ optional($instructor->user)->last_name }}
        </span>
    </div>
    
    <div class="info-item">
        <span class="info-label">Section</span>
        <span class="info-value">{{ $course->Section ?? 'N/A' }}</span>
    </div>
    
    <div class="info-item">
        <span class="info-label">Start Time</span>
        <span class="info-value">{{ \Carbon\Carbon::parse($course->start_time)->format('g:i A') ?? 'N/A' }}</span>
    </div>
    
    <div class="info-item">
        <span class="info-label">End Time</span>
        <span class="info-value">{{ \Carbon\Carbon::parse($course->end_time)->format('g:i A') ?? 'N/A' }}</span>
    </div>
    
    <div class="info-item">
        <span class="info-label">Day of Week</span>
        <span class="info-value">{{ ucfirst($course->day_of_week) ?? 'N/A' }}</span>
    </div>
    
</div>

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
</div>

</body>
</html>