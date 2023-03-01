<?php
namespace Library\Custom\Hp\Page\SectionParts\Form;
use Library\Custom\Hp\Page\SectionParts\SectionPartsAbstract;
use Library\Custom\Form\Element;
use Library\Custom\Hp\Page\SectionParts\Form\Element\Factory;
use App\Repositories\HpPage\HpPageRepository;
use App\Repositories\HpPage\HpPageRepositoryInterface;
use App\Repositories\HpContactParts\HpContactPartsRepositoryInterface;
use App\Repositories\HpContactParts\HpContactPartsRepository;
use App\Rules\StringLength;
use App\Rules\EmailAddress;
use App\Rules\NoKishuizon;
use Library\Custom\Model\Estate\FdpType;
use App\Rules\NotInArray;
use App\Rules\RequiredSomeOne;
use App\Repositories\HpContact\HpContactRepositoryInterface;
use App\Rules\SingleQuote;

class AbstractForm extends SectionPartsAbstract {

	protected $_template = 'form';
	protected $_mail_to_count = 5;

	public function getItemCodes() {
		return array();
	}

	public function getMailToCount() {
		return $this->_mail_to_count;
	}

	protected $_form_title;

	public function getFormTitle() {
		return $this->_form_title;
	}

	protected $_form_subject_preset = array();
	public function getFormSubjectPresets() {
		return $this->_form_subject_preset;
	}

	protected $_form_subject_placeholder = array();
	public function getFormSubjectPlaceholder() {
		return $this->_form_subject_placeholder;
	}

	protected $_form_placeholder = array();
	public function getFormPlaceholder($key) {
		if(!isset($this->_form_placeholder[$key])) return null; 
		return $this->_form_placeholder[$key];
	}

	protected $_form_subject_choice_count = 0;
	public function getFormSubjectChoiceCount() {
		return $this->_form_subject_choice_count;
	}

	protected $_defaultRequiredType = array();
	public function getDefaultRequiredType() {
		return $this->_defaultRequiredType;
	}

	protected $_onlyRequiredRequiredType = array();
	public function getOnlyRequiredRequiredType() {
		return $this->_onlyRequiredRequiredType;
	}
	
	protected $_typeName;
	public function getTypeName() {
		return $this->_typeName;
	}

