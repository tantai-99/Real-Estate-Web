<?php
namespace Library\Custom\View\Helper;

use App\Repositories\HpPage\HpPageRepositoryInterface;

class PageList extends  HelperAbstract {

    public function pageList() {

        return \App::make(HpPageRepositoryInterface::class)->getPages(\Library\Custom\User\Abstract::factory('default')->getCurrentHp()->id)->toArray();

    }
}