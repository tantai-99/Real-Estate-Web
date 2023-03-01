<?php
namespace Library\Custom\Hp\Page\Parts;
use App\Repositories\HpPage\HpPageRepositoryInterface;
use App\Repositories\HpPage\HpPageRepository;
use Library\Custom\Hp\Page\Parts\Element;

class SetLinkAuto extends Table {

    protected $_is_unique = true;
    
    protected $_template = 'set-link-auto';

	protected $_title = '自動リンク設定';
	protected $_has_heading = false;

	protected $_presetTypes = array(
			'lead',
			'contact',
	);
	public function init() {
		parent::init();
		if ($this->isArticle()) {
			$this->_presetTypes = array('contact');
		}
	}
	public function isArticle() {
		return in_array($this->getPage()->page_type_code, \App::make(HpPageRepositoryInterface::class)->getPageArticleByCategory(HpPageRepository::CATEGORY_ARTICLE));
	}

	public function isOriginalCategory($pageTypeCode) {
		if ($pageTypeCode == HpPageRepository::TYPE_LARGE_ORIGINAL || $pageTypeCode == HpPageRepository::TYPE_SMALL_ORIGINAL) {
				return true;
			}
			return false;
	}

	protected function _createPartsElement($type) {
		$element = null;
		switch ($type) {
			case 'lead':
				$element = new Element\TextareaLinkAuto();
				$element->setTitle('リード文');
				break;
			case 'contact':
				$element = new Element\Checkbox();
                $element->setTitle('お問い合わせへのリンクを設置する');
				break;
			default:
				break;
		}
		
		if ($element) {
			$element->setIsUnique(true);
		}

		return $element;
	}
	public function getPageTypeCode() {
		return $this->getPage()->page_type_code;
	}

	public function setPreset() {
		// $this->heading->setValue('会社概要');
		return parent::setPreset();
	}
}