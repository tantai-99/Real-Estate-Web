<?php
/**
 * 座標のバリデーション
 *
 *
 */
namespace App\Rules;

class LatLng extends CustomRule
{

    const INVALID = 'Invalid';

    /**
     *  @var array
     */
    protected $_messageTemplates = array(
        self::INVALID => '緯度・経度を正しく入力してください'
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
        
		if(preg_match("/^[\-+]{0,1}\d{1,}$/", $valueString) || preg_match("/^[\-+]{0,1}\d{1,}\.\d{1,}$/", $valueString)) {
            return true;
        } else {
            $this->invokableRuleError($fail, self::INVALID);
            return false;
        }
    }
}