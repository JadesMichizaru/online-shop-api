<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public function __invoke(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

         if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Ambil credentials berdasarkan input yang diberikan
        $credentials = $request->only('password');

        if ($request->has('email')) {
            $credentials['email'] = $request->email;
        } else {
            $credentials['username'] = $request->username;
        }

         // Jika autentikasi gagal
         if (!$token = auth()->guard('api')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Email/Username atau Password salah'
            ], 401);
        }

        // Jika berhasil, kembalikan token
        return response()->json([
            'success' => true,
            'user' => auth()->guard('api')->user(),
            'token' => $token
        ], 200);
    }
}
