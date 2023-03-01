<?php

namespace Modules\Admin\Http\Form;

use Library\Custom\Form;
use Library\Custom\Form\Element;
use App\Rules\StringLength;
use App\Rules\Regex;
use App\Rules\Domain;

use Library\Custom\Model\Lists\FtpPasvMode;

class CompanyRegistFtp extends Form
{

	public function init()
	{

		

		//ＦＴＰ情報枠 #########################

		// 営業デモ用ドメイン
		$config	= getConfigs('sales_demo');
		$element	= new Element\Hidden('demo_domain');
		$element->setValue($config->demo->domain);
		$this->add($element);

		//FTP_サーバー名
		$element = new Element\Text('ftp_server_name');
		$element->setLabel('FTP_サーバー名');
		$element->addValidator(new Domain());
		$element->addValidator(new StringLength(array('max' => 100)));
		$element->setRequired(true);
		$this->add($element);

		//FTP_ポート番号
		$element = new Element\Text('ftp_server_port');
		$element->setLabel('FTP_ポート番号');
		$element->setRequired(true);
		$element->addValidator(new Regex(array('pattern' => '/^[0-9]+$/', 'messages' => '半角数字のみです。')));
		$element->addValidator(new StringLength(array('max' => 100)));
		$this->add($element);

		//FTP_ユーザーID
		$element = new Element\Text('ftp_user_id');
		$element->setLabel('FTP_ユーザーID');
		$element->setRequired(true);
		$element->addValidator(new StringLength(array('max' => 100)));
		$element->addValidator(new Regex(array('pattern' => '/^[a-zA-Z0-9 -~]+$/', 'messages' => '半角英数字のみです。')));
		$element->setRequired(true);
		$this->add($element);

		//FTP_パスワード
		$element = new Element\Text('ftp_password');
		$element->setLabel('FTP_パスワード');
		$element->setRequired(true);
		$element->addValidator(new StringLength(array('max' => 100)));
		$element->addValidator(new Regex(array('pattern' => '/^[a-zA-Z0-9 -~]+$/', 'messages' => '半角英数字のみです。')));
		$this->add($element);

		//FTP_ディレクトリ名
		$element = new Element\Text('ftp_directory');
		$element->setLabel('FTP_ディレクトリ名');
		$element->setRequired(true);
		$element->addValidator(new StringLength(array('max' => 100)));
		$element->addValidator(new Domain());
		$element->setRequired(true);
		$this->add($element);

		//FTP_PASVモードフラグ
		$element = new Element\Radio('ftp_pasv_flg');
		$element->setLabel('FTP_PASVモード');
		$element->setRequired(true);
		$ftpPasvObj = new FtpPasvMode();
		$element->setValueOptions($ftpPasvObj->getAll());
		$this->add($element);
	}

	public function isValid($params, $checkError = true)
	{
		if ($params['basic']['contract_type'] == config('constants.company_agreement_type.CONTRACT_TYPE_ANALYZE')) {
			$this->getElement('ftp_server_name')->setRequired(false);
			$this->getElement('ftp_server_port')->setRequired(false);
			$this->getElement('ftp_user_id')->setRequired(false);
			$this->getElement('ftp_directory')->setRequired(false);
			$this->getElement('ftp_pasv_flg')->setRequired(false);
		}
		return parent::isValid($params, $checkError);
	}
}
