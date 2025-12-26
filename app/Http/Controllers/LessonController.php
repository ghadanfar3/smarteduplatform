<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LessonController extends Controller
{
    // عرض كل الدروس لدورة معينة
    public function index($courseId)
    {
        $lessons = Lesson::where('course_id', $courseId)->get();



        $completedLessonIds = DB::table('lesson_completions')
            ->where('user_id', auth()->id())
            ->pluck('lesson_id')
            ->toArray();

        $lessons->each(function ($lesson) use ($completedLessonIds) {
            $lesson->is_completed = in_array($lesson->id, $completedLessonIds);
        });

        return response()->json($lessons);
    }

    // عرض درس واحد
    public function show($id)
    {
        $lesson = Lesson::findOrFail($id);
        $lesson->video_url = asset('storage/' . $lesson->videoPath);
        $lesson->is_completed = DB::table('lesson_completions')
            ->where('user_id', auth()->id())
            ->where('lesson_id', $lesson->id)
            ->exists();
        return response()->json($lesson);
    }

    // إنشاء درس جديد (للمعلم فقط) + رفع فيديو
    public function store(Request $request, $courseId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'video' => 'required|file|mimes:mp4,mov,avi,webm|max:102400', // 100MB
        ]);

        $course = Course::findOrFail($courseId);

        // تحقق أن المعلم هو صاحب الدورة
        if ($course->teacher_id !== Auth::id()) {
            return response()->json(['error' => 'غير مصرح لك بإضافة درس لهذه الدورة'], 403);
        }

        // رفع الفيديو وتخزينه
        $videoPath = $request->file('video')->store('videos', 'public');

        // إنشاء الدرس
        $lesson = Lesson::create([
            'title' => $request->title,
            'course_id' => $courseId,
            'videoPath' => $videoPath, // مسار الفيديو في الـ storage
        ]);

        return response()->json($lesson, 201);
    }

    // تعديل درس
    public function update(Request $request, $id)
    {
        $lesson = Lesson::findOrFail($id);
        $lesson->update($request->only(['title', 'description']));
        return response()->json($lesson);
    }

    // حذف درس + حذف الفيديو
    public function destroy($id)
    {
        $lesson = Lesson::findOrFail($id);

        // حذف الفيديو من storage إذا موجود
        if ($lesson->videoPath && Storage::disk('public')->exists($lesson->videoPath)) {
            Storage::disk('public')->delete($lesson->videoPath);
        }

        $lesson->delete();

        return response()->json(['message' => 'تم حذف الدرس والفيديو المرتبط به']);
    }
}
