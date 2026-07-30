<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    /**
     * Show admin login page.
     */
    public function login()
    {
        // If already logged in as admin, redirect to dashboard
        if (Auth::check() && Auth::user()->type == 1) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    /**
     * Authenticate admin — creates session + Sanctum token.
     */
    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if ($user->type != 1) {
                Auth::logout();
                return response()->json(['message' => 'Access denied. Admin privileges required.'], 403);
            }

            $request->session()->regenerate();
            $token = $user->createToken('admin-token')->plainTextToken;

            return response()->json([
                'access_token' => $token,
                'user' => $user,
            ]);
        }

        return response()->json(['message' => 'Invalid email or password.'], 401);
    }

    /**
     * Logout admin — destroys session + tokens.
     */
    public function logout(Request $request)
    {
        if (Auth::check()) {
            Auth::user()->tokens()->delete();
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    // ─── View methods (protected by auth + admin middleware) ───

    public function dashboard()
    {
        return view('admin.dashboard');
    }

    public function teams()
    {
        return view('admin.teams');
    }

    public function services()
    {
        return view('admin.services');
    }

    public function projects()
    {
        return view('admin.projects');
    }

    public function about()
    {
        return view('admin.about');
    }

    public function faq()
    {
        return view('admin.faq');
    }

    public function reviews()
    {
        return view('admin.reviews');
    }

    public function videos()
    {
        return view('admin.videos');
    }

    public function contacts()
    {
        return view('admin.contacts');
    }
}
