<?php
namespace App\Rules;
class SpecialTesuryoKokokuhi extends CustomRule {

	const INVALID = 'invalid';

	/**
	 * Validation failure message template definitions
	 *
	 * @var array
	 */
	protected $_messageTemplates = array(
			self::INVALID => "値が不正です",
			'MSG1' => "分かれを除く 分かれも含む のいずれかにしてください",
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
		if ($value != 'tesuryo_ari_nomi') {
			return true;
		}
		$context = app('request')->all();
		if (isset($context['tesuryo_kokokuhi']) && is_array($context['tesuryo_kokokuhi'])) {
			$values = $context['tesuryo_kokokuhi'];
		}
		else {
			$values = [];
		}
		if(in_array('tesuryo_wakare_komi', $values)) {
			$this->invokableRuleError($fail, 'MSG1');
			return false;
		}
		return true;
	}
}
