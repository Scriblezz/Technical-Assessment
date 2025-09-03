<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    // Register a new user
    public function register(Request $request)
    {
        // If client sent JSON body, merge it so Lumen validation sees it
        $json = $request->json()->all();
        if (is_array($json) && count($json)) {
            $request->merge($json);
        }

        // Validate input
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        // Create user in database
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        // Return success
        return response()->json(['message' => 'User created successfully', 'user' => $user], 201);
    }

    public function login(Request $request)
{
    // Merge JSON body so validation works when client POSTs JSON
    $json = $request->json()->all();
    if (is_array($json) && count($json)) {
        $request->merge($json);
    }

    $this->validate($request, [
        'email' => 'required|email',
        'password' => 'required|string',
    ]);

    try {
        // Try database-backed auth first
        $user = User::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            $token = JWTAuth::fromUser($user);
            return response()->json(['message' => 'Login successful', 'token' => $token]);
        }
    } catch (\Exception $e) {
        // likely no PDO driver or DB misconfigured — fall back to file-backed users
        // continue to file-based check below
    }

    // File-backed fallback (development only)
    $usersPath = dirname(__DIR__, 3) . '/storage/app/users.json';
    if (file_exists($usersPath)) {
        $json = file_get_contents($usersPath);
        $data = json_decode($json, true) ?: [];
        foreach ($data as $u) {
            if (isset($u['email']) && $u['email'] === $request->email && isset($u['password']) && Hash::check($request->password, $u['password'])) {
                // create a simple JWT-like token (not real JWT) for frontend usage
                $token = base64_encode($u['email'] . ':' . time());
                return response()->json(['message' => 'Login successful (file fallback)', 'token' => $token]);
            }
        }
    }

    return response()->json(['error' => 'Invalid credentials'], 401);
}
}
