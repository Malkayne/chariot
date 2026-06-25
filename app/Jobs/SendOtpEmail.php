<?php

namespace App\Jobs;

use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendOtpEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 30;

    public function __construct(
        public readonly User $user,
        public readonly string $otp
    ) {}

    public function handle(): void
    {
        $email = $this->user->email;

        if (! $email) {
            // No email address — skip silently for MVP
            return;
        }

        try {
            Mail::to($email)->send(new OtpMail($this->user, $this->otp));
        } catch (\Exception $e) {
            logger()->error("Failed to send verification OTP email to {$email}: " . $e->getMessage());
        }
    }
}
