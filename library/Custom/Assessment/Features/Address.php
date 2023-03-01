<?php
namespace library\Custom\Assessment\Features;

class Address extends AbstractFeatures
{

    /**
     * 住所を設定しているか否か
     *
     * @return boolean
     */
    public function isUtilized()
    {
        if (getInstanceUser('cms')->getInstance()->checkHasTopOriginal()) return true;
        
        return !!$this->hp->adress;
    }
}
