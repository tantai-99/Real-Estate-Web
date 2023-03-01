<?php
namespace Library\Custom\Hp\Page\Parts\Element;
use Library\Custom\Form\Element;
use App\Rules\StringLength;
use App\Rules\NotEmpty;

class ForDownloadApplication extends ElementAbstract {

	protected $_columnMap = array(
		'file_title'	=> 'attr_1',
		'file'			=> 'attr_2',
	);

	protected $_required_force = array(
			'file_title',
	);

	public function init() {
		parent::init();

		$max = 30;
		$element = new Element\Text('file_title');
		$element->addValidator(new StringLength(array('min' => null, 'max' => $max)));
		$element->setAttributes([
			'class' => 'watch-input-count',
			'data-maxlength' => $max,
		]);
		$this->add($element);

		$element = new Element\Hidden('file', array('disableLoadDefaultDecorators'=>true));
		$notEmpty = new NotEmpty(array('messages' => 'ファイルは必須です。'));
		$element->addValidator($notEmpty);
		$element->setAttributes([
			'class' => 'upload-file-id',
			'data-upload-to' => '/api-upload/hp-file',
			'data-file-type' => 'file',
		]);
		$this->add($element);

	}

    public function isValid($params, $checkError = true) {
		$error_flg = parent::isValid($params);
		if(array_key_exists('file_title', $params) && $params['file_title'] == ''){
			$this->getElement('file_title')->setMessages(['値は必須です。']);
			$error_flg = false;
		}
		if(array_key_exists('file_title', $params)) {
			$vowels = array('*','/','\\','#','(',')','\'','?','&','@','=',',','+','<','>','$','"','%',' ','　');
			foreach($vowels as $val) {
				if(strstr($params['file_title'], $val) !== false) {
					$this->getElement('file_title')->setMessages( array("ファイル名に使用できない文字列が含まれております。", "エラー文字：* / \ # ( ) ? & @ = , + < > $ \" % 半角スペース 全角スペース"));
					$error_flg = false;
					break;
				}
			}
		}
		return $error_flg;
    }
}