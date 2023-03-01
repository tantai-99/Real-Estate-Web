<?php
namespace App\Rules;

class Between extends CustomRule
{
    const NOT_BETWEEN        = 'notBetween';
    const NOT_BETWEEN_STRICT = 'notBetweenStrict';

    protected $_messageTemplates = array(
        self::NOT_BETWEEN        => "'%value%' is not between '%min%' and '%max%', inclusively",
        self::NOT_BETWEEN_STRICT => "'%value%' is not strictly between '%min%' and '%max%'"
    );

    protected $_messageVariables = array(
        'min' => '_min',
        'max' => '_max'
    );

    protected $_min;
    protected $_max;

    protected $_inclusive;

    public function __construct($options)
    {
        if (!is_array($options)) {
            $options = func_get_args();
            $temp['min'] = array_shift($options);
            if (!empty($options)) {
                $temp['max'] = array_shift($options);
            }

            if (!empty($options)) {
                $temp['inclusive'] = array_shift($options);
            }

            $options = $temp;
        }

        if (!array_key_exists('min', $options) || !array_key_exists('max', $options)) {
            throw new \Exception("Missing option. 'min' and 'max' has to be given");
        }

        if (!array_key_exists('inclusive', $options)) {
            $options['inclusive'] = true;
        }

        $this->setMin($options['min'])
             ->setMax($options['max'])
             ->setInclusive($options['inclusive']);
    }

    /**
     * Returns the min option
     *
     * @return mixed
     */
    public function getMin()
    {
        return $this->_min;
    }

    /**
     * Sets the min option
     *
     * @param  mixed $min
     * @return Zend_Validate_Between Provides a fluent interface
     */
    public function setMin($min)
    {
        $this->_min = $min;
        return $this;
    }

    /**
     * Returns the max option
     *
     * @return mixed
     */
    public function getMax()
    {
        return $this->_max;
    }

    /**
     * Sets the max option
     *
     * @param  mixed $max
     * @return Zend_Validate_Between Provides a fluent interface
     */
    public function setMax($max)
    {
        $this->_max = $max;
        return $this;
    }

    /**
     * Returns the inclusive option
     *
     * @return boolean
     */
    public function getInclusive()
    {
        return $this->_inclusive;
    }

    /**
     * Sets the inclusive option
     *
     * @param  boolean $inclusive
     * @return Zend_Validate_Between Provides a fluent interface
     */
    public function setInclusive($inclusive)
    {
        $this->_inclusive = $inclusive;
        return $this;
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
        $this->_setValue($value);

        if ($this->_inclusive) {
            if ($this->_min > $value || $value > $this->_max) {
                $this->invokableRuleError($fail, self::NOT_BETWEEN);
                return false;
            }
        } else {
            if ($this->_min >= $value || $value >= $this->_max) {
                $this->invokableRuleError($fail, self::NOT_BETWEEN_STRICT);
                return false;
            }
        }
        return true;
    }

}
