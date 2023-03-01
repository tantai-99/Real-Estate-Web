<?php
namespace library\Custom\Assessment\Features;

class FooterLink extends AbstractFeatures
{

    /**
     * ウェブクリップアイコンを設定しているか否か
     *
     * @return boolean
     */
    public function isUtilized()
    {
        return $this->hp->footer_link_level;
    }
}
