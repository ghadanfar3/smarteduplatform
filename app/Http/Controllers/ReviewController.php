<?php

namespace App\Http\Controllers;
use App\Models\Course;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    // إرسال تقييم جديد
    public function store(Request $request, $courseId)
    {
        $user = auth()->User();

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        // تحقق إذا الطالب اشترك في الدورة
        $enrolled = $user->enrollments()->where('course_id', $courseId)->exists();
        if (!$enrolled) {
            return response()->json(['error' => 'يجب أن تكون مشتركًا في الدورة لتقييمها'], 403);
        }

        // تحقق إذا قيّم مسبقًا
        $existing = Review::where('user_id', $user->id)->where('course_id', $courseId)->first();
        if ($existing) {
            return response()->json(['message' => 'لقد قمت بتقييم هذه الدورة مسبقًا'], 409);
        }

        $review = Review::create([
            'user_id' => $user->id,
            'course_id' => $courseId,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json(['message' => 'تم إرسال التقييم', 'review' => $review]);
    }

    // عرض كل التقييمات لدورة معينة
    public function index($courseId)
    {
        $reviews = Review::with('user')->where('course_id', $courseId)->latest()->get();
        return response()->json($reviews);
    }


}
