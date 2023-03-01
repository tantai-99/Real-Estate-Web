<?php
namespace library\Custom\Assessment\Features;

class LineButton extends AbstractFeatures
{

    /**
     * Line有効か
     *
     * @return boolean
     */
    public function isUtilized()
    {
        return !!$this->hp->line_button_flg;
    }
}
