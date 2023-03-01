<?php

class Naming {

    public static function camelize($str) {

        $str = strtr($str, '_', ' ');
        $str = ucwords($str);
        return str_replace(' ', '', $str);
    }

    public static function snakize($str) {

        $str = preg_replace('/[A-Z]/', '_\0', $str);
        $str = strtolower($str);
        return ltrim($str, '_');
    }
}