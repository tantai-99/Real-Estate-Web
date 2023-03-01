<?php

namespace App\Http\Form;

use Library\Custom\Form;
use Library\Custom\Form\Element;
use App\Rules;
use Library\Custom\Util;
use Illuminate\Support\Facades\App;
use App\Repositories\HpSiteImage\HpSiteImageRepositoryInterface;
use Library\Custom\Model\Lists\SiteLogoType;
use Library\Custom\Model\Lists\QRType;
use Library\Custom\Model\Lists\FooterLinkLevel;
use Library\Custom\Model\Lists\HankyoPlus;

class Site extends Form
{
	public function __construct()
	{
		parent::__construct();

		$hp = getInstanceUser('cms')->getCurrentHp();
		$siteImageTable = App::make(HpSiteImageRepositoryInterface::class);

		$max = 22;
		$element = new Element\Text('title');
		$element->setLabel('サイト名');
		$element->setRequired(true);
		$element->setAttributes(
			array(
				'class' => array('watch-input-count'),
				'data-maxlength' => $max,
				'placeholder' => '○○市の不動産なら株式会社○○不動産',
			)
		);
		$element->addValidator(new Rules\StringLength(array('min' => 8, 'max' => $max)));
		$this->add($element);

		$max = 87;
		$element = new Element\Textarea('description');
		$element->setLabel('サイトの説明');
		$element->setRequired(true);
		$element->addValidator(new Rules\StringLength(array('min' => 26, 'max' => $max)));
		$element->setAttributes(
			array(
				'class' => array('watch-input-count'),
				'data-maxlength' => $max,
				'rows' => 4,
				'placeholder' => '○○市を中心に不動産のことなら○○不動産にお任せください。物件情報だけでなく○○市の周辺情報やおすすめスポットなどの情報も盛りだくさん。安心のサポート体制が整っています。',
			)
		);
		$this->add($element);

		$keywords = new Keyword();
		$this->addSubForm($keywords, 'keywords');

		$element = new Element\Hidden('favicon');
		$element->setLabel('ファビコン');
		$element->setAttributes(
			array(
				'class' => array('upload-file-id'),
				'data-upload-to' => '/api-upload/favicon',
				'data-view' => '/image/favicon',
			)
		);
		$element->addValidator(new Rules\ImageContent($siteImageTable, $hp->id, array(['type', config('constants.hp_site_image.TYPE_FAVICON')])));
		$this->add($element);

		$element = new Element\Hidden('webclip');
		$element->setLabel('ウェブクリップアイコン');
		$element->setAttributes(
			array(
				'class' => array('upload-file-id'),
				'data-upload-to' => '/api-upload/webclip',
				'data-view' => '/image/webclip',
			)
		);
		$element->addValidator(new Rules\ImageContent($siteImageTable, $hp->id, array(['type', config('constants.hp_site_image.TYPE_WEBCLIP')])));;
		$this->add($element);

		$max = 100;
		$element = new Element\Text('company_name');
		$element->setLabel('会社名');
		$element->setRequired(true);
		$element->setAttributes(
			array(
				'class' => array('watch-input-count'),
				'data-maxlength' => $max,
			)
		);
		$element->addValidator(new Rules\StringLength(array('min' => null, 'max' => $max)));
		$this->add($element);

		$max = 100;
		$element = new Element\Text('adress');
		$element->setLabel('住所');
		$element->setAttributes(
			array(
				'class' => array('watch-input-count'),
				'data-maxlength' => $max,
			)
		);
		$element->addValidator(new Rules\StringLength(array('min' => null, 'max' => $max)));
		$this->add($element);

		$max = 13;
		$element = new Element\Text('tel');
		$element->setLabel('電話番号');
		$element->setAttributes(
			array(
				'class' => array('watch-input-count'),
				'data-maxlength' => $max,
			)
		);
		$element->addValidator(new Rules\Tel());
		$element->addValidator(new Rules\StringLength(array('min' => null, 'max' => $max)));
		$this->add($element);

		$max = 100;
		$element = new Element\Text('office_hour');
		$element->setLabel('営業時間');
		$element->setAttributes(
			array(
				'class' => array('watch-input-count'),
				'data-maxlength' => $max,
			)
		);
		$element->addValidator(new Rules\StringLength(array('min' => null, 'max' => $max)));
		$this->add($element);

		$max = 30;
		$element = new Element\Text('outline');
		$element->setLabel('サイトの概要');
		$element->setRequired(true);
		$element->setAttributes(
			array(
				'class' => array('watch-input-count'),
				'data-maxlength' => $max,
			)
		);
		$element->addValidator(new Rules\StringLength(array('min' => 10, 'max' => $max)));
		$this->add($element);

		$siteLogoType = new SiteLogoType();

		$element = new Element\Hidden('logo_pc');
		$element->setLabel('サイトロゴ（PC）');
		$element->setAttributes(
			array(
				'class' => array('upload-file-id'),
				'data-upload-to' => '/api-upload/site-logo-pc',
				'data-view' => '/image/site-logo-pc',
			)
		);

		// $element->setAllowEmpty(false);
		$element->addValidator(new Rules\ImageContent($siteImageTable, $hp->id, array(['type', config('constants.hp_site_image.TYPE_SITELOGO_PC')])));;
		$requiredSomeOne = new Rules\RequiredSomeOne(array('elementNames' => array('logo_pc', 'logo_pc_text')));
		$requiredSomeOne->setMessage('画像、またはテキスト（社名）のいずれかは必須です。');
		$element->addValidator($requiredSomeOne);

		$this->add($element);

		$max = 30;
		$element = new Element\Text('logo_pc_title');
		$element->setLabel('画像タイトル');
		$element->addValidator(new Rules\StringLength(array('min' => null, 'max' => $max)));
		$element->setAttributes(
			array(
				'class' => array('watch-input-count'),
				'data-maxlength' => $max,
			)
		);
		$this->add($element);

		$max = 100;
		$element = new Element\Text('logo_pc_text');
		$element->setLabel('画像と表示する社名');
		$element->setAttributes(
			array(
				'class' => array('watch-input-count'),
				'data-maxlength' => $max,
				'placeholder' => 'テキスト(社名）',
			)
		);
		$element->addValidator(new Rules\StringLength(array('min' => null, 'max' => $max)));
		$this->add($element);

		$element = new Element\Hidden('logo_sp');
		$element->setLabel('サイトロゴ（スマホ）');
		$element->setAttributes(
			array(
				'class' => array('upload-file-id'),
				'data-upload-to' => '/api-upload/site-logo-sp',
				'data-view' => '/image/site-logo-sp',
			)
		);
		$element->addValidator(new Rules\ImageContent($siteImageTable, $hp->id, array(['type', config('constants.hp_site_image.TYPE_SITELOGO_SP')])));
		// $element->setAllowEmpty(false);
		$requiredSomeOne = new Rules\RequiredSomeOne(array('elementNames' => array('logo_sp', 'logo_sp_text')));
		$requiredSomeOne->setMessage('画像、またはテキスト（社名）のいずれかは必須です。');
		$element->addValidator($requiredSomeOne);
		$this->add($element);

		$max = 30;
		$element = new Element\Text('logo_sp_title');
		$element->setLabel('画像タイトル');
		$element->setAttributes(
			array(
				'class' => array('watch-input-count'),
				'data-maxlength' => $max,
			)
		);
		$element->addValidator(new Rules\StringLength(array('min' => null, 'max' => $max)));
		$this->add($element);

		$max = 100;
		$element = new Element\Text('logo_sp_text');
		$element->setLabel('画像と表示する社名');
		$element->setAttributes(
			array(
				'class' => array('watch-input-count'),
				'data-maxlength' => $max,
				'placeholder' => 'テキスト(社名）',
			)
		);
		$element->addValidator(new Rules\StringLength(array('min' => null, 'max' => $max)));
		$this->add($element);

		$max = 100;
		$element = new Element\Text('copylight');
		$element->setLabel('コピーライト');
		$element->setAttributes(
			array(
				'class' => array('watch-input-count'),
				'data-maxlength' => $max,
				'placeholder' => 'At Home Co.,Ltd',
			)
		);
		$element->addValidator(new Rules\StringLength(array('min' => null, 'max' => $max)));
		$element->addValidator(new Rules\Hankaku());
		$this->add($element);

		$flagOptions = array('1' => '有効', '0' => '無効');

		$element = new Element\Radio('fb_like_button_flg');
		$element->setLabel('いいねボタンの設置');
		$element->setRequired(true);
		$element->setValueOptions($flagOptions);
		$element->setValue(1);
		$this->add($element);

		$element = new Element\Radio('fb_timeline_flg');
		$element->setLabel('タイムラインの設置');
		$element->setRequired(true);
		$element->setValueOptions($flagOptions);
		$element->setValue(0);
		$this->add($element);

		$max = 255;
		$label = 'FacebookページURL';
		$element = new Element\Text('fb_page_url');
		$element->setLabel($label);
		$element->setAttributes(
			array(
				'class' => array('watch-input-count'),
				'data-maxlength' => $max,
			)
		);
		$element->addValidator(new Rules\StringLength(array('min' => null, 'max' => $max)));
		$element->addValidator(new Rules\Url());
		$this->add($element);

		$element = new Element\Radio('tw_tweet_button_flg');
		$element->setLabel('Tweetボタンの設置');
		$element->setRequired(true);
		$element->setValueOptions($flagOptions);
		$element->setValue(1);
		$this->add($element);

		$element = new Element\Radio('tw_timeline_flg');
		$element->setLabel('タイムラインの設置');
		$element->setRequired(true);
		$element->setValueOptions($flagOptions);
		$element->setValue(0);
		$this->add($element);

		$max = 100;
		$label = 'TwitterウィジェットID';
		$element = new Element\Text('tw_widget_id');
		$element->setLabel($label);
		$element->setAttributes(
			array(
				'class' => array('watch-input-count'),
				'data-maxlength' => $max,
			)
		);
		$element->addValidator(new Rules\StringLength(array('min' => null, 'max' => $max)));
		$this->add($element);

		$max = 100;
		$label = 'Twitterユーザ名';
		$element = new Element\Text('tw_username');
		$element->setLabel($label);
		$element->setAttributes(
			array(
				'class' => array('watch-input-count'),
				'data-maxlength' => $max,
			)
		);
		$element->addValidator(new Rules\StringLength(array('min' => null, 'max' => $max)));
		$this->add($element);

		$element = new Element\Radio('line_button_flg');
		$element->setLabel('LINE');
		$element->setRequired(true);
		$element->setValueOptions($flagOptions);
		$element->setValue(1);
		$this->add($element);

		// LINE公式アカウント
		$element = new Element\Text('line_at_freiend_qrcode');
		$element->setLabel('「友だち追加」QRコード　埋め込みコード');
		$element->setAttributes(
			array(
				'class' => array('watch-input-count'),
				'rows' => 4,
				'placeholder' => h('<img src="https://qr-official.line.me/○/○○.png">'),	
			)
		);
		$element->addValidator(new Rules\LineAtTag());
		$this->add($element);

		$element = new Element\Text('line_at_freiend_button');
		$element->setLabel('「友だち追加」ボタン　埋め込みコード');
		$element->setAttributes(
			array(
				'class' => array('watch-input-count'),
				'rows' => 4,
				'placeholder' => h('<a href="https://lin.ee/○○"><img height="36" border="0" alt="友だち追加" src="https://scdn.line-apps.com/n/line_add_friends/btn/ja.png"></a>'),
			));

		$element->addValidator(new Rules\LineAtTag());
		$this->add($element);

		$qrType = new QRType();
		$element = new Element\Radio('qr_code_type');
		$element->setLabel('QRコード');
		$element->setRequired(true);
		$element->setValueOptions($qrType->getAll());
		$element->setValue(config('constants.qr_type.COMMON'));
		$element->setSeparator(' ');
		$this->add($element);

		$flLevel = new FooterLinkLevel();
		$element = new Element\Radio('footer_link_level');
		$element->setLabel('フッターリンク一覧');
		$element->setValueOptions($flLevel->getAll());
		$element->setValue(config('constants.footer_link_level.COMMON'));
		$element->setSeparator(' ');
		$this->add($element);

		$max = 16;
		$element = new Element\Password('test_site_password');
		$element->setLabel('パスワード');
		$element->setRequired(true);
		$element->setAttributes(
			array(
				'renderPassword' => true,
				'class' => array('watch-input-count'),
				'data-maxlength' => $max,
				'autocomplete' => 'new-password',
			)
		);
		$element->addValidator(new Rules\StringLength(array('min' => 8, 'max' => $max)));
		$element->addValidator(new Rules\Hankaku());
		$this->add($element);

		$hankyoPlus = new HankyoPlus();
		$element = new Element\Radio('hankyo_plus_use_flg');
		$element->setLabel('ユーザー同意　チェックボックス');
		$element->setRequired(true);
		$element->setValueOptions($hankyoPlus->getAll());
		$element->setSeparator(' ');
		$element->setValue(HankyoPlus::FORM_NOT_VIEW);
		$this->add($element);

		// set elements base on setting top original
		if (getInstanceUser('cms')->checkHasTopOriginal()) {
			$this->setElementsBaseTopOriginal();
		}
	}

