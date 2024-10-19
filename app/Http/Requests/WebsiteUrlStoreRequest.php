<?php

namespace App\Http\Requests;

use App\Enums\Category;
use App\Enums\Sites;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WebsiteUrlStoreRequest extends FormRequest
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
            'domain' => ['required', 'numeric', 'exists:domains,id'],
            'user' => ['required', 'numeric', 'exists:users,id'],
            'sites' => ['required', 'array'],
            'sites.*' => ['required', Rule::enum(Sites::class)],
            'categories' => ['required', 'array'],
            'categories.*' => ['required', Rule::enum(Category::class)],
        ];
    }
}
