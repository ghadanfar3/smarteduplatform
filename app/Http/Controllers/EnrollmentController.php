<?php

namespace App\Http\Controllers;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnrollmentController extends Controller
{
    // اشتراك الطالب في دورة
    public function enroll($id)
    {
        $user = auth()->user() ;

        if ($user->role !== 'student') {
            return response()->json(['error' => 'فقط الطلاب يمكنهم الاشتراك في الدورات'], 403);
        }

        $course = Course::findOrFail($id);

        // تحقق إذا كان الطالب مشترك مسبقًا
        $alreadyEnrolled = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->exists();

        if ($alreadyEnrolled) {
            return response()->json(['message' => 'أنت مشترك بالفعل في هذه الدورة']);
        }

        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);

        return response()->json(['message' => 'تم الاشتراك بنجاح', 'enrollment' => $enrollment]);
    }

    // عرض الدورات التي اشترك بها الطالب
    public function myCourses()
    {
        $user = Auth::user();

        if ($user->role !== 'student') {
            return response()->json(['error' => 'فقط الطلاب يمكنهم عرض دوراتهم'], 403);
        }

        $enrollments = Enrollment::where('user_id', $user->id)
            ->with('course.teacher') // المدرّس
            ->get()
            ->map(function ($enrollment) {
                $course = $enrollment->course;

                return [
                    'course_id' => $course->id,
                    'title'     => $course->title,
                    'description' => $course->description,
                    'teacher'   => $course->teacher->name ?? null,
                    'progress'  => $enrollment->progress, // النسبة المئوية فقط
                ];
            });

        return response()->json($enrollments);
    }

}
