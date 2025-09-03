<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        return response()->json([
            'message' => 'Register route works!',
            'data' => $request->all()
        ]);
    }

    public function login(Request $request)
    {
        return response()->json([
            'message' => 'Login route works!',
            'data' => $request->all()
        ]);
    }
}
