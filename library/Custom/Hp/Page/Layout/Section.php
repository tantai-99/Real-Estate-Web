<?php
namespace Library\Custom\Hp\Page\Layout;
use Library\Custom\Form;
use Library\Custom\User\Cms;

class Section extends Form {

	protected $_title;
    protected $_page;

	public function setTitle($title) {
		$this->_title = $title;
	}

	public function getTitle() {
		return $this->_title;
	}

	public function getTemplate() {
		return '_forms.hp-page.layout.section';
	}

    // 4425 Add condition check Top page
    public function setPage($page) {
        $this->_page = $page;
    }

	public function isValid($data, $checkError = true) {
		$isValid = true;
		$subForms = $this->getSubForms();
		foreach ($subForms as $name => $form) {
            if (Cms::getInstance()->checkHasTopOriginal() && $this->_page == config('constants.hp_page.TYPE_TOP')) {
                $form->removeElement('sort');
            }
			$isValid = $form->isValid(isset($data[$name])?$data[$name]:array(), false) && $isValid;
		}
		return $isValid;
	}

	public function save($hp, $page) {
		$subForms = $this->getSubForms();
		foreach ($subForms as $name => $form) {
			$form->save($hp, $page);
		}
	}

	public function getUsedImages() {
		$images = array();
		$subForms = $this->getSubForms();
		foreach ($subForms as $name => $form) {
			if ($_images = $form->getUsedImages()) {
				$images = array_merge($images, $_images);
			}
		}
		return $images;
	}
	
	public function getUsedFile2s()
	{
		$file2s		= array()	;
		$subForms	= $this->getSubForms();
		foreach ($subForms as $name => $form) {
			if ( $_file2s = $form->getUsedFile2s() ) {
				$file2s = array_merge( $file2s, $_file2s ) ;
			}
		}
		return $file2s ;
	}
}