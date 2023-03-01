<?php
namespace Modules\V1api\Exceptions;

use App\Exceptions\CustomException;
/**
 * アプリケーション例外
 */
class Expired extends CustomException
{    
	public function getMessageId()
	{
		return 'MSG001';
	}

	public function getDisplayMessage() {
		return <<< EOD
誠に恐れ入りますが、お客様がアクセスしようとしたページがみつかりませんでした。<br>
お探しのページはすでに削除されたか、名前が変更されたか、<br>
アドレスが間違っている可能性がございます。
EOD;
    }
}