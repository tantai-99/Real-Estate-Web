<?php
/**
 * 会社問い合わせのお問い合わせ内容
 *
 */
namespace Library\Custom\Model\Lists;

class FdpContactContactSubject extends ListAbstract {

    static protected $_instance;

    protected $_list = array(
        1 => 'FDPお店に直接訪問したい',
        2 => 'FDP希望条件に合う物件を紹介してほしい',
        3 => 'FDP入居・購入に関して相談したい',
    );

    protected $_chinese = array(
        1 => 'FDP直接到店咨询',
        2 => 'FDP有合适物件的话请通知我',
        3 => 'FDP我想咨询（咨询有关入住，购房的细节问题）',
    );
}