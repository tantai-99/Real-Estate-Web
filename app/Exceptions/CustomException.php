<?php
namespace App\Exceptions;

use Exception;

class CustomException extends Exception
{
	const DISPLAY_MESSAGE = 'システムエラーが発生しました。';
	
    public function __construct($msg = '', $code = 0, Exception $previous = null)
    {
        parent::__construct($msg, $code, $previous);
    }
    
    public function getDisplayMessage() {
    	return static::DISPLAY_MESSAGE;
    }
}