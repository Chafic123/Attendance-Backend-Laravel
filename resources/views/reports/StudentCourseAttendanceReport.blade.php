<!DOCTYPE html>
<html>

<head>
    <title>Student Attendance Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            line-height: 1.6;
            color: #333;
            padding: 30px;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 0px;
        }

        .logo-container img {
            width: 120px;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .header h2 {
            margin: 0;
            font-size: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .info-section {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
        }

        .info-block {
            flex: 1;
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 6px;
        }

        .info-block h4 {
            margin-top: 0;
            font-size: 18px;
            color: #444;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        .info-block p {
            margin: 4px 0;
        }

        ul.absent-list {
            list-style: square;
            padding-left: 20px;
        }

        .no-data {
            font-style: italic;
            color: #888;
        }

        table.meta-table {
            width: 100%;
            margin-top: 0;
            border-collapse: collapse;
        }

        table.meta-table th,
        table.meta-table td {
            padding: 8px 10px;
            border: 1px solid #000;
            text-align: left;
        }

        @page {
            size: A4 landscape;
            margin: 0;
        }
    </style>
</head>

<body>

    <div class="header">
        <div class="logo-container">
            <img src="{{ public_path('docs/images/RHU-Logo.jpg') }}" alt="RHU Logo" style="width: 150px;">
        </div>
        <h2>Student Course Attendance Report</h2>
        <p><strong>Course:</strong> {{ $course->name }} ({{ $course->Code }})</p>
        <p><strong>Instructor:</strong> {{ optional($instructor->user)->first_name }}
            {{ optional($instructor->user)->last_name }}</p>
    </div>

    <div class="info-section">
        <div class="info-block">
            <h4>Student Information</h4>
            <p><strong>Name:</strong> {{ optional($student->user)->first_name }}
                {{ optional($student->user)->last_name }}
            </p>
            <p><strong>University ID:</strong> {{ $student->student_id }}</p>
            <p><strong>Email:</strong> {{ optional($student->user)->email }}</p>
            <p><strong>Department:</strong> {{ optional($student->department)->name ?? 'N/A' }}</p>
            <p><strong>Major:</strong> {{ $student->major }}</p>
        </div>

        <div class="info-block">
            <h4>Attendance Summary</h4>
            <table class="meta-table">
                <tr>
                    <th>Total Absences</th>
                    <td>{{ $absentCount }}</td>
                </tr>
                <tr>
                    <th>Absence Percentage</th>
                    <td>{{ $absencePercentage }}%</td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>{{ $status }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="info-block">
        <h4>Absent Days</h4>
        @if ($attendanceRecords->isEmpty())
            <p class="no-data">No absences recorded for this course.</p>
        @else
            <ul class="absent-list">
                @foreach ($attendanceRecords as $record)
                    <li>{{ \Carbon\Carbon::parse($record->course_session->session_date)->format('l, F j, Y') }}</li>
                @endforeach
            </ul>
        @endif
    </div>

</body>

</html>
