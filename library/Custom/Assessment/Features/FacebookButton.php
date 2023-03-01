<?php
namespace library\Custom\Assessment\Features;

class FacebookButton extends AbstractFeatures
{

    /**
     * Facebook有効か
     *
     * @return boolean
     */
    public function isUtilized()
    {
        return !!$this->hp->fb_like_button_flg;
    }
}
