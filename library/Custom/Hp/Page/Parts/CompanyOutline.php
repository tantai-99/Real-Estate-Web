<?php
namespace Library\Custom\Hp\Page\Parts;
use Library\Custom\Hp\Page\Parts\Element;
use Library\Custom\Model\Lists\GuaranteeAssociation;

class CompanyOutline extends Table {

	protected $_is_unique = true;

	protected $_title = '会社概要';

	protected $_presetTypes = array(
			'name',
			'adress',
			'access',
			'establish',
			'president',
			'tel',
			'fax',
			'office_hour',
			'holiday',
			'capital',
			'amount_sold',
			'employee',
			'license_number',
			'organization',
			'guarantee_association',
			'business',
			'group',
			'executive',
			'customer',
			'message',
			'pr',
	);

	protected $_freeTypes = array(
			'free',
	);

	protected function _createPartsElement($type) {
		$element = null;
		switch ($type) {
			case 'name':
				$element = new Element\TextFree();
				$element->setTitle('会社名');
				break;
			case 'adress':
				$element = new Element\TextFree();
				$element->setTitle('所在地');
				break;
			case 'access':
				$element = new Element\TextFree();
				$element->setTitle('交通アクセス');
				break;
			case 'establish':
				$element = new Element\TextFree();
				$element->setTitle('設立年月日');
				break;
			case 'president':
				$element = new Element\TextFree();
				$element->setTitle('代表者');
				break;
			case 'tel':
				$element = new Element\TelFree();
				$element->setTitle('TEL');
				break;
			case 'fax':
				$element = new Element\FaxFree();
				$element->setTitle('FAX');
				break;
			case 'office_hour':
				$element = new Element\TextFree();
				$element->setTitle('営業時間');
				break;
			case 'holiday':
				$element = new Element\TextFree();
				$element->setTitle('定休日');
				break;
			case 'capital':
				$element = new Element\TextFree();
				$element->setTitle('資本金');
				break;
			case 'amount_sold':
				$element = new Element\TextFree();
				$element->setTitle('年商');
				break;
			case 'employee':
				$element = new Element\TextFree();
				$element->setTitle('従業員数');
				break;
			case 'license_number':
				$element = new Element\TextFree();
				$element->setTitle('免許番号');
				break;
			case 'organization':
				$element = new Element\TextFree();
				$element->setTitle('所属団体');
				break;
			case 'guarantee_association':
				$element = new Element\SelectFree();
				$element->setTitle('保証協会');
				$element->setValueOptions(GuaranteeAssociation::getInstance()->getAll());
				break;
			case 'business':
				$element = new Element\TextFree();
				$element->setTitle('事業内容');
				break;
			case 'group':
				$element = new Element\TextFree();
				$element->setTitle('グループ会社');
				break;
			case 'executive':
				$element = new Element\TextareaFree();
				$element->setTitle('役員');
				break;
			case 'customer':
				$element = new Element\TextareaFree();
				$element->setTitle('主要取引先');
				break;
			case 'message':
				$element = new Element\TextareaFree();
				$element->setTitle('代表挨拶');
				break;
			case 'pr':
				$element = new Element\TextareaFree();
				$element->setTitle('PRコメント');
				break;
			case 'free':
				$element = new Element\TextFree();
				$element->setTitle('フリーテキスト');
			default:
				break;
		}
		
		if ($element && $type != 'free') {
			$element->setIsUnique(true);
		}

		return $element;
	}

	public function setPreset() {
		$this->getElement('heading')->setValue('会社概要');
		return parent::setPreset();
	}
}