<?php
/**
 * URLのバリデーション
 *
 *
 */
namespace App\Rules;

class Tel extends CustomRule
{

    const INVALID = 'Invalid';

    const MSG = '電話番号は半角数字、「-」で入力してください。';

    /**
     *  @var array
     */
    protected $_messageTemplates = array(
        self::INVALID => self::MSG,
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
        $valueString = (string) $value;

        $this->_setValue($valueString);

        $regex = '/^[0-9\-]*$/';

        if (preg_match($regex, $value, $matches)) {
            return true;
        } else {
            $this->invokableRuleError($fail, self::INVALID);
            return false;
        }
    }
}