<?php
namespace Library\Custom\Model\Lists;

class GuaranteeAssociation extends ListAbstract {

    static protected $_instance;

    protected $_list = array(
        1 => '（公社）全国宅地建物取引業保証協会',
        2 => '（公社）不動産保証協会',
        3 => '営業保証金供託'
    );

    protected $_chinese = array(
        1 => '（公社）全国宅地建物取引业保证协会',
        2 => '（公社）不动产保证协会',
        3 => '营业保证金供托济',
    );
}