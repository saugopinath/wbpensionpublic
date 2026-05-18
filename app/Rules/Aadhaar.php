<?php

namespace App\Rules;
use Illuminate\Contracts\Validation\Rule;

class Aadhaar implements Rule
{
    // Verhoeff Multiplication Table
    protected $d = [
        [0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
        [1, 2, 3, 4, 0, 6, 7, 8, 9, 5],
        [2, 3, 4, 0, 1, 7, 8, 9, 5, 6],
        [3, 4, 0, 1, 2, 8, 9, 5, 6, 7],
        [4, 0, 1, 2, 3, 9, 5, 6, 7, 8],
        [5, 9, 8, 7, 6, 0, 4, 3, 2, 1],
        [6, 5, 9, 8, 7, 1, 0, 4, 3, 2],
        [7, 6, 5, 9, 8, 2, 1, 0, 4, 3],
        [8, 7, 6, 5, 9, 3, 2, 1, 0, 4],
        [9, 8, 7, 6, 5, 4, 3, 2, 1, 0],
    ];

    // Verhoeff Permutation Table
    protected $p = [
        [0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
        [1, 5, 7, 6, 2, 8, 3, 0, 9, 4],
        [5, 8, 0, 3, 7, 9, 6, 1, 4, 2],
        [8, 9, 1, 6, 0, 4, 3, 5, 2, 7],
        [9, 4, 5, 3, 1, 2, 6, 8, 7, 0],
        [4, 2, 8, 6, 5, 7, 3, 9, 0, 1],
        [2, 7, 9, 3, 8, 0, 6, 4, 5, 1],
        [7, 0, 4, 6, 9, 1, 3, 2, 5, 8],
    ];

    // Verhoeff Inverse Table
    protected $inv = [0, 4, 3, 2, 1, 5, 6, 7, 8, 9];

    public function passes($attribute, $value)
    {
        // 1. Remove any spaces and check structure (Must be exactly 12 digits starting with 2-9)
        $cleanValue = preg_replace('/\s+/', '', $value);
        if (!preg_match('/^[2-9]{1}[0-9]{11}$/', $cleanValue)) {
            return false;
        }

        // 2. Run the Verhoeff Algorithm
        $c = 0;
        $len = strlen($cleanValue);

        for ($i = 0; $i < $len; $i++) {
            $digit = (int) $cleanValue[$len - 1 - $i];
            $c = $this->d[$c][$this->p[($i % 8)][$digit]];
        }

        return $c === 0;
    }

    public function message()
    {
        return 'The :attribute is not a valid Aadhaar card number.';
    }
}
