<?php
/**
 * 全角ひらがなのバリデーション
 *
 *
 */
namespace App\Rules;

class HudousanyougoYomi extends CustomRule
{

    const INVALID = 'Invalid';
    const INVALID_FIRAT = 'Invalid_First';

    /**
     *  @var array
     */
    protected $_messageTemplates = array(
        self::INVALID => '全角ひらがな、「・」で入力してください。',
    	self::INVALID_FIRAT => '一文字目はひらがなで入力してください。',
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
        
        if (preg_match('/^[・ー]/u', $value)) {
        	$this->invokableRuleError($fail, self::INVALID_FIRAT);
        	return false;
        }

        $regex = '/^[ぁ-ん・ー]+$/u';

        if (preg_match($regex, $value, $matches)) {
            return true;
        } else {
            $this->invokableRuleError($fail, self::INVALID);
            return false;
        }
    }
}
?>