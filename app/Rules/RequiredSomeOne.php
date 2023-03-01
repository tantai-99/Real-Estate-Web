<?php

namespace App\Rules;
use Illuminate\Http\Request;

class RequiredSomeOne extends CustomRule
{

	const INVALID = 'invalid';

	protected $_elementNames = array();
	protected $_form = null;
	protected $_type = null;

	public function __construct($options = array(), $form = null, $type = null)
	{
		if (is_array($options) && !isset($options['elementNames'])) {
			$options = array(
				'elementNames' => $options
			);
		}

		if (isset($options['elementNames'])) {
			$this->setElementNames($options['elementNames']);
		}

		if ($form) {
			$this->_form = $form;
		}

		if ($type) {
			$this->_type = $type;
		}
	}

	public function setElementNames($names)
	{
		$this->_elementNames = $names;
		return $this;
	}

	/**
	 * Validation failure message template definitions
	 *
	 * @var array
	 */
	protected $_messageTemplates = array(
		self::INVALID => "いずれかひとつは必須です。"
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
		if ($this->_form) {
			$context = $this->_form->getDatas();
		} else {
			$context = app('request')->all();
		}
		if (empty($context) || !is_array($context)) {
			$this->invokableRuleError($fail, self::INVALID);
			return false;
		}

		if (isset($context['tdk'])) {
			$context = array_merge($context, $context['tdk']);
			unset($context['tdk']);
		}

		if (isset($context['form'])) {
			$context = array_merge($context, $context['form']);
			// unset($context['form']);
		}
		if ($attribute == 'start') {
			if (!is_null($value)) {
				return true;
			}
			$this->invokableRuleError($fail, self::INVALID);
			return false;
		}

		foreach ($this->_elementNames as $name) {
			if ($this->_type) {
				if (!isEmptyKey($context['form'][$this->_type], $name)) {
					return true;
				}
			}
			if (!isEmptyKey($context, $name)) {
				return true;
			}
		}

		$this->invokableRuleError($fail, self::INVALID);
		return false;
	}
}
