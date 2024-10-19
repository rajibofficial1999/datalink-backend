<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UserStoreRequest extends FormRequest
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
        return [
            'name' => 'required|string|max:100|min:3',
            'email' => 'required|email|max:200|min:3|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)->max(100)->letters()],
            'password_confirmation' => ['required'],
            'profile_photo' => 'required|image|mimes:jpeg,png,jpg,svg|max:6000',
            'role' => 'nullable|numeric|exists:roles,id',
        ];
    }
}
