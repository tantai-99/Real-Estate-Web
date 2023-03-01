<?php
namespace App\Rules;

class NotInArray extends CustomRule
{

    const INVALID = 'Invalid';

    protected $_values = array();
    
    public function setValues($values) {
    	$this->_values = $values;
    }
    
    /**
     *  @var array
     */
    protected $_messageTemplates = array(
        self::INVALID => '同じ値は設定できません。'
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
        if (!in_array($valueString, $this->_values)) {
            return true;
        } else {
            $this->invokableRuleError($fail, self::INVALID);
            return false;
        }
    }
}
?>