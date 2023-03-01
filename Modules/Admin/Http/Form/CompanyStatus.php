<?php

namespace Modules\Admin\Http\Form;

use Library\Custom\Form;
use Library\Custom\Form\Element;
use App\Rules\DateFormat;
use App\Rules\Regex;
use App\Rules\InArray;

use Library\Custom\Model\Lists\CmsPlan;

class CompanyStatus extends Form
{

	protected $_row	;

    private $contractStatus = [
        'default' => '-',
        'on'      => '利用中',
        'off'     => '停止中'
	];
	
	public function __construct($row)
	{
		$this->_row = $row;
		

		$disabled	= array("disabled"		=> "disabled");

		$name		= "契約状況";
		$element	= new Element\Text('contract_status', $disabled);
		$element->setLabel($name);
		$element->setValue($this->_getContractSatus());
		$element->setAttributes(array("class" => "is-lock"));
		$element->addValidator( new InArray(array_values($this->contractStatus) )) ;
		$this->add($element);

		$name		= "プラン";
		$element	= new Element\Select('cms_plan');		// JQにて変更不可に
		$element->setLabel($name);
		$element->setAttributes(array("class" => "is-lock"));
		$obj		= new CmsPlan();
		$element->setValueOptions($obj->getAll());
		$this->add($element);

		$name		= "初回利用開始日";
		$element	= new Element\Text('initial_start_date', $disabled);
		$element->setLabel($name);
		$element->setAttributes(array('class' => 'datepicker is-lock'));
		$element->addValidator(new DateFormat(array('messages' => "{$name}が正しくありません。", 'format' => 'Y/m/d')));
		$this->add($element);

		$name		= "利用開始申請日";
		$element	= new Element\Text('applied_start_date', $disabled);
		$element->setLabel($name);
		$element->setAttributes(array('class' => 'datepicker is-lock'));
		$element->addValidator(new DateFormat(array('messages' => "{$name}が正しくありません。", 'format' => 'Y/m/d')));
		$this->add($element);

		$name		= "利用開始日";
		$element	= new Element\Text('start_date', $disabled);
		$element->setLabel($name);
		$element->setAttributes(array('class' => 'datepicker is-lock'));
		$element->addValidator(new DateFormat(array('messages' => "{$name}が正しくありません。", 'format' => 'Y/m/d')));
		$this->add($element);

		$name		= "契約担当者ID";
		$element	= new Element\Text('contract_staff_id', $disabled);
		$element->setLabel($name);
		$element->setAttributes(array("class" => "is-lock"));
		$element->addValidator(new Regex(array('messages' => '半角数字のみです。','pattern' => '/^[0-9]+$/')));
		$this->add($element);

		$name		= "契約者担当名";
		$element	= new Element\Text('contract_staff_name', $disabled);
		$element->setLabel($name);
		$element->setAttributes(array("class" => "is-lock"));
		$element->setAttributes(array("class" => "width:90%"));
		$this->add($element);

		$name		= "契約者担当部署";
		$element	= new Element\Text('contract_staff_department', $disabled);
		$element->setLabel($name);
		$element->setAttributes(array("class" => "is-lock"));
		$element->setAttributes(array("style" => "width:90%;"));
		$this->add($element);
	}


	public function getContractSatus()
	{
		$result	= 'none'	;
		
		if ( $this->_row && $this->_row->initial_start_date )
		{
			if ( $this->_row->isAvailable() )
			{
				$result	= "on"	;
			}
			else 
			{
				$result	= "off"	;
			}
		}
		
		return $result	;
	}

	protected function _getContractSatus()
	{
		$result	= $this->contractStatus['default'];
		if(isset($this->contractStatus[ $this->getContractSatus() ])) {
			$result = $this->contractStatus[ $this->getContractSatus() ];
		}
		
		return $result	;
	}
}
