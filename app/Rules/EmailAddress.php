<?php
/**
 * メールアドレスのバリデーション
 * ※RFC準拠ではない。(携帯アドレスとかもとおるが、一部通らなくなっているモノもある)
 *
 */
namespace App\Rules;

class EmailAddress extends CustomRule
{

    const INVALID = 'Invalid';
    const INVALID_PARAMETER = 'InvalidParameter';

    const MSG = 'メールアドレスの形式に誤りがあります。';
    const MSG_PARAMETER = '文字列ではありません。';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::INVALID => self::MSG,
        self::INVALID_PARAMETER => self::MSG_PARAMETER,
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
        if (isEmpty($value)) {
    		return true;
        }
        
        if (!is_string($value)) {
            $this->invokableRuleError($fail,self::INVALID_PARAMETER);
            return false;
        }
        $valueString = (string)$value;

        $this->_setValue($valueString);

        $regex = '/^([*+!.&#$|\'\\%\/0-9a-z^_`{}=?~:-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,})$/i';
        if (preg_match($regex, $value, $matches)) {
            return true;
        } else {
            $this->invokableRuleError($fail,self::INVALID);
            return false;
        }
    }
}