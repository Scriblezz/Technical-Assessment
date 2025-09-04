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
        // Ensure JSON body is merged
        if (empty($request->all())) {
            $raw = $request->getContent();
            if ($raw) {
                $decoded = json_decode($raw, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $request->merge($decoded);
                }
            }
        }

        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Lookup user and verify password
        $user = User::where('email', $request->email)->first();
        if ($user && Hash::check($request->password, $user->password)) {
            try {
                $token = JWTAuth::fromUser($user);
                return response()->json(['message' => 'Login successful', 'token' => $token]);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Token generation failed', 'message' => $e->getMessage()], 500);
            }
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
