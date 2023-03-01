<?php
namespace library\Custom\Assessment\Features;
use App\Repositories\HpMainParts\HpMainPartsRepository;
class CompanyMap extends AbstractFeatures
{

    /**
     * 会社地図を設定しているか否か
     * (会社紹介ページに地図パーツが設定されていれば true)
     *
     * @return boolean
     */
    public function isUtilized()
    {
        $pages = $this->hp->findPagesByType(config('constants.hp_page.TYPE_COMPANY'), false);
        if ($pages->count() === 0) {
            return false;
        }

        /** @var $page App\Models\HpPage */
        foreach ($pages as $page) {
            if ($page->isNew()){
                // 未作成ページはスキップ
                continue;
            }

            $parts = $page->fetchParts(HpMainPartsRepository::PARTS_MAP);
            if ($parts->count() > 0) {
                return true;
            }
        }

        return false;
    }
}
