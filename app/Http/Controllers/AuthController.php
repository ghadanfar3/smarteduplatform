<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // عرض كل المستخدمين للـ Admin فقط
    public function index()
    {
        $user = Auth::user();
        $users = User::all(['id', 'name', 'email', 'role']); // حدد الأعمدة اللي بدك تعرضها
        return response()->json($users);
    }

    // تسجيل مستخدم جديد
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|string',
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password); // مهم جداً
        $user->role = $request->role;
        $user->save();

        return response()->json($user, 200);
    }

    // تسجيل الدخول
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return response()->json([
            "user" => $user,
            "token" => $user->createToken($request->email)->plainTextToken
        ], 200);
    }

    // تحديث بيانات المستخدم
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $data = $request->only(['email', 'name']);
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json($user);
    }
    public function destroy($id)
    {
        $user = user::findOrFail($id);
        $user->delete();
        return response()->json(['message' => 'تم حذف المستخدم بنجاح']);
    }

    //logout
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'تم تسجيل الخروج']);
    }
}
