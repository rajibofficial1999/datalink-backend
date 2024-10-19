<?php

namespace App\Http\Requests;

use App\Rules\ValidDomain;
use App\Rules\ValidSkypeUrl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DomainUpdateRequest extends FormRequest
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
        $domainId = $this->domain_id;

        $roles = [
            'domain' => [
                'required',
                'max:255',
                Rule::unique('domains', 'name')->ignore($domainId),
                new ValidDomain
            ],
            'privacy' => 'required|boolean',
            'domain_id' => 'required|exists:domains,id',
        ];


        if (!request()->user()->isSuperAdmin) {
            $roles['skype_url'] = ['required', 'max:255', new ValidSkypeUrl];
            $roles['screenshot'] = 'required|image|mimes:jpeg,png,jpg|max:2048';
            $roles['amount'] = 'nullable|number';
            $roles['privacy'] = 'nullable|boolean';
        }

        return $roles;
    }

    protected function prepareForValidation(): void
    {
        $name = $this->input('domain');
        if (!Str::startsWith($this->input('domain'), 'www.')) {
            $name = "www." . $this->input('domain');
        }

        $this->merge([
            'domain' => $name,
        ]);
    }
}
