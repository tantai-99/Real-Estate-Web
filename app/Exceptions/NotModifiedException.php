<?php
namespace App\Exceptions;

class NotModifiedException extends CustomException
{
	const DISPLAY_MESSAGE = "";

    public function getDisplayMessage() {
        return $this->getMessage();
    }
}