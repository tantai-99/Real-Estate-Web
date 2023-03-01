<?php
namespace Library\Custom\Hp\Page\SectionParts;
use Library\Custom\Form\Element;
use App\Repositories\HpPage\HpPageRepositoryInterface;
use App\Rules\StringLength;
use App\Rules\Hankaku;
use App\Rules\Confirm;

class Memberonly extends SectionPartsAbstract {

	public function init() {
		$max = 30;
		$element = new Element\Text('member_id', array('disableLoadDefaultDecorators'=>true));
		$element->setRequired(true);
		$element->addValidator(new StringLength(['min' => 8, 'max' => $max]));
		$element->addValidator(new Hankaku());
		$element->setAttributes([
            'class' => 'watch-input-count',
            'data-maxlength' => $max,
        ]);
		$this->add($element);

		$max = 30;
		$element = new Element\Password('member_password', array('disableLoadDefaultDecorators'=>true));
		$element->setRequired(true);
		// $element->setRenderPassword(true);
		$element->addValidator(new StringLength(['min' => 8, 'max' => $max]));
		$element->addValidator(new Hankaku());
		$element->setAttributes([
            'class' => 'watch-input-count',
            'data-maxlength' => $max,
        ]);
		$this->add($element);

		$max = 30;
		$element = new Element\Password('member_password_confirm', array('disableLoadDefaultDecorators'=>true));
		$element->setRequired(true);
		// $element->setRenderPassword(true);
		$element->addValidator(new StringLength(['min' => null, 'max' => $max]));
		$element->addValidator(new Confirm(array('label'=>'パスワード', 'confirmKey'=>'member_password')));
		$element->setAttributes([
            'class' => 'watch-input-count',
            'data-maxlength' => $max,
        ]);
		$this->add($element);
	}

	/**
	 * (non-PHPdoc)
	 * @see Library\Custom\Hp\Page\SectionParts\SectionPartsAbstract::save()
	 */
	public function save($hp, $page) {
		$data = $this->getValues(true);

		unset($data['member_password_confirm']);
		$data['member_only_flg'] = 1;

		$table = \App::make(HpPageRepositoryInterface::class);
		$table->update(array(['hp_id', $this->_hp->id], ['id', $this->_page->id]), $data);
	}

	public function setDefaults(array $values) {
		foreach ($this->getElements() as $paramName => $colName) {
			if (isset($values[$paramName])) {
				$colName->setValue($values[$paramName]);
			}
		}
	}
}