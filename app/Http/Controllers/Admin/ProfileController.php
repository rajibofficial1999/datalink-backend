<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Jobs\Auth\OtpCodeJob;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function update(Request $request ): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'min:3'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($request->user())],
        ]);

        $request->user()->update($data);

        return response()->json([
            'success' => 'Profile has updated successfully.',
            'user' => UserResource::make($request->user()),
        ]);
    }

    public function changeProfilePicture(Request $request): JsonResponse
    {
        $data = $request->validate([
            'profile_photo' => 'required|image|mimes:jpeg,png,jpg,svg|max:6000',
            'user_id' => 'required|numeric|exists:users,id',
        ]);

        $user = User::find($data['user_id']);

        if ($request->hasFile('profile_photo')) {
            if (Storage::disk('public')->exists($user->avatar ?? '')) {
                Storage::disk('public')->delete($user->avatar ?? '');
            }

            $user->avatar = $request->file('profile_photo')->store('users', 'public');
        }

        $user->update();

        return response()->json([
            'success' => 'Profile picture has updated successfully.',
            'user' => UserResource::make($user),
        ]);
    }


    public function handleTwoSteps(Request $request): JsonResponse
    {
        $data = $request->validate([
            'two_step_auth' => 'required|boolean',
        ]);

        $request->user()->update($data);

        return response()->json([
            'success' => true,
            'message' => 'User has updated successfully.',
            'user' => $request->user()
        ]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'confirmed', Password::min(8)->max(100)->letters()],
            'password_confirmation' => ['required'],
        ]);

        $user = $request->user();

        // Check if the old password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The provided password does not match your current password.'],
            ]);
        }

        // Update the password
        $user->password = $request->password;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully.',
        ]);
    }
}
