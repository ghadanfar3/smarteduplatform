<?php

namespace App\Http\Controllers;
use App\Models\Quiz;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function store(Request $request, Quiz $quiz) {
        $data = $request->validate([
            'text'=>'required|string',
            'options'=>'required|array|min:3|max:3',
            'options.*.text'=>'required|string',
            'options.*.is_correct'=>'boolean'
        ]);

        $question = $quiz->questions()->create(['text'=>$data['text']]);

        foreach ($data['options'] as $opt) {
            $question->options()->create([
                'text'=>$opt['text'],
                'is_correct'=>$opt['is_correct'] ?? false
            ]);
        }

        return response()->json($question->load('options'),201);
    }

}
