<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class UserController extends Controller
{
    public function login(Request $request) {
        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:255",
            "password" => "required|string",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "message" => "Validation error",
                "errors" => $validator->errors(),
            ], 400);
        }

        $credentials = $request->only(["name", "password"]);

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json([
                "message" => "Forbidden",
            ], 403);
        }

        return response()->json([
            "token" => $token,
        ]);
    }

    // Update user berdasarkan id dan token
    public function update(Request $request, $id) {
        // Autentikasi user dari token
        $authUser = JWTAuth::parseToken()->authenticate();

        // Pastikan user yang sedang login hanya bisa update dirinya sendiri
        if ($authUser->id != $id) {
            return response()->json([
                'message' => 'Unauthorized access',
            ], 403);
        }

        // Cari user berdasarkan id
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255|unique:users,email,' . $user->id,
            'phone_number' => 'sometimes|nullable|string|max:20',
            'address' => 'sometimes|nullable|string|max:255',
            'password' => 'sometimes|nullable|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 400);
        }

        // Update data user
        if ($request->has('name')) {
            $user->name = $request->name;
        }
        if ($request->has('email')) {
            $user->email = $request->email;
        }
        if ($request->has('phone_number')) {
            $user->phone_number = $request->phone_number;
        }
        if ($request->has('address')) {
            $user->address = $request->address;
        }
        if ($request->has('password') && $request->password) {
            $user->password = bcrypt($request->password);
        }

        $user->save();

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user
        ]);
    }
}
