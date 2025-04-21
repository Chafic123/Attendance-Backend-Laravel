<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('student.{id}', function ($user, $id) {
    if ($user->status === 'Student') {
        return (int) $user->student->id === (int) $id;
    }

    if ($user->status === 'Instructor') {
        $student = \App\Models\Student::find($id);

        if ($student) {
            $instructorCourses = $user->instructor->courses;

            foreach ($instructorCourses as $course) {
                if ($student->courses->contains($course)) {
                    return true; 
                }
            }
        }

        return false;
    }

    return false;
});