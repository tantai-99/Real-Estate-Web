<?php

namespace App\Rules;

class Confirm extends CustomRule
{

    const INVALID = 'Invalid';

    /**
     * @var string
     */
    protected $_confirm_key;

    /**
     * @var string
     */
    protected $_label;

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::INVALID => '%label%が一致しません。'
    );
    protected $_messageVariables = array(
    	'label' => '_label',
    );
    
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($options = array())
    {
        if (!is_array($options) || func_num_args() > 1) {
            $options = array();
            $options['label'] = func_get_arg(0);

            if (func_num_args() > 1) {
                $options['confirmKey'] = func_get_arg(1);
            }
        }
        if (isset($options['label'])) {
            $this->setLabel($options['label']);
        }
        if (isset($options['confirmKey'])) {
            $this->setConfirmKey($options['confirmKey']);
        }
    }

    /**
     * @parma void
     * @return string|null
     */
    public function getLabel()
    {
        return $this->_label;
    }

    /**
     * @param string confirm_key
     * @throw Exception
     */
    public function setLabel($label)
    {
        if (!is_string($label)) {
            throw new \Exception('label must be string');
        }
        $this->_label = $label;
        return $this;
    }

    /**
     * @parma void
     * @return string|null
     */
    public function getConfirmKey()
    {
        return $this->_confirm_key;
    }

    /**
     * @param string confirm_key
     * @throw Exception
     */
    public function setConfirmKey($confirm_key)
    {
        if (!is_string($confirm_key)) {
            throw new \Exception('confirm_key must be string');
        }
        $this->_confirm_key = $confirm_key;
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
        $valueString = (string)$value;

        $this->_setValue($valueString);
        $context = app('request')->all();
        if (isset($context['memberonly'])) {
            $context = array_merge($context, $context['memberonly']);
        }

        if (!isset($context[$this->_confirm_key]) || $value !== $context[$this->_confirm_key]) {
            $this->_messageTemplates = str_replace('%label%', $this->_label, $this->_messageTemplates);
            $this->invokableRuleError($fail, self::INVALID);
            return false;
        }
        return true;
    }
}