<?php
namespace App\Rules;

class StringLengthCKEditor extends StringLength
{
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
        if (!is_string($value)) {
            $this->invokableRuleError($fail, self::INVALID);
            return false;
        }

        $this->_setValue($value);
        
        $length = mb_strlen( str_replace("&nbsp;", " ", strip_tags($value)) );

        if ($length < $this->_min) {
            $this->invokableRuleError($fail, self::TOO_SHORT);
        }

        if (null !== $this->_max && $this->_max < $length) {
            $this->invokableRuleError($fail, self::TOO_LONG);
        }

        if ($this->message && count($this->message)) {
            return false;
        } else {
            return true;
        }
    }
}
