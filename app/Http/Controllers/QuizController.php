<?php

namespace App\Http\Controllers;
use App\Models\Course;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\QuizResult;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class QuizController extends Controller
{

    // عرض أسئلة الاختبار لدورة معينة
    public function show($courseId)
    {
        $quiz = Quiz::where('course_id', $courseId)->with('questions')->first();

        if (!$quiz) {
            return response()->json(['error' => 'لا يوجد اختبار لهذه الدورة'], 404);
        }

        return response()->json($quiz);
    }

    // إرسال الإجابات وتسجيل النتيجة
    public function submit(Request $request, $courseId)
    {
        $user = new User() ;

        $quiz = Quiz::where('course_id', $courseId)->with('questions')->firstOrFail();

        $answers = $request->input('answers'); // [question_id => answer]

        $score = 0;
        $total = $quiz->questions->count();

        foreach ($quiz->questions as $question) {
            if (isset($answers[$question->id]) && $answers[$question->id] == $question->correct_answer) {
                $score++;
            }
        }

        $result = QuizResult::create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'score' => $score,
            'total' => $total,
        ]);

        return response()->json([
            'message' => 'تم إرسال الإجابات',
            'score' => $score,
            'total' => $total,
            'result' => $result
        ]);
    }


}
