<?php

namespace App\Http\Controllers;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class CourseController extends Controller
{
    public function index(){
        $courses = Course::with('teacher')->where('is_active', true)
            ->withCount('enrolloments')->withAvg('ratings', 'value')->get();
        return response()->json($courses);

    }

    // عرض دورة واحدة
    public function show($id)
    {
        $course = Course::with(['teacher', 'lessons', 'reviews'])->findOrFail($id);
        $reviews = Review::where("course_id", $course->id)->get();
        $evaluation = 0;
        foreach ($reviews as $review){
            $evaluation += $review->rating;
        }
        $count = count($reviews) <= 0 ? 1 : count($reviews);
        $evaluation = $evaluation/$count;
        $numOfStud = Enrollment::where("course_id", $course->id)->count();
        return response()->json(["course"=>$course,
            "evaluation"=>$evaluation,
            "numberOfStudent"=> $numOfStud
        ]);
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

    public function uploadImage(Request $request, Course $course)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // لو في صورة قديمة نحذفها
        if ($course->image) {
            Storage::disk('public')->delete($course->image);
        }

        // اسم مرتب
        $filename = 'course_'.$course->id.'_'.time().'.'.$request->image->extension();

        // تخزين
        $path = $request->image->storeAs(
            'courses/images',
            $filename,
            'public'
        );
        // حفظ بالـ DB
        $course->update([
            'image' => $path,
        ]);

        return response()->json([
            'message' => 'تم رفع صورة الكورس بنجاح 🖼️',
            'path' => $path,
            'url' => asset('storage/' . $path),
        ]);
    }

    // حذف دورة
    public function destroy($id)
    {
        $course = Course::findOrFail($id);
        $course->delete();
        return response()->json(['message' => 'تم حذف الدورة بنجاح']);
    }
}
