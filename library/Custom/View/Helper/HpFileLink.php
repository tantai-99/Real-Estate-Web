<?php
namespace Library\Custom\View\Helper;

use Illuminate\Support\Facades\App;
use App\Http\Controllers\FileController;
/**
 * ユーザーサイト生成用ヘルパー
 *
 * ファイルへのURLを吐き出す
 * CLIからの呼び出し時は、FTPアップ用として処理する
 *
 */
class HpFileLink extends  HelperAbstract
{
    public function hpFileLink($hp_id, $file_id)
    {
        if (getActionName() === 'previewPage') {
            return action(FileController::class . '@hpFile', ['id' => $file_id]);
        }

        $file = (new HpFileContent())->hpFileContent($hp_id, $file_id);

        return "/files/{$file_id}/" . urlencode($file['filename']);
    }

}