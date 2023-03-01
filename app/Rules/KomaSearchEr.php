<?php
namespace App\Rules;

class KomaSearchEr extends NotEmpty
{
    // ATHOME_HP_DEV-3367 Create msg error
    static public function createMsgKomaSearchEr() {
        $notEmpty = new NotEmpty(array('messages' => '内容を入力してください。 ※「埋込みタグ（PC用）」「埋込みタグ（スマホ用）」両方の登録が必要となります。'));
        return $notEmpty;
    }

    // ATHOME_HP_DEV-3367 add message error
    static public function addToKomaSearchEr($element) {
        $validators = $element->getValidators();
        array_unshift($validators, static::createMsgKomaSearchEr());
        $element->setValidator($validators);
    }
}