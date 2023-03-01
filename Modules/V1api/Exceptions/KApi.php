<?php
namespace Modules\V1api\Exceptions;
/**
 * アプリケーション例外
 */
class KApi extends Retryable
{   
	const DEFAULT_ERR = 'KAPI001';
	
	public function getDefaultErrId()
	{
		return static::DEFAULT_ERR;
	}
}