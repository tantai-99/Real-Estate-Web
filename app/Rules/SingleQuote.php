<?php
namespace App\Rules;

class SingleQuote extends CustomRule
{
    const INVALID = 'invalid';
	const INVALIDMESSAGE = "「'（シングルクォーテーション）」は入力できません。";

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = array(
        self::INVALID => self::INVALIDMESSAGE
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
        if (preg_match('/\'/', $value)) {
            $this->invokableRuleError($fail, self::INVALID);
            return false;
        }

        return true;
    }
}
