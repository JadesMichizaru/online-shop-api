<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Exceptions\JWTException;

class LogoutController extends Controller
{
    public function __construct() {
        $this->middleware('auth:api');
    }

    public function __invoke(Request $request)
    {
        try {
            // Cek apakah token tersedia
            $token = JWTAuth::getToken();
            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token not provided'
                ], 400);
            }

            // Hapus token agar tidak bisa digunakan lagi
            JWTAuth::invalidate($token);

            return response()->json([
                'success' => true,
                'message' => 'Logout Successfully'
            ], 200);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to logout, please try again'
            ], 500);
        }
    }
}

