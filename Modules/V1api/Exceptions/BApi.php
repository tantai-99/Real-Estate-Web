<?php
namespace Modules\V1api\Exceptions;
/**
 * アプリケーション例外
 */
class BApi extends Retryable
{   
	const DEFAULT_ERR = 'BAPI001';
	
	public function getDefaultErrId()
	{
		return static::DEFAULT_ERR;
	}
}