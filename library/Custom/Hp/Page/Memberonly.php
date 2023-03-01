<?php
namespace Library\Custom\Hp\Page;
use Library\Custom\Hp\Page as HpPage;
use Library\Custom\Hp\Page\SectionParts\Memberonly as PartMemberonly;

class Memberonly extends HpPage {

	public function initContents() {
		parent::initContents();

		$options = array('hp' => $this->getHp(), 'page'=>$this->getRow());
		$this->form->addSubForm(new PartMemberonly($options), 'memberonly');
	}

	protected function _load() {
		parent::_load();

		$data = $this->_row->toArray();
		$data['member_password_confirm'] = $data['member_password'];
		$this->form->getSubForm('memberonly')->setDefaults($data);
	}
}