<?php
namespace Library\Custom\Hp\Page\SideParts\Element;
use  Library\Custom\Hp\Page\Parts\Element\ElementAbstract;
use App\Repositories\HpSideElements\HpSideElementsRepositoryInterface;

class SideEleAbstract extends ElementAbstract {

	public function getSaveTable() {
		return \App::make(HpSideElementsRepositoryInterface::class);
	}

}