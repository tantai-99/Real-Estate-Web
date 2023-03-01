<?php

class Session {

    private $namespace;

    public function __construct($namespace) {

        $this->namespace = $namespace;
    }

    public function setNamespace($namespace) {

        $this->namespace = $namespace;
    }

    public function hasNamespace() {

        return isset($_SESSION[$this->namespace]);
    }

    public function set(array $array) {

        foreach ($array as $key => $val) {
            $_SESSION[$this->namespace][$key] = $val;
        }
    }

    public function get($key) {

        return $_SESSION[$this->namespace][$key];
    }

    public function getAll() {

        return $_SESSION[$this->namespace];
    }

    public function destroy() {

        unset($_SESSION[$this->namespace]);
    }

    public function deleteByKey($key) {

        if (isset($_SESSION[$this->namespace][$key])) {
            unset($_SESSION[$this->namespace][$key]);
            return true;
        }
        return false;
    }

}













