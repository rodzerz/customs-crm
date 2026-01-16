<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CurrencyFormatRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Must be a valid currency format: number with up to 2 decimal places
        if (!is_numeric($value) || !preg_match('/^\d+(\.\d{1,2})?$/', (string)$value)) {
            $fail("The $attribute must be a valid currency format (e.g., 1234.56)");
        }

        // Must be positive
        if ($value <= 0) {
            $fail("The $attribute must be a positive number");
        }
    }
}
