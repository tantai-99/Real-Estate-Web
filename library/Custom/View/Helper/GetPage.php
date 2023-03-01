<?php
namespace Library\Custom\View\Helper;

use App\Repositories\HpPage\HpPageRepositoryInterface;

class GetPage extends  HelperAbstract {

    public function getPage(array $pageList, $id) {

        if (isset($pageList[$id])) {
            return $pageList[$id];
        }

        $table = \App::make(HpPageRepositoryInterface::class);
        $row   = $table->fetchRowById($id);

        // 物件検索お問い合わせのみDB直接参照
        if ($row && in_array($row->page_type_code, $table->estateContactPageTypeCodeList())) {
            return $row->toArray();
        }

        return [];
    }
}