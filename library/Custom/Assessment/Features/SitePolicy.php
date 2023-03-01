<?php
namespace library\Custom\Assessment\Features;

class SitePolicy extends AbstractFeatures
{

    /**
     * サイトポリシーページを作成しているか否か
     *
     * @return boolean
     */
    public function isUtilized()
    {
        $pages = $this->hp->findPagesByType(config('constants.hp_page.TYPE_SITEPOLICY'), false);
        /** @var $page App\Models\HpPage */
        foreach ($pages as $page) {
            if (!$page->isNew()) {
                return true;
            }
        }

        return false;
    }
}
