<?php
namespace Library\Custom\View\Helper;
/**
 * ユーザーサイト生成用ヘルパー
 *
 * ファイルの情報を返す
 */
class HpFileContent extends  HelperAbstract
{
    /**
     * @param int $hp_id
     * @param int $file_id
     * @throws InvalidArgumentException
     * @return array
     */
    public function hpFileContent($hp_id, $file_id)
    {
        $table = \App::make(\App\Repositories\HpFileContent\HpFileContentRepositoryInterface::class);;
        $file = $table->fetchInfo($hp_id, $file_id);

        if (!$file) {
            throw new InvalidArgumentException('file not found.');
        }

        $info = $file->toArray();
        if ($info['extension'] === 'pdf') {
            $info['kind'] = 'pdf';
        } else if ($info['extension'] === 'xls' || $info['extension'] === 'xlsx') {
            $info['kind'] = 'excel';
        } else if ($info['extension'] === 'doc' || $info['extension'] === 'docx') {
            $info['kind'] = 'word';
        } else if ($info['extension'] === 'ppt' || $info['extension'] === 'pptx') {
            $info['kind'] = 'powerpoint';
        }

        return $info;
    }

}