<?php
namespace Library\Custom\Hp\Page\SectionParts\Form\Element;
use Library\Custom\Form\Element;
use App\Repositories\HpPage\HpPageRepositoryInterface;
use App\Repositories\HpPage\HpPageRepository;
use App\Rules\StringLength;
use App\Rules\NoKishuizon;
use App\Rules\SingleQuote;
use App\Rules\RequiredSomeOne;
use App\Repositories\HpContactParts\HpContactPartsRepository;

class Multi extends Text {

	protected $_free_choice_count = 0;
	protected $_type = null;

	public function setFreeChoiceCount($count) {
		$this->_free_choice_count = $count;
		return $this;
	}

	public function setType($type) {
		$this->_type = $type;
		return $this;
	}

	public function getFreeChoiceCount() {
		return $this->_free_choice_count;
	}

	protected $_preset_choices = array();

	public function setPresetChoices($choices) {
		$this->_preset_choices = $choices;
		return $this;
	}

	public function getPresetChoices() {
		return $this->_preset_choices;
	}

	protected $_preset_placeholders = array();
	public function setPresetPlaceholders($placeholders) {
		$this->_preset_placeholders = $placeholders;
		return $this;
	}
	public function getPresetPlaceholders() {
		return $this->_preset_placeholders;
	}

	public function init() {
		parent::init();
		$max = 100;
		$names = array();
		$validator = null;
		$isRequiredSingleQuoteValidator = false;
		// お問い合わせ系のページではシングルクオートを入力できないようにする
		if (isset($_GET['id'])) {
			$pageCategoryCode = \App::make(HpPageRepositoryInterface::class)->fetchRowById($_GET['id'])->page_category_code;
			if ($pageCategoryCode == HpPageRepository::CATEGORY_FORM) {
				$isRequiredSingleQuoteValidator = true;
			}
		}
		for ($i = 1, $l = $this->getFreeChoiceCount(); $i <= $l; $i++) {
			$name = 'choice_' . $i;
			$names[] = $name;
			$element = new Element\Text($name);

			//プレースフォルダーがある場合は追加する
			if(isset($this->getPresetPlaceholders()[$i-1])) {
				$element->setAttribute('placeholder', $this->getPresetPlaceholders()[$i-1]);
			}

			// どれかひとつ必須
			if ($i == 1) {
				$validator = new RequiredSomeOne(array(), null, $this->_type);
				// $element->setAllowEmpty(false)
				$element->addValidator($validator);
			}

			if ($isRequiredSingleQuoteValidator) {
				$singleQuoteValidation = new SingleQuote();
				$presetChoiceCount = count($this->getPresetChoices());
				$listCount = $i + $presetChoiceCount;
				$singleQuoteValidation->setMessage('選択肢' . $listCount . 'に' . $singleQuoteValidation::INVALIDMESSAGE);
				$element->addValidator(new StringLength(['min' => null, 'max' => $max]));
				$element->addValidator(new NoKishuizon());
				$element->addValidator($singleQuoteValidation);
			} else {
				$element->addValidator(new StringLength(['min' => null, 'max' => $max]));
				$element->addValidator(new NoKishuizon());
			}
	        $element->setAttribute('class', 'watch-input-count');
	        $element->setAttribute('data-maxlength', $max);
			$this->add($element);
		}

		if($validator != null) $validator->setElementNames($names);
	}

	public function isValid($data, $checkError = true) {
		if ($this->getFreeChoiceCount()) {
			$_data = $this->_dissolveArrayValue($data, $this->getElementBelongsTo());
            if ($_data['required_type'] == HpContactPartsRepository::REQUIREDTYPE_OPTION){
				for ($i = 1, $l = $this->getFreeChoiceCount(); $i <= $l; $i++) {
                    $choice = 'choice_' . $i;
                    if (!empty($_data[$choice])) {
						$this->getElement('choice_1')->removeValidator('RequiredSomeOne');
					}
                }
			}
			if ($_data['required_type'] == HpContactPartsRepository::REQUIREDTYPE_REQUIRED){
				for ($i = 1, $l = $this->getFreeChoiceCount(); $i <= $l; $i++) {
                    $choice = 'choice_' . $i;
                    if (!empty($_data[$choice])) {
						$this->getElement('choice_1')->removeValidator('RequiredSomeOne');
					}
                }
			}
			if (
				(isset($_data['required_type']) && $_data['required_type'] == HpContactPartsRepository::REQUIREDTYPE_HIDDEN) ||
				($this->getElement('choices_type_code') && isset($_data['choices_type_code']) && in_array($_data['choices_type_code'], array('text', 'textarea'))) ||
				$this->getPresetChoices()
			) {
				$this->getElement('choice_1')->removeValidator('RequiredSomeOne');
				// ->setAllowEmpty(true)
			}
            // ATHOME_HP_DEV-5285 問い合わせフォームの内容の削除タイミングを改善する
			// 問い合わせ系のページでテキストボックス(1行)もしくはテキストボックス(複数行)を選択し、ページを保存すると選択肢のバリデーションを解除する
			if ($this->getElement('choices_type_code') && isset($_data['choices_type_code']) && in_array($_data['choices_type_code'], array('text', 'textarea'))) {
                for ($i = 1, $l = $this->getFreeChoiceCount(); $i <= $l; $i++) {
                    $choice = 'choice_' . $i;
                    $this->getElement($choice)->removeValidator('StringLength');
                    // ->setAllowEmpty(true)

                    $this->getElement($choice)->removeValidator('SingleQuote');
                    // ->setAllowEmpty(true)
                    $_data[$choice] = '';
                }
            }
		}
		return parent::isValid($_data, false);
	}

}