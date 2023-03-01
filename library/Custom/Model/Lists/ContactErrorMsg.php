<?php
namespace Library\Custom\Model\Lists;
use Modules\Api\Http\Form\Contact\ContactAbstract;
use App\Rules\EmailAddress;
use App\Rules\Tel;

class ContactErrorMsg extends ListAbstract {

    static protected $_instance;

    protected $_list = array(
        1  => ContactAbstract::ERROR_MSG_MAIL_AND_TEL,
        2  => ContactAbstract::ERROR_MSG_MAIL_OR_TEL,
        3  => ContactAbstract::ERROR_MSG_MAIL_AND_OTHER,
        4  => ContactAbstract::ERROR_MSG_TEL_AND_OTHER,
        5  => ContactAbstract::ERROR_MSG_TEL,
        6  => ContactAbstract::ERROR_MSG_MAIL,
        7  => ContactAbstract::ERROR_MSG_OTHER,
        8  => EmailAddress::MSG,
        9  => Tel::MSG,
        10 => '"必須項目の $msg を選択してください。"',
        11 => '"必須項目の $msg の入力がありません。"',
    );

    protected $_chinese = array(
        1  => '必须填写邮箱地址及电话号码。',
        2  => '邮箱地址或电话号码必须填写其中一项。',
        3  => '必须填写邮箱地址及其他联系方式。',
        4  => '必须填写电话号码及其他联系方式。',
        5  => '必须填写电话号码。',
        6  => '必须填写邮箱地址。',
        7  => '必须填写邮箱地址和电话号码以及其他联系方式。',
        8  => '邮箱地址的形式错误。',
        9  => '请输入半角数字，「-」。',
        10 => '"请选择必须项目 $msg 。"',
        11 => '"没有输入（必须项目的输入未完成）"',
    );
}


