<?php
namespace App\Rules;

/**
 * 全角ひらがなのバリデーション
 *
 *
 */
class Hirakana extends CustomRule
{

    const INVALID = 'Invalid';

    /**
     *  @var array
     */
    protected $_messageTemplates = array(
        self::INVALID => '全角ひらがなではありません。'
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

        $regex = '/^[ぁ-んー]+$/u';

        if (preg_match($regex, $value, $matches)) {
            return true;
        } else {
            $this->invokableRuleError($fail, self::INVALID);
            return false;
        }
    }
}
?>