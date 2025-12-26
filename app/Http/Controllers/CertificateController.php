<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\QuizResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CertificateController extends Controller
{
    /**
     * عرض كل شهادات المستخدم
     */
    public function index()
    {
        $user = Auth::user();

        $certificates = Certificate::with('course')
            ->where('user_id', $user->id)
            ->get()
            ->map(function ($cert) {
                return [
                    'id' => $cert->id,
                    'course_id' => $cert->course_id,
                    'course_title' => $cert->course->title ?? null,
                    'certificate_code' => $cert->certificate_code,
                    'issued_at' => $cert->issued_at,
                ];
            });

        return response()->json($certificates);
    }

    /**
     * إنشاء شهادة (يُستدعى بعد الاختبار)
     */
    public function create(Request $request, $courseId)
    {
        $user = Auth::user();

        // 1️⃣ تحقق من التقدم = 100%
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->first();

        if (!$enrollment || $enrollment->progress < 100) {
            return response()->json([
                'message' => 'يجب إكمال جميع الدروس أولاً'
            ], 403);
        }

        // 2️⃣ تحقق من نتيجة الاختبار
        $quiz = Quiz::where('course_id', $courseId)->first();

        if (!$quiz) {
            return response()->json([
                'message' => 'لا يوجد اختبار لهذا الكورس'
            ], 404);
        }

        $result = QuizResult::where('quiz_id', $quiz->id)
            ->where('user_id', $user->id)
            ->latest()
            ->first();

        if (!$result || !$result->passed) {
            return response()->json([
                'message' => 'لم تنجح في الاختبار ❌'
            ], 403);
        }

        // 3️⃣ منع تكرار الشهادة
        $existing = Certificate::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'الشهادة موجودة مسبقًا',
                'certificate' => $existing
            ]);
        }

        // 4️⃣ إنشاء الشهادة
        $certificate = Certificate::create([
            'user_id' => $user->id,
            'course_id' => $courseId,
            'certificate_code' => 'CERT-' . strtoupper(uniqid()),
            'issued_at' => now(),
        ]);

        return response()->json([
            'message' => 'مبروك 🎉 تم إصدار الشهادة',
            'certificate' => $certificate
        ], 201);
    }

    /**
     * عرض شهادة واحدة
     */
    public function show($id)
    {
        $certificate = Certificate::with('course')
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return response()->json([
            'id' => $certificate->id,
            'course' => $certificate->course->title ?? null,
            'certificate_code' => $certificate->certificate_code,
            'issued_at' => $certificate->issued_at,
        ]);
    }

    /**
     * حذف شهادة (اختياري – للإدارة فقط)
     */
    public function destroy($id)
    {
        $certificate = Certificate::findOrFail($id);
        $certificate->delete();

        return response()->json([
            'message' => 'تم حذف الشهادة'
        ]);
    }
}
