<?php
namespace library\Custom\Assessment\Features;

class Copyright extends AbstractFeatures
{

    /**
     * 著作権表記を設定しているか否か
     *
     * @return boolean
     */
    public function isUtilized()
    {
        if (getInstanceUser('cms')->getInstance()->checkHasTopOriginal()) return true;
        
        return !!$this->hp->copylight;
    }
}
