<?php

namespace App\Http\Requests;

use App\Enums\OrderStatus;
use App\Enums\Package;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderStoreRequest extends FormRequest
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
            'package' => ['required', Rule::enum(Package::class)],
            'amount' => 'required|integer',
            'period' => 'required|integer',
            'payment_screenshot' => 'required|image|mimes:jpg,jpeg,svg,png',
            'status' => ['nullable', Rule::enum(OrderStatus::class)]
        ];
    }
}
