<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VerificationController extends Controller
{
    public function __construct(private OtpService $otp) {}

    public function showForm(): View
    {
        return view('auth.verify');
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $user = $request->user();

        // Already verified — skip to dashboard
        if ($user->is_verified) {
            return $this->redirectToDashboard($user);
        }

        if ($this->otp->verify($user, $request->otp)) {
            return $this->redirectToDashboard($user)
                ->with('success', 'Account verified! Welcome to Chariot 🎉');
        }

        return back()->withErrors(['otp' => 'Invalid or expired code. Please try again.']);
    }

    public function resend(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->is_verified) {
            return $this->redirectToDashboard($user);
        }

        if (! $user->email) {
            return back()->with('error', 'No email address on file. Please contact an admin to verify your account manually.');
        }

        $this->otp->send($user);

        return back()->with('info', 'A new verification code has been sent to your email.');
    }

    private function redirectToDashboard($user): RedirectResponse
    {
        return match($user->role) {
            'admin'  => redirect()->route('admin.dashboard'),
            'driver' => redirect()->route('driver.dashboard'),
            default  => redirect()->route('rider.home'),
        };
    }
}
