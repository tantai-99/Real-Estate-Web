<?php
namespace Library\Custom\View\Helper;

use App\Repositories\HpFile2\HpFile2RepositoryInterface;
/**
 * ユーザーサイト生成用ヘルパー
 *
 * サイト内ファイルのURLを吐き出す
 * CLIからの呼び出し時は、FTPアップ用として処理する
 *
 */
class HpFile2 extends  HelperAbstract
{
    public function hpFile2( $file2_id )
    {
        if (getActionName() === 'previewPage' ){
            return '/file/hp-file2?file2_id=' . $file2_id ;
        }

        $file2 = \App::make(HpFile2RepositoryInterface::class)->fetchFile2Information( $file2_id ) ;
        if ( !$file2 ){
            throw new \InvalidArgumentException( 'file2 not found' );
        }

        return "/file2s/{$file2['title']}.{$file2['extension']}" ;
    }

}