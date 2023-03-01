<?php
namespace App\Rules;

use Validator;

class InArray extends CustomRule
{
    const INVALID = 'notInArray';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::INVALID => "value was not found in the haystack",
    );

    /**
     * Sets validator options
     *
     * @param  array $haystack
     * @return void
     */
    public function __construct($options = [])
    {
        $this->array = $this->convertArray($options);
    }

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
        $values = $this->convertArray($value);
        foreach($values as $value) {
            if (!in_array($value, $this->array)) {
                $this->invokableRuleError($fail, self::INVALID);
                return false;
            }
        }
        return true;
    }

    public function convertArray($array) {
        if (is_array($array)) {
            return array_map(function($value) {
                return (string) $value;
            }, $array);
        }
        return [(string) $array];
    }
}
