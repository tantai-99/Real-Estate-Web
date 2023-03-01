<?php
namespace App\Rules;
class SpecialPublishEstate extends CustomRule {

	const INVALID = 'invalid';

	/**
	 * Validation failure message template definitions
	 *
	 * @var array
	 */
	protected $_messageTemplates = array(
			self::INVALID => "値が不正です",
			'MSG1' => "公開する物件の種類を選択してください",
			'MSG2' => "2次広告自動公開の物件のみが選択されている場合は公開する物件の絞り込みオプションは利用できません",
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
		if ($value != 'only_er_enabled') {
			return true;
		}
		$context = app('request')->all();
		
		if (isset($context['publish_estate']) && is_array($context['publish_estate'])) {
			$values = $context['publish_estate'];
		}
		else {
			$values = [];
		}
		
		if(($key = array_search('only_er_enabled', $values)) !== false) {
			unset($values[$key]);
		}
		
		if(count($values) == 0) {
			$this->invokableRuleError($fail, 'MSG1');
			return false;
		}
		if(count($values) == 1 && $values[0] == 'niji_kokoku_jido_kokai') {
			$this->invokableRuleError($fail, 'MSG2');
			return false;
		}
		
		return true;
	}
}
