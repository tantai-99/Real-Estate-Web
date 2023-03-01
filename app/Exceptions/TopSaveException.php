<?php
namespace App\Exceptions;
/**
 * アプリケーション例外
 *
 * エラーレスポンスを返す際に利用する
 *
 */
class TopSaveException extends CustomException
{
	const DISPLAY_MESSAGE = "公開予約があるためサイドコンテンツを更新できませんでした。";
}