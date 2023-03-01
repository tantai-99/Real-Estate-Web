<?php

require_once(APPLICATION_PATH.'/../script/_AbstractController.php');

class Controller extends AbstractController {

    protected $device = 'sp';

    public function __construct(ViewHelper $viewHelper, array $config) {

        parent::__construct($viewHelper, $config);
        $this->path = dirname(__FILE__);
    }

}