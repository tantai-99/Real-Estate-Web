<?php

/**
 * logger
 * 
 */

namespace Library\Custom\Logger;

class AbstractLogger
{
    static protected $_instance;


    /** コンストラクタ
     *
     */
    public function __construct()
    {
    }

    static public function getInstance()
    {
        if (!static::$_instance) {
            static::$_instance = new static();
        }
        return static::$_instance;
    }
}