	public function isValid($data, $checkErrors = true)
	{
		$this->initRequired($data);
		$isValid = parent::isValid($data);
		if (isset($data['fb_timeline_flg']) && $data['fb_timeline_flg'] == '1' && empty($data['fb_page_url'])) {
			$this->getElement('fb_page_url')->setMessages(["URLを入力してください。"]);
			$isValid = false;
		}
		// custom validate, disallow value space
		if($data['line_at_freiend_qrcode'] !== ''
		&& $data['line_at_freiend_qrcode'] !== null
		&& empty(trim($data['line_at_freiend_qrcode']))) {
			$this->getElement('line_at_freiend_qrcode')->setMessages(["※LINE公式アカウント以外のコードは埋め込めません。"]);
		    $isValid = false;
		}
		return $isValid;
	}

	public function initRequired($data)
	{
		if ($data) {
			foreach (array('pc', 'sp') as $type) {
				$name = 'logo_' . $type;
				$titleName = 'logo_' . $type . '_title';
				if (!isEmptyKey($data, $name)) {
					$this->getElement($titleName)->setRequired(true);
				}
			}

			if (isset($data['tw_timeline_flg']) && $data['tw_timeline_flg'] == '1') {
				$this->getElement('tw_username')->setRequired(true);
			}
		}
		return $this;
	}

