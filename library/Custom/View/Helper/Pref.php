<?php
namespace Library\Custom\View\Helper;

class Pref extends  HelperAbstract  {

    public function pref() {

        $map = new \Library\Custom\Hp\Map();
        return $map->getSelfPref();
    }
}