<?php
namespace App\Rules;

use Illuminate\Contracts\Validation\InvokableRule;

class NotEmpty extends CustomRule implements InvokableRule {

    const INVALID  = 'notEmptyInvalid';
    const IS_EMPTY = 'isEmpty';

    /**
     * Indicates whether the rule should be implicit.
     *
     * @var bool
     */
    public $implicit = true;

    // const INVALID = 'Invalid';

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
            self::IS_EMPTY => $this->messages,
            self::INVALID  => "Invalid type given. String, integer, float, boolean or array expected",
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
        if ($value !== null && !is_string($value) && !is_int($value) && !is_float($value) &&
            !is_bool($value) && !is_array($value) && !is_object($value)) {
                $this->invokableRuleError($fail, self::INVALID);
            return false;
        }

        $this->_setValue($value);

        if (is_object($value) && (count($value) == 0)) {
            $this->invokableRuleError($fail, self::IS_EMPTY);
            return false;
        }

        if (is_string($value) && (preg_match('/^\s+$/s', $value))) {
            $this->invokableRuleError($fail, self::IS_EMPTY);
            return false;
        }

        if ($value === null) {
            $this->invokableRuleError($fail, self::IS_EMPTY);
            return false;
        }

        if (is_array($value) && ($value == array())) {
            $this->invokableRuleError($fail, self::IS_EMPTY);
            return false;
        }

        if (is_string($value) && (trim($value) == '')) {
            $this->invokableRuleError($fail, self::IS_EMPTY);
            return false;
        }

        if (is_float($value) && ($value == 0.0)) {
            $this->invokableRuleError($fail, self::IS_EMPTY);
            return false;
        }

        if (is_bool($value) && ($value == false)) {
            $this->invokableRuleError($fail, self::IS_EMPTY);
            return false;
        }
        return true;
    }
	static public function createForHpPagePartsElement() {
		$notEmpty = new NotEmpty(array('messages' => '内容を入力してください。項目自体不要な場合は×で削除してください。'));
		return $notEmpty;
	}
	
	
	static public function addToHpPagePartsElement($element) {
		$validators = $element->getValidators();
		array_unshift($validators, static::createForHpPagePartsElement());
        $element->setValidator($validators);
	}
}