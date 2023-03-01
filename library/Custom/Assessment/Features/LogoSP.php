<?php
namespace library\Custom\Assessment\Features;

class LogoSP extends AbstractFeatures
{

    /**
     * 企業ロゴ(スマホ)設定しているか否か
     *
     * @return boolean
     */
    public function isUtilized()
    {
        if (getInstanceUser('cms')->getInstance()->checkHasTopOriginal()) return true;
        
        return !!$this->hp->logo_sp;
    }
}
