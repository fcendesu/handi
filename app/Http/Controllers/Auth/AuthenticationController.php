<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthenticationController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            "name" => "required|min:4|max:255",
            "email" => "required|email|unique:users",
            "password" => "required|min:4|max:255",
        ]);

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ];

        $user = User::create($userData);
        $token = $user->createToken("auth_token");

        return response(
            [
                'name' => $user,
                'token' => $token->plainTextToken,
            ],
            201,
        );
    }

    public function login(Request $request)
    {
        $request->validate([
            "email" => "required|email",
            "password" => "required|min:4|max:255",
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response([
                'message' => 'Invalid credentials',
            ], 422);
        }

        $token = $user->createToken("auth_token");

        return response([
            'user' => $user,
            'token' => $token->plainTextToken,
        ], 200);
    }

    public function logout(Request $request)
    {
        // Handle API logout
        if ($request->expectsJson()) {
            $request->user()->tokens()->delete();
            return response([
                'message' => 'Logged out successfully',
            ], 200);
        }

        // Handle web logout
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function validateToken(Request $request)
    {
        return response()->json([
            'message' => 'Token is valid',
            'user' => $request->user(),
        ], 200);
    }

    //auth for web
    //
    //
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function webLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:4'
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'email' => 'Invalid credentials',
        ]);
    }

    public function webRegister(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|min:4|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:4|max:255',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);
        return redirect()->route('dashboard');
    }




}
