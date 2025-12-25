<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{
    // عرض كل الدورات
    public function index()
    {
        $courses = Course::with('teacher')
            ->where('is_active', true)
            ->withCount('enrollments')
            ->withAvg('reviews', 'rating')
            ->get();

        // أضف رابط الصورة لكل دورة
        $courses->transform(function ($course) {
                $course->image_url = $course->imgPath
                ? asset('storage/' . $course->imgPath)
                : null;
            return $course;
        });

        return response()->json($courses);
    }

    // عرض دورة واحدة
    public function show($id)
    {
        $course = Course::with(['teacher', 'lessons', 'reviews'])->findOrFail($id);
        $evaluation = Review::where("course_id", $course->id)->avg('rating') ?? 0;
        $numOfStud = Enrollment::where("course_id", $course->id)->count();

        // رابط الصورة
        if ($course->imgpath) {
            $course->image_url = asset('storage/' . $course->imgpath);
        } else {
            $course->image_url = null;
        }

        return response()->json([
            "course" => $course,
            "reviews_avg_rating" => $evaluation,
            "enrollments_count" => $numOfStud
        ]);
    }

    // إنشاء دورة جديدة + رفع صورة
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // اسم مرتب
        $filename = 'course_' . time() . '.' . $request->image->extension();

        // تخزين الصورة
        $path = $request->image->storeAs('courses/images', $filename, 'public');

        // إنشاء الدورة وربطها بالأستاذ الحالي
        $course = auth()->user()->courses()->create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'imgpath' => $path,
        ]);

        // رابط الصورة
        $course->image_url = asset('storage/' . $path);

        return response()->json([
            'message' => 'تم إنشاء الدورة بنجاح 🖼️',
            'course' => $course
        ], 201);
    }

    // تعديل دورة (بدون الصورة)
    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);
        $course->update($request->only(['title', 'description']));
        return response()->json($course);
    }

    // حذف دورة + حذف الصورة
    public function destroy($id)
    {
        $course = Course::findOrFail($id);

        // حذف الصورة من storage إذا موجودة
        if ($course->imgpath && Storage::disk('public')->exists($course->imgpath)) {
            Storage::disk('public')->delete($course->imgpath);
        }

        $course->delete();

        return response()->json(['message' => 'تم حذف الدورة والصورة المرتبطة بها 🗑️']);
    }
}
