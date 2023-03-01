<?php
namespace Library\Custom\Hp\Page;
use Library\Custom\Hp\Page as HpPage;
use App\Repositories\HpMainParts\HpMainPartsRepository;

class Privacypolicy extends HpPage {

	protected $_mainParts = array(
			HpMainPartsRepository::PARTS_PRIVACYPOLICY,
	);

	protected $_presetMainParts = array(
		array(
			array(
				HpMainPartsRepository::PARTS_PRIVACYPOLICY,
			)
		),
	);

    protected $_requiredMainParts = array(
        HpMainPartsRepository::PARTS_PRIVACYPOLICY,
    );

}