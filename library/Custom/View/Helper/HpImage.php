<?php
namespace Library\Custom\View\Helper;

use App\Repositories\HpImage\HpImageRepositoryInterface;
/**
 * ユーザーサイト生成用ヘルパー
 *
 * サイト内画像のURLを吐き出す
 * CLIからの呼び出し時は、FTPアップ用として処理する
 *
 */
class HpImage extends  HelperAbstract
{
    public function hpImage($image_id)
    {
        if (getActionName() === 'previewPage'){
            return '/image/hp-image?image_id=' . $image_id;
        }
        
        $image = \App::make(HpImageRepositoryInterface::class)->fetchImageInformation($image_id);

        if (!$image){
            throw new \InvalidArgumentException('image not found');
        }
        
        return "/images/{$image_id}.{$image['extension']}";
    }

}