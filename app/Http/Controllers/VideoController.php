<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class VideoController extends Controller
{
    public function store(Request $request)
    {
        // 1. تحقق
        $request->validate([
            'video' => 'required|file|mimes:mp4,mov,avi,webm|max:51200',
        ]);

        // 2. خزّن
        $path = $request->file('video')->store('videos', 'public');

        // 3. رجّع النتيجة
        return response()->json([
            'message' => 'تم رفع الفيديو بنجاح 🎉',
            'path' => $path,
            'url' => asset('storage/' . $path),
        ]);
    }
}

