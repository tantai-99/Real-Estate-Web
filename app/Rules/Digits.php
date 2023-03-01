<?php
namespace App\Rules;

class Digits extends CustomRule
{
    const NOT_DIGITS   = 'notDigits';
    const STRING_EMPTY = 'digitsStringEmpty';
    const INVALID      = 'digitsInvalid';

    /**
     * Digits filter used for validation
     *
     * @var App\Rules\Digits
     */
    protected static $_filter = null;
    protected static $_unicodeEnabled;

    public function __construct()
    {
        if (null === self::$_unicodeEnabled) {
            self::$_unicodeEnabled = (@preg_match('/\pL/u', 'a')) ? true : false;
        }
    }

    /**
     * Defined by App\rules\Digits
     *
     * Returns the string $value, removing all but digit characters
     *
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {
        if (!self::$_unicodeEnabled) {
            $pattern = '/[^0-9]/';
        } else if (extension_loaded('mbstring')) {
            $pattern = '/[^[:digit:]]/';
        } else {
            $pattern = '/[\p{^N}]/';
        }

        return preg_replace($pattern, '', (string) $value);
    }
    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_DIGITS   => "'%value%' must contain only digits",
        self::STRING_EMPTY => "'%value%' is an empty string",
        self::INVALID      => "Invalid type given. String, integer or float expected",
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
        if (is_null($value)) {
            return true;
        }

        if (!is_string($value) && !is_int($value) && !is_float($value)) {
            $this->invokableRuleError($fail, self::INVALID);
            return false;
        }

        $this->_setValue((string) $value);

        if ('' === $this->_value) {
            $this->invokableRuleError($fail, self::STRING_EMPTY);
            return false;
        }

        // if (null === self::$_filter) {
        //     require_once 'Zend/Filter/Digits.php';
        //     self::$_filter = new Zend_Filter_Digits();
        // }

        if ($this->_value !== self::filter($this->_value)) {
            $this->invokableRuleError($fail, self::NOT_DIGITS);
            return false;
        }

        return true;
    }
}
