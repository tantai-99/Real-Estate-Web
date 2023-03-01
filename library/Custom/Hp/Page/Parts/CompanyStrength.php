<?php
namespace Library\Custom\Hp\Page\Parts;
use Library\Custom\Hp\Page\Parts\AbstractParts\CompanyStrengthTips;
use Library\Custom\Hp\Page\Parts\Element;

class CompanyStrength extends CompanyStrengthTips {

	protected $_title = '当社の思い・強み';
	protected $_template = 'company_strength';

	protected $_presetTypes = array(
			'company_strength'
	);

	protected function _createPartsElement($type) {
		$element = null;
		if ($type == 'company_strength') {
			$element = new Element\CompanyStrength();
		}
		return $element;
	}
}