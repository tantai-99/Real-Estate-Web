<?php
namespace Library\Custom\View\Helper;

class Sn extends  HelperAbstract {

    public function sn() {

        $session = app('request')->session()->get('page/default');

        if (is_null($session->counter)) {
            $session->counter = 0;
        }
        $session->counter++;
        echo 'name="'.$session->counter.'" id="'.$session->counter.'"';
    }
}