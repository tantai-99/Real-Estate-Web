<?php
namespace Modules\Admin\Http\Form;

use App;
use Library\Custom\Form;
use Library\Custom\Form\Element;
use App\Rules\StringLength;
use App\Rules\Regex;
use App\Repositories\Company\CompanyRepositoryInterface;

class SpamBlockRegist extends Form
{
    public function init() {
        $element = new Element\Hidden('id');
        $this->add($element);

        $element = new Element\Radio('range_option');
        $element->setLabel('設定範囲');
        $element->setValueOptions(array('全会員', '特定の会員'));
        $element->setValue('0');
        $this->add($element);

        $element = new Element\Text('member_no_add');
        $element->setAttribute('placeholder', '00000001');
        $this->add($element);

        $element = new Element\Text('member_no');
        $element->setLabel('会員No');
        $this->add($element);

        $element = new Element\Text('email');
        $element->setLabel('メールアドレス');
        $element->setAttribute('placeholder', 'example@example.com');
        $element->addValidator(new StringLength(array('max' => 255)));
        $this->add($element);

        $element = new Element\Radio('email_option');
        $element->setLabel('メールアドレスのオプション');
        $element->setValueOptions(array('完全一致', '部分一致'));
        $element->setValue('0');
        $this->add($element);

        $element = new Element\Text('tel');
        $element->setLabel('電話番号');
        $element->setAttribute('placeholder', '08012345678');
        $element->addValidator(new StringLength(array('max' => 255)));
        $element->addValidator(new Regex(array('pattern' => '/^[0-9]+$/', 'messages' => '半角数字のみ有効です')));
        $this->add($element);
    }

    public function isValid($data, $checkError = false) {
        $isValid = parent::isValid($data);

        if (isset($_POST['add'])) {
            if ($data['range_option'] == config('constants.spamblock.ALL_MEMBER')) {
                $this->getElement('member_no')->setMessages('設定範囲が全会員の場合は会員Noは入力しないでください');
                $isValid = false;
            } else if (is_null($data['member_no_add']) || $data['member_no_add'] == '') {
                $this->getElement('member_no')->setMessages('会員Noを入力してください');
                $isValid = false;
            } else {
                //会員Noが存在するか
                $companyObj = App::make(CompanyRepositoryInterface::class);
                $company = $companyObj->fetchRow([['member_no', $data['member_no_add']]]);
                if (is_null($company)) {
                    $this->getElement('member_no')->setMessages('「' . $data['member_no_add'] . '」に一致する会員Noがありません');
                    $isValid = false;
                } else {
                    $memberNoList = explode(',', $data['member_no']);
                    foreach ($memberNoList as $memberNo) {
                        if ($data['member_no_add'] == $memberNo) {
                            $this->getElement('member_no')->setMessages('登録済みの会員Noです');
                            $isValid = false;
                            break;
                        }
                    }
                }
            }
        } else if (isset($_POST['conf'])) {
            if (!empty($data['member_no_add'])) {
                if ($data['range_option'] == config('constants.spamblock.ALL_MEMBER')) {
                    $this->getElement('member_no')->setMessages('設定範囲が全会員の場合は会員Noは入力しないでください');
                    $isValid = false;
                } elseif ($data['range_option'] == config('constants.spamblock.SPECIFIC_MEMBER')) {
                    $this->getElement('member_no')->setMessages('会員を追加してください');
                    $isValid = false;
                }
            } else if ($data['range_option'] == config('constants.spamblock.SPECIFIC_MEMBER') && $data['member_no'] == '') {
                $this->getElement('member_no')->setMessages('会員を追加してください');
                $isValid = false;
            }
            //メールアドレスか電話番号は必須
            if ((is_null($data['email']) || $data['email'] == '') && (is_null($data['tel']) || $data['tel'] == '')) {
                $this->getElement('email')->setMessages('メールアドレスか電話番号は必須です');
                $this->getElement('tel')->setMessages('メールアドレスか電話番号は必須です');
                $isValid = false;
            }
        }
        return $isValid;
    }
}