<?php

namespace App\Rules;

class Regex extends CustomRule
{
    const INVALID = 'Invalid';

    /**
     *  @var array
     */
    protected $_messageTemplates = array();

    protected $pattern;
    protected $messages;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($options = array())
    {
        foreach($options as $name=>$value) {
            $this->{$name} = $value;
        }

        $this->_messageTemplates = array(
            self::INVALID => $this->messages
        );
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
        
        $valueString = (string) $value;

        $this->_setValue($valueString);

        if (empty($valueString) || preg_match($this->pattern, $valueString, $matches)) {
            return true;
        } else {
            $this->invokableRuleError($fail, self::INVALID);
            return false;
        }
    }
}
