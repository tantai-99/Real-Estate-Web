<?php

namespace App\Rules;

class Hankaku extends CustomRule
{
    const INVALID = 'invalid';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = array(
        self::INVALID => "半角で入力してください。"
    );

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
        $this->_setValue($value);

        if (strlen($value) !== mb_strlen($value)) {
            $this->invokableRuleError($fail, self::INVALID);
            return false;
        }

        return true;
    }
}
