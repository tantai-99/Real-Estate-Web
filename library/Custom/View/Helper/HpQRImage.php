<?php
namespace Library\Custom\View\Helper;
/**
 * ユーザーサイト生成用ヘルパー
 *
 * QRコード画像のURLを吐き出す
 * CLIからの呼び出し時は、FTPアップ用として処理する
 *
 */
class HpQRImage extends  HelperAbstract
{
    public function hpQRImage($page_id, $isPreview)
    {
        return $isPreview ? "/image/company-qr?page_id={$page_id}" : "/images/qr/{$page_id}.png";
    }

}