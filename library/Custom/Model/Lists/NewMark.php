<?php
namespace Library\Custom\Model\Lists;

class NewMark extends ListAbstract {

    const COMMON = 14;
    const NOT_USED = 0;
    const NEW_MARK = '<span class="new-mark">NEW</span>';

    protected $_list = array(
        3 => '3日間表示する',
        7 => '7日間表示する',
        14 => '14日間表示する',
        30 => '30日間表示する',
        0 => '表示しない'
    );

}