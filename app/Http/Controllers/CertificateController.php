<?php

namespace App\Http\Controllers;
use App\Models\Certificate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CertificateController extends Controller
{
    // عرض الشهادات الخاصة بالمستخدم
    public function index()
    {
        $user = new User() ;

        $certificates = Certificate::with('course')
            ->where('user_id', $user->id)
            ->get();

        return response()->json($certificates);
    }

    // تحميل شهادة معينة (اختياري)
    public function download($id)
    {
        $certificate = Certificate::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // لاحقًا ممكن ترجع ملف PDF أو رابط تحميل
        return response()->json(['message' => 'تحميل الشهادة غير مفعل بعد']);
    }


}
