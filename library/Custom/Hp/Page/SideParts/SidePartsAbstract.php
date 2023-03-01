<?php
namespace Library\Custom\Hp\Page\SideParts;
use Library\Custom\Hp\Page\Parts\PartsAbstract;
use Library\Custom\Form\Element;
use Library\Custom\Form;
use App\Rules\StringLength;
use App\Rules\InArray;
use App\Repositories\HpSideParts\HpSidePartsRepositoryInterface;

class SidePartsAbstract extends PartsAbstract {

	protected $_columnMap = array(
			'heading' => 'attr_1',
	);

	public function init() {
		parent::init();
		$class_name = get_class($this);
		$this->_typeName = strtolower(str_replace('Library\Custom\Hp\Page\SideParts\\', '', $class_name));

		$element = new ELement\Hidden('parts_type_code', array('disableLoadDefaultDecorators'=>true));
		$element->setRequired(true);
		$this->add($element);

		$element = new ELement\Hidden('sort', array('disableLoadDefaultDecorators'=>true));
		$element->setAttribute('class', 'sort-value');
		$element->setRequired(true);
		$this->add($element);

		$display_options = array(
				1 => '表示',
				0 => '非表示',
		);
		$element = new ELement\Hidden('display_flg', array('disableLoadDefaultDecorators'=>true));
		$element->addValidator(new InArray(array_keys($display_options)));
		$element->setAttribute('class', 'display-isDisplayItem');
		$this->add($element);

		if ($this->hasHeading()) {
			$max = 100;
			$element = new ELement\Text('heading', array('disableLoadDefaultDecorators'=>true));
			$element->addValidator(new StringLength(['min' => null,'max' => $max]));
			$element->setAttributes([
				'class' => 'watch-input-count',
				'data-maxlength' => $max,
			]);
			$this->add($element);
		}

		if ($this->hasElement()) {
			$this->addSubForm(new Form(), 'elements');
		}
		if ($this->getElement('heading_type')) {
			// $this->removeElement('heading_type');
			$this->heading_type->setRequired(false);
		}
		if ($this->getElement('column_sort')) {
			// $this->removeElement('column_sort');
            $this->column_sort->setRequired(false);
		}
	}

	public function getTemplate($prefix = '_forms.hp-page.side-parts.') {
		$template = $prefix;
		if ($this->_template) {
			$template .= $this->_template;
		}
		else {
			$template .= $this->_typeName;
		}
		return $template;
	}

	public function getSaveTable() {
		return \App::make(HpSidePartsRepositoryInterface::class);
    }
    
    public function isJson($string) {
        if(is_string($string) && is_array($jsonData = json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE)) {
            return $jsonData['url'];
        }

        return false;
    }
}