<?php

namespace App\Services;

use App\Jobs\SendOtpEmail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class OtpService
{
    /**
     * Generate a fresh 6-digit OTP, hash and store it on the user row,
     * then dispatch the email job.
     */
    public function send(User $user): void
    {
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->update([
            'otp_code'       => Hash::make($otp),
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        SendOtpEmail::dispatchSync($user, $otp);
    }

    /**
     * Verify the supplied plain-text OTP against the stored hash.
     * Marks the user as verified and clears OTP fields on success.
     */
    public function verify(User $user, string $otp): bool
    {
        if (! $user->otp_expires_at || $user->otp_expires_at->isPast()) {
            return false;
        }

        if (! $user->otp_code || ! Hash::check($otp, $user->otp_code)) {
            return false;
        }

        $user->update([
            'is_verified'    => true,
            'otp_code'       => null,
            'otp_expires_at' => null,
        ]);

        return true;
    }

    /**
     * Check whether the user's OTP window is still open.
     */
    public function isExpired(User $user): bool
    {
        return ! $user->otp_expires_at || $user->otp_expires_at->isPast();
    }
}
