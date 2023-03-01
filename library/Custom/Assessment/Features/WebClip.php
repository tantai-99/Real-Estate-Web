<?php
namespace library\Custom\Assessment\Features;

class WebClip extends AbstractFeatures
{

    /**
     * ウェブクリップアイコンを設定しているか否か
     *
     * @return boolean
     */
    public function isUtilized()
    {
        return !!$this->hp->webclip;
    }
}
