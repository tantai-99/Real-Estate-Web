<?php
namespace App\Exceptions;
/**
 * アプリケーション例外
 *
 * エラーレスポンスを返す際に利用する
 *
 */
class UnAuthorizedForApi extends CustomException
{
	const DISPLAY_MESSAGE = "通信に失敗しました。\nお手数ですが再度ログインからやり直してください。";
}