<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login (Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
        return response()->json(["user"=> $user, "token" =>$user->createToken($request->email)->plainTextToken ], 200) ;
    }

    public function register(Request $request){
        $user = new User() ;
        $user->name = $request->name ;
        $user->email = $request->email ;
        $user->password = $request->password ;
        $user->role = $request->role ;
        $user->save();
        return response()->json($user, 200) ;
    }
}
