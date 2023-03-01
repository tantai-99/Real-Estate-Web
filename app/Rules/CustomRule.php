<?php

namespace App\Rules;

use Lang;
use Illuminate\Contracts\Validation\InvokableRule;

class CustomRule implements InvokableRule
{
    protected $_messageVariables = [];

    protected $_value;

    protected $message;

    /**
     * Indicates whether the rule should be implicit.
     *
     * @var bool
     */
    public $implicit = true;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
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
        //
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        if (!is_null($this->message) && !empty($this->message)) {
            return $this->replaceMessage($this->_messageTemplates[$this->message]);
        }
        return $this->_messageTemplates;
    }

    public function _setValue($value) {
        $this->_value = $value;
    }

    public function replaceMessage($message) {
        $langMessages = Lang::get('message');
        if (!isset($langMessages[$message])) {
            return $message;
        }

        $message = $langMessages[$message];
        $message = str_replace('%value%', $this->_value, $message);
        foreach ($this->_messageVariables as $ident => $property) {
            $message = str_replace(
                "%$ident%",
                implode(' ', (array) $this->$property),
                $message
            );
        }
        return $message;
    }

    public function _error($message) {
        $this->message = $message;
    }

    public function setMessage($messageString, $messageKey = null)
    {
        if ($messageKey === null) {
            $keys = array_keys($this->_messageTemplates);
            foreach($keys as $key) {
                $this->setMessage($messageString, $key);
            }
            return $this;
        }

        // if (!isset($this->_messageTemplates[$messageKey])) {
        //     throw new \Exception("No message template exists for key '$messageKey'");
        // }

        $this->_messageTemplates[$messageKey] = $messageString;
        return $this;
    }

    public function invokableRuleError($fail, $key) {
        $fail($this->replaceMessage($this->_messageTemplates[$key]));
    }
}
