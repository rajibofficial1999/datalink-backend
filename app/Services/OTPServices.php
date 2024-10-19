<?php

namespace App\Services;

use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class OTPServices
{
    public function verify(array $data): array
    {
        $otpCode = $this->getValidOTPCode($data);

        $user = $this->markOTPAsUsed($otpCode);

        return $this->generateResponse($user, $data['loginAfterVerified'] ?? false);
    }

    protected function getValidOTPCode(array $data): OtpCode
    {
        $otpCode = OtpCode::findOTPCodeByCode($data['otp_code']);

        if (!$otpCode || $otpCode->token !== $data['token']) {
            throw ValidationException::withMessages(['otp_code' => ['The OTP code is invalid.']]);
        }

        if ($otpCode->isUsed()) {
            throw ValidationException::withMessages(['otp_code' => ['The OTP code has already been used.']]);
        }

        if ($otpCode->isExpired()) {
            throw ValidationException::withMessages(['otp_code' => ['The OTP code has expired.']]);
        }

        return $otpCode;
    }


    protected function markOTPAsUsed(OtpCode $otpCode): User
    {
        $otpCode->update([
            'used_at' => now(),
            'is_valid' => false,
        ]);

        $user = $otpCode->user;
        $user->update(['email_verified_at' => now()]);

        return $user;
    }

    protected function generateResponse(User $user, bool $loginAfterVerified): array
    {
        $response = [
            'success' => true,
            'message' => 'OTP verified successfully.',
        ];

        if ($loginAfterVerified) {
            $token = $user->createToken(time())->plainTextToken;
            $response['token'] = $token;
            $response['user'] = $user;
        }

        return $response;
    }
}
