<?php
namespace library\Custom\Assessment\Features;

class Tel extends AbstractFeatures
{

    /**
     * 電話番号を設定しているか否か
     *
     * @return boolean
     */
    public function isUtilized()
    {
        if (getInstanceUser('cms')->getInstance()->checkHasTopOriginal()) return true;
        
        return !!$this->hp->tel;
    }
}
