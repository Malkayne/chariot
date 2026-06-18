<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showForm(): View
    {
        return view('auth.login');
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $request->validate([
            'phone'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Attempt login with phone as the identifier
        if (! Auth::attempt(['phone' => $request->phone, 'password' => $request->password], $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'phone' => 'The phone number or password is incorrect.',
            ]);
        }

        $user = Auth::user();

        // Check account is active
        if (! $user->is_active) {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'Your account has been deactivated. Please contact support.');
        }

        $request->session()->regenerate();

        // Redirect based on role
        return match($user->role) {
            'admin'  => redirect()->route('admin.dashboard'),
            'driver' => redirect()->route('driver.dashboard'),
            default  => redirect()->route('rider.home'),
        };
    }

    public function logout(Request $request): RedirectResponse
    {
        // Revoke all Sanctum tokens on logout
        $request->user()?->tokens()->delete();

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('success', 'You have been logged out successfully.');
    }
}
