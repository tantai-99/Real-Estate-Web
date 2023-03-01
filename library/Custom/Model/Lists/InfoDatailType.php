<?php
namespace Library\Custom\Model\Lists;

use Library\Custom\Model\Lists\ListAbstract;

class InfoDatailType extends ListAbstract {

    const ADD_PAGE = 1;
    const ONLY_ADD_LIST = 2;
    static protected $_instance;

    protected $_list = array(
        1 => 'お知らせ（詳細ページ追加）',
        2 => 'お知らせ（一覧のみ追加）',
    );

}