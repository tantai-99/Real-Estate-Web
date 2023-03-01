<?php
/**
 * 全角ひらがな（英字有り）のバリデーション
 *
 *
 */
namespace App\Rules;

class NameHuriganaAndAlpha extends CustomRule
{

    const INVALID = 'Invalid';

    /**
     *  @var array
     */
    protected $_messageTemplates = array(
        self::INVALID => '全角ひらがな、または半角英字ではありません。'
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

        $regex = '/^[A-Za-zぁ-んー\ \　]+$/u';

        if (preg_match($regex, $value, $matches)) {
            return true;
        } else {
            $this->invokableRuleError($fail, self::INVALID);
            return false;
        }
    }
}
?>