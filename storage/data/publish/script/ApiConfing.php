<?php

class ApiConfing {

    private $parsed;

    public function __construct() {

        $this->parsed = parse_ini_file(APPLICATION_PATH.'/../setting/api.ini');
    }

    public function get($key) {

        if (!isset($this->parsed[$key])) {
            return null;
        }

        return $this->parsed[$key];
    }

    public function getAll() {

        return $this->parsed;
    }
}