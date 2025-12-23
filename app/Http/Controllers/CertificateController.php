<?php

namespace App\Http\Controllers;
use App\Models\Certificate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Event\RuntimeException;

class CertificateController extends Controller
{
    // عرض الشهادات الخاصة بالمستخدم
    public function index()
    {
        $user = Auth::user() ;
        $certificates = Certificate::with('course')
            ->where('user_id', $user->id)
            ->get();

        return response()->json($certificates);
    }

    // تحميل شهادة معينة (اختياري)
    public function getCertificateById($id)
    {
        $certificate = Certificate::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();
        if(!$certificate) throw new RuntimeException("certificate is not fond with id: " . $id);
        // لاحقًا ممكن ترجع ملف PDF أو رابط تحميل
        return response()->json(['item' => $certificate]);
    }

    //add api to add certificate

    public function createCertificate(){

    }

    public function deleteCertificate(){

    }

}
