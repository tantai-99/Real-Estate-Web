<?php
namespace Library\Custom\Hp\Page\AbstractPage;
use Library\Custom\Hp\Page as HpPage;
use Library\Custom\Hp\Page\SectionParts\Form\AbstractForm;
use App\Repositories\HpContact\HpContactRepositoryInterface;
use App\Repositories\HpContactParts\HpContactPartsRepositoryInterface;

class Form extends HpPage {

	protected $_form_title;

	public function initContents() {
		parent::initContents();

		$options = array('hp' => $this->getHp(), 'page'=>$this->getRow());
		$this->form->addSubForm($this->_createFormParts($options), 'form');
	}

	protected function _getItemCodes() {
		return array();
	}

	protected function _createFormParts($options) {
		return new AbstractForm($options);
	}

	protected function _load() {
		parent::_load();

		$where = array(['page_id', $this->getId()], ['hp_id', $this->getHpId()]);

		$data = array();
		$contactRow = \App::make(HpContactRepositoryInterface::class)->fetchRow($where);
		if ($contactRow) {
			$data = $contactRow->toArray();
		}

		$partsData = \App::make(HpContactPartsRepositoryInterface::class)->fetchAll($where)->toArray();
		foreach ($partsData as $pd) {
			$data[ $pd['item_code'] ] = $pd;
		}

		$this->form->getSubForm('form')->setData($data);
	}
}