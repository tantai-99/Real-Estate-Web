<?php
namespace library\Custom\Assessment\Features;

class CompanyName extends AbstractFeatures
{

    /**
     * 会社名を設定しているか否か
     *
     * @return boolean
     */
    public function isUtilized()
    {
        if (getInstanceUser('cms')->getInstance()->checkHasTopOriginal()) return true;
        
        return !!$this->hp->company_name;
    }
}
