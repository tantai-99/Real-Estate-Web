<?php
namespace Modules\Admin\Http\Form;

use Library\Custom\Form;
use Library\Custom\Form\Element;
use App\Rules\StringLength;
use App\Rules\Regex;

class SpamBlockSearch extends Form
{

    public function init()
    {
        $element = new Element\Radio('range_option');
        $element->setLabel('設定範囲');
        $element->setValueOptions(array('全会員', '特定の会員'));
        $this->add($element);

        $element = new Element\Text('email');
        $element->setLabel('メールアドレス');
        $element->setAttribute('placeholder', 'example@example.com');
        $element->addValidator(new StringLength(array('max' => 255)));
        $this->add($element);

        $element = new Element\Text('tel');
        $element->setLabel('電話番号');
        $element->setAttribute('placeholder', '08012345678');
        $element->addValidator(new StringLength(array('max' => 255)));
        $element->addValidator(new Regex(array('pattern' => '/^[0-9]+$/', 'messages' => '半角数字のみ有効です')));
        $this->add($element);

        $element = new Element\Text('member_no');
        $element->setLabel('会員No');
        $element->setAttribute('placeholder', '00000000');
        $element->setAttribute('id', 'member_no');
        $element->addValidator(new StringLength(array('max' => 100)));
        $element->addValidator(new Regex(array('pattern' => '/^[a-zA-Z0-9 -~]+$/', 'messages' => '半角英数字のみです。')));
        $this->add($element);
    }

    public function isValid($data, $checkError = true) {
        $isValid = parent::isValid($data);

        if (!empty($_GET['member_no'])) {
            $rangeOption = isset($data['range_option']) ? (int)$data['range_option'] : null;
            if (!is_null($rangeOption) && $rangeOption == config('constants.spamblock.ALL_MEMBER')) {
                $this->getElement('member_no')->setMessages('設定範囲が全会員の場合は会員Noは入力しないでください');
                $isValid = false;
            }
        }
        return $isValid;
    }
}
