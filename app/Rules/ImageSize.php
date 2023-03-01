<?php

namespace App\Rules;

class ImageSize extends CustomRule
{
	const WIDTH_TOO_BIG = 'WidthToBig';
	const HEIGHT_TOO_BIG = 'HeightToBig';

	protected $_options;
	protected $_messageCustoms = [];

	public function __construct($options = array(), $messages = array())
	{
		$this->_options = $options;
		$this->_messageCustoms = $messages;
	}

	/**
	 * Validation failure message template definitions
	 *
	 * @var array
	 */
	protected $_messageTemplates = array();

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
		if (!getimagesize($value)) {
			return true;
		}
		$error = false;
		foreach($this->_options as $key=>$option) {
			$rules = [
				$attribute => 'dimensions:'.$key.'='.$option
			];
			$validator = \Validator::make([$attribute => $value], $rules);
			if ($validator->fails()) {
				switch ($key) {
					case 'max_width':
						if (!isset($this->_messageTemplates[self::WIDTH_TOO_BIG])) {
							if (isset($this->_messageCustoms[self::WIDTH_TOO_BIG])) {
								$fail($this->_messageCustoms[self::WIDTH_TOO_BIG]);
							} else {
								$fail('横が'.$option.'ピクセルの制限を超えています。');
							}
						}
						break;
					case 'max_height':
						if (!isset($this->_messageTemplates[self::HEIGHT_TOO_BIG])) {
							if (isset($this->_messageCustoms[self::HEIGHT_TOO_BIG])) {
								$fail($this->_messageCustoms[self::HEIGHT_TOO_BIG]);
							} else {
								$fail('縦が'.$option.'ピクセルの制限を超えています。');
							}
						}
						break;
					default:
						# code...
						break;
				}
				$error = true;
			}
		}

		if ($error) {
			return false;
		}
		return true;
	}
}
