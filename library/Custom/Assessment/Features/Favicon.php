<?php
namespace Library\Custom\Assessment\Features;


class Favicon extends AbstractFeatures
{

    /**
     * ファビコンを設定しているか否か
     *
     * @return boolean
     */
    public function isUtilized()
    {
        return !!$this->hp->favicon;
    }
}
