<?php
/**
 * 日付のバリデーション
 *
 *
 */
namespace App\Rules;

class Date extends CustomRule
{

    const INVALID = 'Invalid';

    /**
     *  @var array
     */
    protected $_messageTemplates = array(
        self::INVALID => '日付形式で入力してください。'
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
        
        if (isEmpty($value)) {
        	return true;
        }

        if (false !== strtotime(str_replace(array('年', '月', '日'), array('-', '-', ''), $value))) {
            return true;
        } else {
            $this->invokableRuleError($fail, self::INVALID);
            return false;
        }
    }
}