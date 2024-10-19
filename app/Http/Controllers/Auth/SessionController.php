<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Jobs\Auth\OtpCodeJob;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SessionController extends Controller
{
    public function create(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if(!$this->attemptLogin($credentials)){
            return $this->unAuthorizedResponse('Provided credentials are not valid.');
        }

        $authUser = Auth::user();

        $statusResponse = $this->checkUserStatus($authUser);
        if ($statusResponse) {
            return $statusResponse;
        }

        // Check if email verification or two-step authentication is required
        if (!$authUser->isVerified() || $authUser->isTowStepAuthOn()) {
            return $this->handleVerification($authUser);
        }

        $authUser = Auth::user();

        $user = UserResource::make($authUser);
        $token = $user->createToken(time())->plainTextToken;

        return $this->respondWithData(
            success: true,
            user: $user,
            token: $token
        );

    }

    public function destroy(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json(['success' => true], Response::HTTP_OK);
    }

    protected function handleVerification(User $authUser): JsonResponse
    {
        $emailVerifiedToken = Str::uuid()->toString();

        OtpCodeJob::dispatch($authUser, $emailVerifiedToken);

        $message = !$authUser->isVerified()
            ? 'The email is not verified. A verification code has been sent to your email.'
            : 'A verification code has been sent to your email.';

        Auth::logout();

        return $this->respondWithData(
            success: false,
            message: $message,
            token: $emailVerifiedToken
        );
    }

    protected function attemptLogin(array $credentials): bool
    {
        return Auth::attempt($credentials, true);
    }

    protected function checkUserStatus(User $authUser): ?JsonResponse
    {
        if ($authUser->status === UserStatus::PENDING) {
            return $this->unAuthorizedResponse('The account is under review.');
        }

        if ($authUser->status === UserStatus::SUSPENDED) {
            return $this->unAuthorizedResponse('The account has been suspended.');
        }

        if ($authUser->status === UserStatus::REJECTED) {
            return $this->unAuthorizedResponse('The account has been rejected.');
        }

        return null; // No issues with user status
    }

    protected function unAuthorizedResponse(string $message): JsonResponse
    {
        return response()->json([
            'errors' => [
                'error' => $message
            ]
        ], Response::HTTP_UNAUTHORIZED);
    }

    protected function respondWithData(bool $success, string $message = null, ?UserResource $user = null, string $token = null): JsonResponse
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'user' => $user,
            'token' => $token
        ], Response::HTTP_OK);
    }
}
