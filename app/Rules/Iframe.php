<?php
namespace App\Rules; 

class Iframe extends CustomRule
{

    const INVALID = 'Invalid';

    const MSG = '検索エンジンレンタルのHTMLタグを入力してください。';

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

        $value=strtolower($value);

        $regex = "/<iframe\s[^>]*src=(.*)([^\"|' >]*?)\\1[^>]*>(.*)<\/iframe>/i";

        if (preg_match($regex, $value, $matches)) {
            return true;
        } else {
            $this->invokableRuleError($fail, self::INVALID);
            return false;
        }
    }
}