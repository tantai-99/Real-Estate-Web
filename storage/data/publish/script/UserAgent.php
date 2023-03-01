<?php

require_once(APPLICATION_PATH.'/../script/Request.php');

class UserAgent {

    private $ua;
    private $device;

    private $request;

    public function __construct() {

        $this->request = new Request();
        $this->set();
    }

    public function set() {

        $this->ua = mb_strtolower($_SERVER['HTTP_USER_AGENT']);
        if (strpos($this->ua, 'iphone') !== false) {
            $this->device = 'mobile';
        }
        elseif (strpos($this->ua, 'ipod') !== false) {
            $this->device = 'mobile';
        }
        elseif ((strpos($this->ua, 'android') !== false) && (strpos($this->ua, 'mobile') !== false)) {
            $this->device = 'mobile';
        }
        elseif ((strpos($this->ua, 'windows') !== false) && (strpos($this->ua, 'phone') !== false)) {
            $this->device = 'mobile';
        }
        elseif ((strpos($this->ua, 'firefox') !== false) && (strpos($this->ua, 'mobile') !== false)) {
            $this->device = 'mobile';
        }
        elseif (strpos($this->ua, 'blackberry') !== false) {
            $this->device = 'mobile';
        }
        elseif (strpos($this->ua, 'ipad') !== false) {
            $this->device = 'tablet';
        }
        elseif ((strpos($this->ua, 'windows') !== false) && (strpos($this->ua, 'touch') !== false)) {
            $this->device = 'tablet';
        }
        elseif ((strpos($this->ua, 'android') !== false) && (strpos($this->ua, 'mobile') === false)) {
            $this->device = 'tablet';
        }
        elseif ((strpos($this->ua, 'firefox') !== false) && (strpos($this->ua, 'tablet') !== false)) {
            $this->device = 'tablet';
        }
        elseif ((strpos($this->ua, 'kindle') !== false) || (strpos($this->ua, 'silk') !== false)) {
            $this->device = 'tablet';
        }
        elseif ((strpos($this->ua, 'playbook') !== false)) {
            $this->device = 'tablet';
        }
        else {
            $this->device = 'others';
        }
        return $this->device;
    }

    public function useragent() {

        return $this->ua;
    }

    public function device() {

        if ($this->set() === 'mobile') {
            return 'sp';
        }
        return 'pc';
    }

    public function requestDevice() {

        $parse = json_decode($this->request->getCookie('device'));

        if ($parse != null && $parse->device !== null) {
            return $parse->device;
        }

        return $this->device();
    }

}