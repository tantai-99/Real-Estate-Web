<?php
namespace library\Custom\Assessment\Features;

class Information extends AbstractFeatures
{

    /**
     * お知らせページを作成しているか否か
     *
     * @return boolean
     */
    public function isUtilized()
    {
        $pages = $this->hp->findPagesByType(config('constants.hp_page.TYPE_INFO_DETAIL'), false);
        /** @var $page App\Models\HpPage */
        foreach ($pages as $page) {
            if (!$page->isNew()) {
                return true;
            }
        }

        return false;
    }
}
