<?php
namespace library\Custom\Assessment\Features;

abstract class AbstractFeatures
{
    /**
     * @var App\Models\Hp
     */
    protected $hp;

    public function __construct($hp)
    {
        $this->hp = $hp;
    }

    /**
     * 機能を活用しているか否か
     *
     * @return boolean
     */
    abstract public function isUtilized();
}
