<?php
namespace Library\Custom\Model\Lists;

class StructureTypeForCustomervoice extends ListAbstract {

    static protected $_instance;

    protected $_list = array(
        1 => '[売買]土地',
        2 => '[売買]一戸建て',
        3 => '[売買]マンション',
        4 => '[売買]事業用物件',
        5 => '[賃貸]アパート・マンション',
        6 => '[賃貸]一戸建て',
        7 => '[賃貸]事業用物件'
    );

    protected $_chinese = array(
        1 => '[买卖]土地',
        2 => '[买卖]日式别墅',
        3 => '[买卖]公寓',
        4 => '[买卖]商业地产',
        5 => '[租赁]日式简易出租房、公寓',
        6 => '[租赁]日式别墅',
        7 => '[租赁]商业类房产'
    );

}