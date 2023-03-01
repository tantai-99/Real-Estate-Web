<?php
namespace Library\Custom\Hp\Page\SectionParts;
use Library\Custom\Form\Element;
use App\Rules\Date;
use App\Rules\StringLengthCKEditor;
use App\Repositories\HpPage\HpPageRepository;

class TdkDate extends Tdk {

	protected $_template = 'tdk';

	public function init() {
		parent::init();

		$element = new Element\Text('date');
		$element->setLabel('日付');
		$element->setRequired(true);
        $element->setAttribute('class', 'datepicker');
		$element->addValidator(new Date());

		if ($time = strtotime($this->_page->date)) {
			$element->setValue(date('Y年m月d日', $time));
		}

        $this->add($element);
        
        $company = $this->_hp->fetchCompanyRow()->checkTopOriginal();
        $pagetype = $this->_page->page_type_code == HpPageRepository::TYPE_INFO_DETAIL;
        $category = $this->_page->fetchNewsCategories();
        $listCategory = $this->genCategory($category);
        if ($company && $pagetype) {
            $element = new Element\Select('notification_class');
            $element->setLabel('カテゴリー');
            $element->setValueOptions($listCategory);
            if (count($category) == 0) {
                $element->setAttribute('disabled', 'disabled');
            }
            $this->add($element);
        }

        if ($pagetype) {
            if (is_null($this->_page->list_title)) {
                $list_title = '<p>'.$this->_page->title.'</p>';
            } else {
                $list_title = $this->_page->list_title;
            }
            $max = 200;
            $element = new Element\Wysiwyg('list_title', array('disableLoadDefaultDecorators'=>true));
            $element->setLabel('一覧タイトル');
            $element->setRequired(true);
            $element->addValidator(new StringLengthCKEditor(['min' => 0, 'max' =>$max]));
            $element->setAttributes([
                'class' => 'watch-input-count',
                'data-maxlength' => $max,
            ]);
            $element->setValue($list_title);
            $this->add($element);
        }
	}


	protected function _beforeSave($data) {
		$data = parent::_beforeSave($data);
		$data['date'] = date('Y-m-d', strtotime(str_replace(array('年', '月', '日'), array('-', '-', ''), $data['date'])));

		return $data;
	}
}