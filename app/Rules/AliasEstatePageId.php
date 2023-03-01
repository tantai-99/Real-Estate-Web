<?php

namespace App\Rules;

class AliasEstatePageId extends CustomRule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function __invoke($attribute, $value, $fail)
    {
        if (!preg_match('/^estate_/', (string)$value)) {
            $fail('ページIDが無効です。');
			return false;
		}
		
		return true;
    }
}
