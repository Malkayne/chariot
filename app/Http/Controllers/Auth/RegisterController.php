<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function __construct(private OtpService $otp) {}

    public function showForm(): View
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = User::create([
            'name'           => $request->name,
            'phone'          => $request->phone,
            'email'          => $request->email,
            'password'       => $request->password, // cast handles hashing
            'role'           => $request->role,
            'rccg_member_id' => $request->rccg_member_id,
        ]);

        // Create driver profile if registering as driver
        if ($request->role === 'driver') {
            $user->driverProfile()->create([
                'vehicle_type'  => $request->vehicle_type,
                'vehicle_model' => $request->vehicle_model,
                'vehicle_color' => $request->vehicle_color,
                'plate_number'  => $request->plate_number,
                'total_seats'   => $request->total_seats,
            ]);
        }

        // Send OTP for email verification (if email provided)
        $this->otp->send($user);

        Auth::login($user);

        return redirect()->route('verify')
            ->with('info', 'A 6-digit verification code has been sent to your email.');
    }
}
