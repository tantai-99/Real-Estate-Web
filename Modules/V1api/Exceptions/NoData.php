<?php
namespace Modules\V1api\Exceptions;

use App\Exceptions\CustomException;
/**
 * アプリケーション例外
 */
class NoData extends CustomException
{    
    public function getDisplayMessage() {
    	return '検索設定が無い、もしくは検索結果が０件です。';
    }
}