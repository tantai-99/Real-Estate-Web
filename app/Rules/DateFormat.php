<?php
/**
 * URLのバリデーション
 *
 *
 */
namespace App\Rules;
use Validator;

class DateFormat extends Regex
{

    const INVALID = 'Invalid';

    /**
     *  @var array
     */
    protected $_messageTemplates = array();

    protected $format;

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
        $valueString = (string) $value;

        $this->_setValue($valueString);
        if (strpos($valueString, '/') !== false) {
            $this->format = str_replace("-", "/", $this->format);
        } else {
            $this->format = str_replace("/", "-", $this->format);
        }

        $rules = [
            $attribute => 'date_format:'.$this->format
        ];
        $validator = Validator::make([$attribute => $value], $rules);
        if (count($validator->errors()->getMessages())) {
            $this->invokableRuleError($fail, self::INVALID);
            return false;
        }
        return true;
    }
}
?>