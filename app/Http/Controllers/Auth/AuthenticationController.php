<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthenticationController extends Controller
{
    public function register(Request $request)
    {
        $rules = [
            "name" => "required|min:2|max:255",
            "email" => "required|email|unique:users",
            "password" => "required|min:6|max:255",
            "user_type" => "required|in:solo_handyman,company_admin",
        ];

        // Add company validation for company admins
        if ($request->user_type === 'company_admin') {
            $rules['admin_company_name'] = 'required|string|max:255';
            $rules['admin_company_address'] = 'required|string|max:500';
            $rules['admin_company_phone'] = 'required|string|max:20';
            $rules['admin_company_email'] = 'nullable|email|max:255';
        }

        $validated = $request->validate($rules);

        try {
            DB::beginTransaction();

            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'user_type' => $validated['user_type'],
            ];

            $company = null;

            // Handle company creation for admins
            if ($validated['user_type'] === 'company_admin') {
                // Create company for admin
                $company = Company::create([
                    'name' => $validated['admin_company_name'],
                    'address' => $validated['admin_company_address'],
                    'phone' => $validated['admin_company_phone'],
                    'email' => $validated['admin_company_email'] ?? $validated['email'],
                    'admin_id' => null, // Will be set after user creation
                ]);

                $userData['company_id'] = $company->id;
            }

            $user = User::create($userData);

            // Set admin relationship for company admin
            if ($validated['user_type'] === 'company_admin' && $company) {
                $company->update(['admin_id' => $user->id]);
            }

            DB::commit();

            $token = $user->createToken("auth_token");

            return response([
                'user' => $user->load('company'),
                'token' => $token->plainTextToken,
                'message' => 'Registration successful',
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'message' => 'Registration failed',
                'error' => $e->getMessage(),
            ], 500);
        }
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

    public function showRegister(Request $request)
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
            $user = Auth::user();

            // Check if user is a company employee
            if ($user->isCompanyEmployee()) {
                Auth::logout();
                return back()->withErrors([
                    'employee_restriction' => 'Company employees cannot access the web dashboard. Please use the mobile application to access your company features.',
                ])->withInput();
            }

            $request->session()->regenerate();
            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'email' => 'Invalid credentials',
        ]);
    }

    public function webRegister(Request $request)
    {
        $rules = [
            'name' => 'required|min:2|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|max:255',
            'password_confirmation' => 'required|same:password',
            'user_type' => 'required|in:solo_handyman,company_admin',
        ];

        // Add company validation for company admins
        if ($request->user_type === 'company_admin') {
            $rules['admin_company_name'] = 'required|string|max:255';
            $rules['admin_company_address'] = 'required|string|max:500';
            $rules['admin_company_phone'] = 'required|string|max:20';
            $rules['admin_company_email'] = 'nullable|email|max:255';
        }

        $validated = $request->validate($rules);

        try {
            DB::beginTransaction();

            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'user_type' => $validated['user_type'],
            ];

            $company = null;

            // Handle company creation for admins
            if ($validated['user_type'] === 'company_admin') {
                // Create company for admin
                $company = Company::create([
                    'name' => $validated['admin_company_name'],
                    'address' => $validated['admin_company_address'],
                    'phone' => $validated['admin_company_phone'],
                    'email' => $validated['admin_company_email'] ?? $validated['email'],
                    'admin_id' => null, // Will be set after user creation
                ]);

                $userData['company_id'] = $company->id;
            }

            $user = User::create($userData);

            // Set admin relationship for company admin
            if ($validated['user_type'] === 'company_admin' && $company) {
                $company->update(['admin_id' => $user->id]);
            }

            DB::commit();

            Auth::login($user);

            return redirect()->route('dashboard')->with('success', 'Registration successful! Welcome to Handi.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors([
                'registration' => 'Registration failed. Please try again.',
            ])->withInput();
        }
    }




}
