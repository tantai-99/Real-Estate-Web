<?php
/**
 * 環境依存文字の排除
 *
 *
 */
namespace App\Rules;
class NoKishuizon extends CustomRule
{

    const INVALID = 'Invalid';

    const MSG = '環境依存文字は登録できません。環境依存文字を削除して、登録し直してください。';

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
		if(strlen($value) === 0) {
			return true;
		}

        // 4byte文字が含まれる場合はエラーとする
        // (参考) https://www.softel.co.jp/blogs/tech/archives/5269
        if(preg_match('/[\xF0-\xF7][\x80-\xBF][\x80-\xBF][\x80-\xBF]/', $value)) {
            $this->invokableRuleError($fail, self::INVALID);
            return false;
		} else {
			return true;
		} 
    }
}
