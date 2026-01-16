<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class HSCodeRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // HS code must be exactly 10 digits
        if (!preg_match('/^\d{10}$/', $value)) {
            $fail("The $attribute must be a valid HS code (10 digits)");
        }
    }
}
