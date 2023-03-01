<?php
namespace library\Custom\Assessment\Features;

class OfficeHour extends AbstractFeatures
{

    /**
     * 営業時間を設定しているか否か
     *
     * @return boolean
     */
    public function isUtilized()
    {
        if (getInstanceUser('cms')->getInstance()->checkHasTopOriginal()) return true;
        
        return !!$this->hp->office_hour;
    }
}
