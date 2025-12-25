<?php

namespace App\Http\Controllers;

use App\Models\Option;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuizController extends Controller
{
    // إنشاء اختبار جديد مرتبط بكورس محدد
    public function store(Request $request, $courseId)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'questions'   => 'required|array|size:5', // لازم 5 أسئلة بالضبط
            'questions.*.text' => 'required|string',
            'questions.*.options' => 'required|array|size:3', // لازم 3 خيارات بالضبط لكل سؤال
            'questions.*.options.*.text' => 'required|string',
            'questions.*.options.*.is_correct' => 'required|boolean',
        ]);

        $quiz = Quiz::create([
            'course_id'  => $courseId ?? null, // أخذناه من الـ route
            'title'      => $validated['title'],
        ]);

        foreach ($validated['questions'] as $q) {
            $question = $quiz->questions()->create([
                'text' => $q['text'],
            ]);

            foreach ($q['options'] as $opt) {
                $question->options()->create([
                    'text'       => $opt['text'],
                    'is_correct' => $opt['is_correct'],
                ]);
            }
        }

        return response()->json([
            'message' => 'تم إنشاء الاختبار لهذا الكورس بنجاح ✅',
            'quiz'    => $quiz->load('questions.options')
        ], 201);
    }

    // عرض الاختبار مع الأسئلة والخيارات حسب الكورس
    public function show($courseId)
    {
        $quiz = Quiz::where('course_id', $courseId)
            ->with('questions.options')
            ->first();

        if (!$quiz) {
            return response()->json(['error' => 'لا يوجد اختبار لهذه الدورة'], 404);
        }

        return response()->json($quiz);
    }

    // إرسال إجابات الطالب وتسجيل النتيجة
    public function submit(Request $request, $courseId)
    {
        $user = Auth::user();

        $quiz = Quiz::where('course_id', $courseId)
            ->with('questions.options')
            ->firstOrFail();

        $answers = $request->input('answers');
        // شكلها: [question_id => option_id]

        $score = 0;
        $total = $quiz->questions->count();

        foreach ($quiz->questions as $question) {
            if (isset($answers[$question->id])) {
                $chosenOptionId = $answers[$question->id];
                $option = $question->options->where('id', $chosenOptionId)->first();

                if ($option && $option->is_correct) {
                    $score++;
                }
            }
        }

        $result = QuizResult::create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'score'   => $score,
            'total'   => $total,
        ]);

        return response()->json([
            'message' => 'تم إرسال الإجابات',
            'score'   => $score,
            'total'   => $total,
            'result'  => $result
        ]);
    }
    public function update(Request $request, $quizId)
    {
        $quiz = Quiz::with('questions.options')->findOrFail($quizId);

        // تحديث اسم الاختبار
        if ($request->filled('title')) {
            $quiz->update(['title' => $request->title]);
        }

        // تعديل الأسئلة والخيارات الموجودة فقط
        if ($request->has('questions')) {
            foreach ($request->questions as $q) {
                $question = Question::findOrFail($q['id']); // لا ننشئ جديد
                $question->update(['text' => $q['text']]);

                foreach ($q['options'] as $opt) {
                    $option = Option::findOrFail($opt['id']); // لا ننشئ جديد
                    $option->update([
                        'text' => $opt['text'],
                        'is_correct' => (bool)$opt['is_correct'],
                    ]);
                }
            }
        }

        return response()->json([
            'message' => 'تم تعديل الاختبار بنجاح',
            'quiz' => $quiz->fresh('questions.options')
        ]);
    }



    public function destroy($id)
    {
        $quiz = Quiz::findOrFail($id);
        $quiz->delete(); // يحذف الاختبار + أي علاقات لو حطيت Cascade في الموديل

        return response()->json([
            'message' => 'تم حذف الاختبار بنجاح'
        ]);
    }

}