	public function init() {
		parent::init();
		$this->_typeName = strtolower(str_replace('Library\Custom\Hp\Page\SectionParts\Form\\', '', get_class($this)));
		
		//--------------------------------------
		// メール設定
		//--------------------------------------

		for ($i = 1, $l = $this->getMailToCount(); $i <= $l; $i++) {
			$name = 'notification_to_' . $i;
			$names[] = $name;

			$max = 255;
			$element = new Element\Text($name, array('disableLoadDefaultDecorators'=>true));
			$element->addValidator(new StringLength(['min' => null, 'max' => $max]));
			$element->addValidator(new EmailAddress());
			$element->setAttributes([
	            'class' => 'watch-input-count',
	            'data-maxlength' => $max,
	        ]);
			$this->add($element);
		}

		$max = 100;
		$element = new Element\Text('notification_subject', array('disableLoadDefaultDecorators'=>true));
		$element->setLabel('メールの件名');
		$element->setRequired(true);
		$element->addValidator(new StringLength(['min' => null, 'max' => $max]));
		$element->addValidator(new NoKishuizon());
		$element->setAttributes([
            'class' => 'watch-input-count',
            'data-maxlength' => $max,
        ]);
		
		$initialValue = '';
		// 物件問い合わせ
		if(\App::make(HpPageRepositoryInterface::class)->isEstateContactPageType($this->getPage()->page_type_code)){
			$page_type_code = $this->getPage()->page_type_code;
			$estateClassName = "";
            switch ($page_type_code) {
                case HpPageRepository::TYPE_FORM_LIVINGLEASE :
                	$estateClassName = "居住用賃貸物件";
                	break;
                case HpPageRepository::TYPE_FORM_OFFICELEASE :
                	$estateClassName = "事業用賃貸物件";
                	break;
                case HpPageRepository::TYPE_FORM_LIVINGBUY :
                	$estateClassName = "居住用売買物件";
                	break;
                case HpPageRepository::TYPE_FORM_OFFICEBUY :
                	$estateClassName = "事業用売買物件";
                	break;
                default:
                	throw new Exception("システムエラー");
                    break;
           }
           $initialValue = "貴店ホームページからの${estateClassName}のお問い合わせ　アットホーム　ホームページ作成ツール";

		// 物件リクエスト
		}else if(\App::make(HpPageRepositoryInterface::class)->isEstateRequestPageType($this->getPage()->page_type_code)){
			$page_type_code = $this->getPage()->page_type_code;
			$estateClassName = "";
            switch ($page_type_code) {
                case HpPageRepository::TYPE_FORM_REQUEST_LIVINGLEASE :
                	$estateClassName = "居住用賃貸物件";
                	break;
                case HpPageRepository::TYPE_FORM_REQUEST_OFFICELEASE :
                	$estateClassName = "事業用賃貸物件";
                	break;
                case HpPageRepository::TYPE_FORM_REQUEST_LIVINGBUY :
                	$estateClassName = "居住用売買物件";
                	break;
                case HpPageRepository::TYPE_FORM_REQUEST_OFFICEBUY :
                	$estateClassName = "事業用売買物件";
                	break;
                default:
                	throw new Exception("システムエラー");
                    break;
           }
           $initialValue = "貴店ホームページからの${estateClassName}リクエスト　アットホーム　ホームページ作成ツール";

		}
		// 通常問い合わせ（会社問い合わせ・資料請求・査定依頼）
		else{
			$initialValue = '貴店ホームページからのお問い合わせ' . '（' . $this->getPage()->getTypeNameJp() . '）' . '　アットホーム　ホームページ作成ツール';
		}
		$element->setValue($initialValue);
		
		$this->add($element);


		//--------------------------------------
		// 自動返信メールの設定
		//--------------------------------------

		$element = new Element\Checkbox('autoreply_flg', array('disableLoadDefaultDecorators'=>true));
		$element->setLabel('自動返信メールを有効にする');
		$element->setValue(1);
		$this->add($element);

		$max = 255;
		$element = new Element\Text('autoreply_from', array('disableLoadDefaultDecorators'=>true));
		$element->setLabel('差出人メールアドレス');
		$element->addValidator(new StringLength(['min' => null, 'max' => $max]));
		$element->addValidator(new EmailAddress());
		$element->setAttributes([
            'class' => 'watch-input-count',
            'data-maxlength' => $max,
        ]);
		$this->add($element);

		$max = 100;
		$element = new Element\Text('autoreply_sender', array('disableLoadDefaultDecorators'=>true));
		$element->setLabel('差出人名');
		$element->addValidator(new StringLength(['min' => null, 'max' => $max]));
		$element->addValidator(new NoKishuizon());
		$element->setAttributes([
            'class' => 'watch-input-count',
            'data-maxlength' => $max,
        ]);
		$profile = getInstanceUser('cms')->getProfile();
		if ($profile) {
			$element->setValue($profile->company_name);
		}
		$this->add($element);

		$max = 100;
		$element = new Element\Text('autoreply_subject', array('disableLoadDefaultDecorators'=>true));
		$element->setLabel('メールの件名');
		$element->addValidator(new StringLength(['min' => null, 'max' => $max]));
		$element->addValidator(new NoKishuizon());
		$element->setAttributes([
            'class' => 'watch-input-count',
            'data-maxlength' => $max,
        ]);
		$element->setValue('お問い合わせありがとうございます');
		$this->add($element);

		$max = 1000;
		$element = new Element\Textarea('autoreply_body', array('disableLoadDefaultDecorators'=>true));
		$element->setLabel('自動返信メールの本文');
		$element->addValidator(new StringLength(['min' => null, 'max' => $max]));
		$element->addValidator(new NoKishuizon());
		$element->setAttributes([
            'class' => 'watch-input-count',
            'data-maxlength' => $max,
            'rows' => 6,
        ]);
		$this->add($element);

		//--------------------------------------
		// フォーム設定
		//--------------------------------------

		$sort = 0;
		$itemCodes = $this->getItemCodes();
        if ((new FdpType)->getPeripheralType($this->getPage()->page_type_code, $this->getHp()->fetchCompanyRow())) {
            $itemCodes = \App::make(HpContactPartsRepositoryInterface::class)->getFDPItemCodes();
        }
		$factory = new Factory();
		foreach ($itemCodes as $key => $type) {
			$form = $factory->create($type, $this);
			$form->getElement('sort')->setValue($sort++);
			$this->addSubForm($form, 'form[' .$type .']');
		}
	}

	public function getSortedFormElements() {
		$elements = $this->getSubForms();
		usort($elements, array($this, '_sortFormElements'));

		return $elements;
	}

	protected function _sortFormElements($a, $b) {
		$as = (int)$a->getElement('sort')->getValue();
		$bs = (int)$b->getElement('sort')->getValue();

		return $as - $bs;
	}

