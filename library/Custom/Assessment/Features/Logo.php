<?php
namespace library\Custom\Assessment\Features;

class Logo extends AbstractFeatures
{

    /**
     * 企業ロゴ設定しているか否か
     *
     * @return boolean
     */
    public function isUtilized()
    {
        if (getInstanceUser('cms')->getInstance()->checkHasTopOriginal()) return true;
        
        return !!$this->hp->logo_pc;
    }
}
