<?php

namespace App\Http\Controllers;
use App\Models\Course;
use Illuminate\Http\Request;


class CourseController extends Controller
{
    public function index(){
        $courses = Course::with('teacher')->where('is_active', true)->get();
        return response()->json($courses);
    }

    // عرض دورة واحدة
    public function show($id)
    {
        $course = Course::with(['teacher', 'lessons', 'reviews'])->findOrFail($id);
        return response()->json($course);
    }
    //create course just teacher
    public function store(Request $request)
    {
        // التحقق من البيانات المرسلة
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'imgpath' => 'nullable|string',
        ]);
        // التحقق أن المستخدم الحالي هو أستاذ

            // إنشاء الدرس وربطه بالأستاذ الحالي
            $course = auth()->user()->courses()->create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'imgpath' => $validated['imgpath'] ?? null,
            ]);

            return response()->json([
                'message' => 'تم إنشاء الدرس بنجاح',
                'course' => $course
            ], 201);
    }

    //edit course
    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);
        $course->update($request->only(['title', 'description']));
        return response()->json($course);
    }

    // حذف دورة
    public function destroy($id)
    {
        $course = Course::findOrFail($id);
        $course->delete();
        return response()->json(['message' => 'تم حذف الدورة بنجاح']);
    }
}
