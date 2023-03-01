<?php
namespace Library\Custom\Hp\Page;
use Library\Custom\Hp\Page as HpPage;
use App\Repositories\HpMainParts\HpMainPartsRepository;

class Moving extends HpPage {

    protected $_mainParts = array(
        HpMainPartsRepository::PARTS_MOVING,
    );

    protected $_presetMainParts = array(
        array(
            array(
                HpMainPartsRepository::PARTS_MOVING,
            )
        ),
    );

}