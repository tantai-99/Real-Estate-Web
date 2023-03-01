<?php
namespace Library\Custom\Model\Lists;

    class HankyoPlus extends ListAbstract {

        const FORM_VIEW = 1;
        const FORM_NOT_VIEW = 0;

        protected $_list = array(
            1 => 'お問い合わせフォームに表示する',
            0 => 'お問い合わせフォームに表示しない'
        );

    }