	public function isValid($data, $checkError = true) {

		$_data = $this->_dissolveArrayValue($data, $this->getElementBelongsTo());
		if (isset($_data['autoreply_flg']) && $_data['autoreply_flg'] == 1) {
			$this->getElement('autoreply_from')->setRequired(true);
			$this->getElement('autoreply_subject')->setRequired(true);
			$this->getElement('autoreply_body')->setRequired(true);
		}

		$forms = $this->getSubForms();
		$titles = array();
		foreach ($forms as $name => $form) {


			if ($form->getElement('item_title')) {

				$validator = new NotInArray();
				$validator->setValues($titles);
				$form->getElement('item_title')->addValidator($validator);
				$form->getElement('item_title')->addValidator(new NoKishuizon());
				$form->getElement('item_title')->addValidator(new SingleQuote());

				if (isset($_data[$name]['item_title'])) {
					$titles[] = $_data[$name]['item_title'];
				}
			}
			else {
				$titles[] = $form->getTitle();
			}
		}

		// どれかひとつ必須(会社問い合わせ)
		if ($this->getPage()->page_type_code == HpPageRepository::TYPE_FORM_CONTACT ||
			$this->getPage()->page_type_code == HpPageRepository::TYPE_FORM_DOCUMENT ||
			$this->getPage()->page_type_code == HpPageRepository::TYPE_FORM_ASSESSMENT ||
	        // 物件リクエスト
			$this->getPage()->page_type_code == HpPageRepository::TYPE_FORM_REQUEST_LIVINGLEASE ||
			$this->getPage()->page_type_code == HpPageRepository::TYPE_FORM_REQUEST_OFFICELEASE ||
			$this->getPage()->page_type_code == HpPageRepository::TYPE_FORM_REQUEST_LIVINGBUY ||
			$this->getPage()->page_type_code == HpPageRepository::TYPE_FORM_REQUEST_OFFICEBUY) {

			if ($this->getMailToCount()) {
				$names = array();
				for ($i = 1, $l = $this->getMailToCount(); $i <= $l; $i++) {
					$name = 'notification_to_' . $i;
					$names[] = $name;
				}

				// $this->getElement('notification_to_1')->setAllowEmpty(false);
				$this->getElement('notification_to_1')->addValidator(new RequiredSomeOne($names));
			}
		}

		$isValid = parent::isValid($data);

		$mail = $this->getSubForm("form[".HpContactPartsRepository::PERSON_MAIL."]");
		$tel  = $this->getSubForm("form[".HpContactPartsRepository::PERSON_TEL."]");

		if ($mail && $tel) {
			if (
				$mail->getElement('required_type')->getValue() != HpContactPartsRepository::REQUIREDTYPE_REQUIRED &&
				$tel->getElement('required_type')->getValue() != HpContactPartsRepository::REQUIREDTYPE_REQUIRED
			) {
				$mail->getElement('required_type')->setMessages('メールと電話番号のどちらかは必須です。');
				$tel->getElement('required_type')->setMessages('メールと電話番号のどちらかは必須です。');
				$isValid = false;
			}
		}

		if (isset($_data['autoreply_flg']) && $_data['autoreply_flg'] == 1) {
			$mailBody = $this->getElement('autoreply_body')->getValue();

		    $vowels = array("{", "}", "$");
		    foreach($vowels as $val) {
		        if(strpos($mailBody, $val) !== false) {
		            $this->getElement('autoreply_body')->setMessages("自動返信メールの本文に使用できない文字列が含まれております。エラー文字：" . $val. "");
				    $isValid = false;
				    break;
		        }
		    }
		}

		return $isValid;
	}

	/**
	 * (non-PHPdoc)
	 * @see Library\Custom\Hp\Page\SectionParts\SectionPartsAbstract::save()
	 */
	public function save($hp, $page) {

		$data = array();
		foreach ($this->getElements() as $name => $element) {
			$data[$name] = $element->getValue();
		}
		$data['hp_id'] = $hp->id;
		$data['page_id'] = $page->id;

		$table = \App::make(HpContactRepositoryInterface::class);
		$row = $table->fetchRow(array(['page_id', $page->id], ['hp_id', $hp->id]));
		if (!$row) {
			$row = $table->create($data);
		}

		$row->setFromArray($data);
		$row->save();

		$subForms = $this->getSubForms();
		$subTable = \App::make(HpContactPartsRepositoryInterface::class);
		$subTable->delete(array(['hp_id', $hp->id], ['page_id', $page->id]), true);
		foreach ($subForms as $type => $form) {
			$data = $form->getValues();
			$data['hp_id'] = $hp->id;
			$data['page_id'] = $page->id;
			$data['item_code'] = str_replace(array('form[', ']'), '', $type);
			$subTable->create($data);
		}
	}
}