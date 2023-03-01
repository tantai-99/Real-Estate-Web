<?php
namespace App\Rules;

class EmbeeSearchER extends CustomRule
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

        if ($valueString === '') {
            return true;
        }

        $this->_setValue($valueString);

        $value=strtolower($value);

        $regexDiv       = "/<div\s[^>]*class=(.*)([^\"|' >]*?)\\1[^>]*>(.*)<\/div>/i";
        $regexScript    = "/<script\s[^>]*src=(.*)([^\"|' >]*?)\\1[^>]*>(.*)<\/script>/i";
        $clearTag=trim(strip_tags($value));
        if (preg_match($regexDiv, $value, $matches)) {
            if(preg_match($regexScript, $value, $matches)){
                if(!$clearTag)
                {
                    return true;
                }
            }
        } 
        $this->invokableRuleError($fail, self::INVALID);
        return false;
        
    }
}