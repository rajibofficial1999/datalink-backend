<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->user_id;

        return [
            'name' => 'required|string|max:100|min:3',
            'email' => [
                'required',
                'email',
                'max:200',
                'min:3',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'password' => ['nullable', 'confirmed', Password::min(8)->max(100)->letters()],
            'password_confirmation' => ['nullable'],
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:6000',
            'role' => 'nullable|numeric|exists:roles,id',
            'user_id' => 'required|numeric|exists:users,id',
        ];
    }
}
