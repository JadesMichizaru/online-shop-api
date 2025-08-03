<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    public function __invoke(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|max:255',
            'roles' => 'required|string|in:customer,admin',
            'password' => 'required|string|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Cek duplikasi user berdasarkan email
        $existingUser = User::where('email', $request->email)->first();
        if ($existingUser) {
            return response()->json([
                'status' => false,
                'message' => 'User with this email already exists',
            ], 409);
        }

        // Creating the User
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'roles' => $request->roles,
            'password' => bcrypt($request->password)
        ]);

        return response()->json([
            'status' => true,
            'message' => 'User created successfully',
            'user' => $user
        ], 201);
    }
}
