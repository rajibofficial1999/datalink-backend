<?php

namespace App\Jobs\Auth;

use App\Mail\Auth\OtpVerificationMail;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OtpCodeJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(protected User $user, protected string $verifyToken, protected ?string $email = null)
    {

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $otp = strtoupper(Str::random(8));
        $userId = $this->user->id;

        OtpCode::updateOrCreate(
            ['user_id' => $userId],
            [
                'code' => $otp,
                'token' => $this->verifyToken,
                'used_at' => null,
            ]
        );

        $to = $this->email ?? $this->user;

        Mail::to($to)->send(new OtpVerificationMail($otp, $this->user->name));
    }
}
