<?php
namespace Modules\Admin\Http\Form;

use Library\Custom\Form;
use Library\Custom\Form\Element;
use App\Rules\DateFormat;
use Library\Custom\Model\Lists\CompanyAgreementType;

class CompanySearch extends Form
{
    public function init() {

        
        $element = new Element\Select('contract_type');
        $obj = new CompanyAgreementType();
        $list = array();
        $list[''] = '';
        $list += $obj->getAll();
        $element->setValueOptions($list);
        $this->add($element);
        
        //会員No
        $element = new Element\Text('member_no');
        $element->setLabel('会員No');
        $element->setAttributes([
            'style' => 'width:80%'
        ]);
        $this->add($element);

        //会社名
        $element = new Element\Text('company_name');
        $element->setLabel('会社名');
        $element->setAttributes([
            'class' => 'watch-input-count',
            'style' => 'width:80%'
        ]);
        $this->add($element);

        //契約店名
        $element = new Element\Text('member_name');
        $element->setLabel('契約店名');
        $this->add($element);

        //利用開始日
        $element = new Element\Text('start_date_s');
        $element->setDescription("※yyyy-mm-dd");
        $element->setAttributes([
            'style' => 'width:45%',
            'class' => 'datepicker'
        ]);
        $element->addValidator(new DateFormat(array('messages' => '利用開始日（開始）が正しくありません。', 'format' => 'Y-m-d')));
        $this->add($element);
        
        $element = new Element\Text('start_date_e');
        $element->setAttributes([
            'style' => 'width:45%',
            'class' => 'datepicker',
        ]);
        $element->addValidator(new DateFormat(array('messages' => '利用開始日（終了）が正しくありません。', 'format' => 'Y-m-d')));
        $this->add($element);


        //利用停止日
        $element = new Element\Text('end_date_s');
        $element->setDescription("※yyyy-mm-dd");
        $element->setAttributes([
            'style' => 'width:45%',
            'class' => 'datepicker',
            ]);
        $element->addValidator(new DateFormat(array('messages' => '利用停止日（開始）が正しくありません。', 'format' => 'Y-m-d')));
        $this->add($element);
        
        $element = new Element\Text('end_date_e');
        $element->setAttributes([
            'style' => 'width:45%',
            'class' => 'datepicker',
            ]);
        $element->addValidator(new DateFormat(array('messages' => '利用停止日（終了）が正しくありません。', 'format' => 'Y-m-d')));
        $this->add($element);
    }
}