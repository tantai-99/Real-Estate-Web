<?php
namespace App\Rules;

class DateSpan extends CustomRule {
	
	const INVALID = 'invalid';
	
	protected $_elementNames = array();
	protected $_form;
	
	public function __construct($options = array()) {
		if ($options instanceof Zend_Config) {
			$options = $options->toArray();
		} else if (!is_array($options) || func_num_args() > 1) {
			$options = array();
			$options['elementNames'] = func_get_arg(0);
		}
		else if (is_array($options) && !isset($options['elementNames'])) {
			$options = array(
					'elementNames' => $options
			);
		}
		
		if (isset($options['elementNames'])) {
			$this->setElementNames($options['elementNames']);
		}

		if (isset($options['form'])) {
			$this->setForm($options['form']);
		}
	}
	
	public function setElementNames($names) {
		$this->_elementNames = $names;
		return $this;
	}

	public function setForm($form) {
		$this->_form = $form;
		return $this;
	}
	
	/**
	 * Validation failure message template definitions
	 *
	 * @var array
	 */
	protected $_messageTemplates = array(
			self::INVALID => "期間開始日に終了日より未来日が設定されています。"
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
		$context = $this->_form->getValues();
		if (!$context || !is_array($context)) {
			$this->invokableRuleError($fail, self::INVALID);
			return false;
		}
		
		$start = false;
		if (!isEmptyKey($context, $this->_elementNames[0])) {
			$start = strtotime(str_replace(array('年', '月', '日'), array('-', '-', ''), $context[$this->_elementNames[0]]));
		}
		
		$end = false;
		if (!isEmptyKey($context, $this->_elementNames[1])) {
			$end = strtotime(str_replace(array('年', '月', '日'), array('-', '-', ''), $context[$this->_elementNames[1]]));
		}
		
		if ($start === false || $end === false) {
			return true;
		}
		
		if ($start > $end) {
			$this->invokableRuleError($fail, self::INVALID);
			return false;
		}
		
		return true;
	}
}