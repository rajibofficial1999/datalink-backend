<?php
namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidSkypeUrl implements Rule
{
    public function passes($attribute, $value)
    {
        // Regex pattern for domain validation
        $pattern = '/^live:\.cid\.[a-f0-9]{16}$/';

        // Validate domain against regex pattern
        return preg_match($pattern, $value);
    }

    public function message()
    {
        return 'The :attribute must be a valid skype id.';
    }
}
