<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use App\Models\Lesson;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    public function completeLesson(Request $request, Lesson $lesson)
    {
        $studentId = $request->user()->id;
        $course = $lesson->course;

        // تحقق من وجود انتساب
        $enrollment = Enrollment::where('course_id', $course->id)
            ->where('user_id', $studentId)
            ->firstOrFail();

        // سجّل اكتمال الدرس (idempotent بسبب unique)
        DB::table('lesson_completions')->updateOrInsert(
            ['lesson_id' => $lesson->id, 'user_id' => $studentId],
            ['completed_at' => now()]
        );

        // احسب النسبة
        // احسب عدد الدروس
        $total = $course->lessons()->count();
        $completed = DB::table('lesson_completions')
            ->where('user_id',$studentId)
            ->whereIn('lesson_id',$course->lessons()->pluck('id'))
            ->count();

        $rate = $total ? (int) round(($completed/$total)*100) : 0;

        // حدّث النسبة داخل الانتساب
        $enrollment->update(['progress' => $rate]);

        return response()->json([
            'progress'=>$rate // نسبة مئوية 0–100
        ]);
    }


}
