<?php
namespace App\Rules;
use Illuminate\Support\Facades\App;
use App\Repositories\HpPage\HpPageRepositoryInterface;
use App\Repositories\SpecialEstate\SpecialEstateRepositoryInterface;
class SpecialEstateFileName extends CustomRule {

	const INVALID = 'invalid';
	const IS_NUMERIC = 'is_numeric';
	const PREDEFINED = 'predefined';
	const INVALID_FORMAT = 'invalid_format';
	const CAN_NOT_USE = 'can_not_use';
	const NOT_EMPTY = 'notEmpty';
	
	protected $_hpId = 0;
	protected $_settingId = 0;
	protected $_specialId = null;

	public function setHpId($id) {
		$this->_hpId = $id;
		return $this;
	}

	public function setSettingId($id) {
		$this->_settingId = $id;
		return $this;
	}
	
	public function setSpecialId ($id) {
		$this->_specialId = $id;
		return $this;
	}
	
	public function __construct($options) {
		foreach ($options as $prop => $value) {
			$setter = 'set'.camelize($prop);
			if (method_exists($this, $setter)) {
				$this->$setter($value);
			}
		}
	}

	/**
	 * Validation failure message template definitions
	 *
	 * @var array
	 */
	protected $_messageTemplates = array(
			self::INVALID => "ほかのページと重複しています。",
			self::IS_NUMERIC => "英字を含めて入力してください。",
			self::INVALID_FORMAT => "半角英数字、「-」で入力してください。",
			self::CAN_NOT_USE => "このページ名は使用できません。",
			self::NOT_EMPTY => '値は必須です。',
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
		if (is_null($value) || empty($value)) {
			$this->invokableRuleError($fail, self::NOT_EMPTY);
			return false;
		}

		if (App::make(HpPageRepositoryInterface::class)->inUseFileNameWithoutNew($value, $this->_hpId)) {
			$this->invokableRuleError($fail, self::INVALID);
			return false;
		}
		
		if (!App::make(SpecialEstateRepositoryInterface::class)->isUniqueFilename($value, $this->_hpId, $this->_settingId, $this->_specialId)) {
			$this->invokableRuleError($fail, self::INVALID);
			return false;
		}
		
		$regex = '/^[0-9a-zA-Z\-]*$/';
		if (!preg_match($regex, $value, $matches)) {
			$this->invokableRuleError($fail, self::INVALID_FORMAT);
			return false;
		}
		
		if (!preg_match('/^sp\-/', $value)) {
			$this->invokableRuleError($fail, self::CAN_NOT_USE);
			return false;
		}
		
		if ($value == 'sp-') {
			$this->invokableRuleError($fail, self::NOT_EMPTY);
			return false;
		}

		if (!preg_match('/[a-zA-Z]/', $value)) {
			$this->invokableRuleError($fail, self::IS_NUMERIC);
			return false;
		}

		return true;
	}
}