	public function getMessages()
	{
		$messages = array();

		$groups = array(
			'logo_pc', 'logo_pc_title', 'logo_pc_text',
			'logo_sp', 'logo_sp_title', 'logo_sp_text',
			'fb_like_button_flg', 'fb_timeline_flg', 'fb_page_url',
			'tw_tweet_button_flg', 'tw_timeline_flg', 'tw_widget_id', 'tw_username'
		);
		foreach ($this->getElements() as $name => $element) {

			if (!$element->hasErrors()) {
				continue;
			}

			if (in_array($name, $groups)) {
				$messages[$name] = $this->getGroupErrors(array($name));
			} else {
				$messages[$name] = $element->getMessages();
			}
			
		}
		foreach($this->getSubForms() as $name=>$form) {
			foreach ($form->getElements() as $name => $elem) {
				if (!$elem->hasErrors()) {
					continue;
				}

				$messages[$elem->getId()] = $form->getGroupErrors(array($name));
			}
		}
		return $messages;
	}

	public function setElementsBaseTopOriginal()
	{
		$names = array(
			// section header/footer
			'company_name', 'adress', 'tel', 'office_hour', 'outline', 'logo_pc',
			'logo_pc_title', 'logo_pc_text', 'logo_sp', 'logo_sp_title', 'logo_sp_text',

			// section copyright
			'copylight',
		);
		foreach ($names as $name) {
			$this->removeElement($name);
		}
	}
}
