<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(){
        return response()->json(['course'=>Course::all()]);
    }

    public function create(){
    }
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
}
