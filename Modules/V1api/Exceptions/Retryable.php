<?php
namespace Modules\V1api\Exceptions;

use App\Exceptions\CustomException;
/**
 * アプリケーション例外
 */
abstract class Retryable extends CustomException
{   
	private $errId;

    public function __construct($errId, $msg = '', $code = 0, \Exception $previous = null)
    {
		$this->errId = empty($errId) ? $this->getDefaultErrId() : $errId;
        parent::__construct($msg, $code, $previous);
    }
	
	abstract function getDefaultErrId();
	
	public function getErrId()
	{
		return $this->errId;
	}
}