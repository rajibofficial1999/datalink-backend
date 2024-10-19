<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Jobs\Auth\OtpCodeJob;
use App\Models\User;
use App\Services\OTPServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class OtpCodeController extends Controller
{
    public function __construct(protected OTPServices $otpServices){}

    public function verifyOtpCode(Request $request): JsonResponse
    {

        $validatedData = $request->validate([
            'otp_code' => 'required|string|max:100',
            'token' => 'required|string',
            'loginAfterVerified' => 'nullable|boolean',
        ]);

        $data = $this->otpServices->verify($validatedData);

        return response()->json($data, Response::HTTP_OK);

    }

    public function sendOTPCode(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id' => 'required|numeric|exists:users,id',
            'email' => 'nullable|email',
        ]);

        $user = User::find($data['user_id']);

        $email = $data['email'];

        $verifyToken = Str::uuid()->toString();

        OtpCodeJob::dispatch($user, $verifyToken, $email);

        return response()->json([
            'success' => true,
            'verifyToken' => $verifyToken,
        ], Response::HTTP_OK);
    }
}
