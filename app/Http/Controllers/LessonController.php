<?php

namespace App\Http\Controllers;
use App\Models\Course;
use Illuminate\Http\Request;
use App\Models\Lesson;
use Illuminate\Support\Facades\Auth;
class LessonController extends Controller
{
    // عرض كل الدروس لدورة معينة
    public function index($courseId)
    {
        $lessons = Lesson::where('course_id', $courseId)->get();
        return response()->json($lessons);
    }

    // عرض درس واحد
    public function show($id)
    {
        $lesson = Lesson::findOrFail($id);
        return response()->json($lesson);
    }

    // إنشاء درس جديد (للمعلم فقط)
    public function store(Request $request, $courseId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'content'=>'required|string',
        ]);

        $course = Course::findOrFail($courseId);

        // تحقق أن المعلم هو صاحب الدورة
        if ($course->teacher_id !== Auth::id()) {
            return response()->json(['error' => 'غير مصرح لك بإضافة درس لهذه الدورة'], 403);
        }

        $lesson = Lesson::create([
            'title' => $request->title,
            'description' => $request->description,
            'course_id' => $courseId,
            'content'=>'required|string',
        ]);

        return response()->json($lesson, 201);
    }

    // تعديل درس
    public function update(Request $request, $id)
    {
        $lesson = Lesson::findOrFail($id);
        $lesson->update($request->only(['title', 'content']));
        return response()->json($lesson);
    }

    // حذف درس
    public function destroy($id)
    {
        $lesson = Lesson::findOrFail($id);
        $lesson->delete();
        return response()->json(['message' => 'تم حذف الدرس']);
    }

}
