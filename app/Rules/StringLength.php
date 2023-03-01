<?php

namespace App\Rules;

class StringLength extends CustomRule
{
    const INVALID   = 'stringLengthInvalid';
    const TOO_SHORT = 'stringLengthTooShort';
    const TOO_LONG  = 'stringLengthTooLong';

    protected $_messageTemplates = array(
        self::INVALID   => "Invalid type given. String expected",
        self::TOO_SHORT => "'%value%' is less than %min% characters long",
        self::TOO_LONG  => "'%value%' is more than %max% characters long",
    );

    protected $_messageVariables = array(
        'min' => '_min',
        'max' => '_max'
    );

    protected $_min = 0;
    protected $_max = null;

    protected $message;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($options = array())
    {
        foreach($options as $name=>$value) {
            if(isset($this->_messageVariables[$name])) {
                $this->{$this->_messageVariables[$name]} = $value;
            }
        }
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
        if (!is_null($value) && !is_string($value)) {
            $this->invokableRuleError($fail, self::INVALID);
            return false;
        }
        $this->_setValue($value);
        $length = mb_strlen( str_replace("\r\n", "\n", $value) );
        if ($length < $this->_min) {
            $this->invokableRuleError($fail, self::TOO_SHORT);
            return false;
        }
        if (null !== $this->_max && $this->_max < $length) {
            $this->invokableRuleError($fail, self::TOO_LONG);
            return false;
        }

        return true;
    }

    public function setMin($min) {
        $this->_min = $min;
    }

    public function setMax($max) {
        $this->_max = $max;
    }
}
