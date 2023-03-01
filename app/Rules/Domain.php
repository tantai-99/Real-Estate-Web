<?php
/**
 * ドメインのバリデーション
 *
 *
 */
namespace App\Rules;

class Domain extends CustomRule
{

    const INVALID = 'Invalid';

    /**
     *  @var array
     */
    protected $_messageTemplates = array(
        self::INVALID => 'ドメイン形式ではありません。'
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

        if (empty($valueString)) {
            return true;
        }

        $this->_setValue($valueString);

        $regex = '/^([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i';
        if (preg_match($regex, $value, $matches)) {
            return true;
        } else {
            $this->invokableRuleError($fail, self::INVALID);
            return false;
        }
    }
}
?>