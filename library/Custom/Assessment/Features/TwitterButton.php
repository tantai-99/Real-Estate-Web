<?php
namespace library\Custom\Assessment\Features;

class TwitterButton extends AbstractFeatures
{

    /**
     * Twitter有効か
     *
     * @return boolean
     */
    public function isUtilized()
    {
        return !!$this->hp->tw_tweet_button_flg;
    }
}
