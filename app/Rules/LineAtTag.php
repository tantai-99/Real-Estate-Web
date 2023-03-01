<?php

namespace App\Rules;

class LineAtTag extends CustomRule
{
    const INVALID = 'invalid';
    const NOT_LINE_AT_CODE = 'not_line_at_code';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = array(
        self::INVALID           => "値が不正です",
        self::NOT_LINE_AT_CODE  => "※LINE公式アカウント以外のコードは埋め込めません。",
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
        if (empty($value)) {
            return true;
        }
        //'line'が含まれていない場合
        if (
            strpos($value, 'line') === false ||
            substr($value, 0, 1) !== '<' || substr($value, -1) !== '>'
        ) {
            $this->invokableRuleError($fail, self::NOT_LINE_AT_CODE);
            return false;
        }
        return true;
    }
}
