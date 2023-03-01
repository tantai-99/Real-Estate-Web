<?php
namespace Library\Custom\Publish\Render;

use App\Repositories\HpPage\HpPageRepositoryInterface;
use App\Repositories\HpPage\HpPageRepository;

class Diff extends AbstractRender {

    public function setPages($pages) {

        parent::setPages($pages);
    }

    /**
     * 非公開になったページを削除
     */
    public function deletePrivateHtml() {

        foreach ($this->getDeviceList() as $devide) {

            $public = $this->listPublicHtml($devide);
            $new    = $this->listNewHtml($this->getPages());

            // 公開中にのみにあるファイル（＝非公開になったファイル）を削除
            $diff    = array_diff($public, $new);
            $viewDir = $this->getTempViewPath().DIRECTORY_SEPARATOR.$devide;
            foreach ($diff as $pathSuffix) {

                // 特集関連のファイルはスキップ。別途処理
                if (preg_match('/^sp-/', $pathSuffix)) {

                    continue;
                };

                $path = $viewDir.DIRECTORY_SEPARATOR.$pathSuffix;
                unlink($path);
            }

            // 特集
            $this->diffSpecial($public, $devide);
        }
    }

    /**
     * 公開中のhtml一覧を取得
     *
     * @param $device
     * @return array
     */
    private function listPublicHtml($device) {

        $list = [];

        $viewDir      = $this->getTempViewPath().DIRECTORY_SEPARATOR.$device;
        $listFullPath = $this->getFilePathRecursive($viewDir);

        foreach ($listFullPath as $fullPath) {
            $list[] = str_ireplace($viewDir.DIRECTORY_SEPARATOR, '', $fullPath);
        }
        return $list;
    }

    /**
     * 更新後のhtml一覧を取得
     *
     * @param $pages
     * @return array
     */
    private function listNewHtml($pages) {

        $list = [];

        $table    = \App::make(HpPageRepositoryInterface::class);
        $category = $table->getCategoryMap();

        foreach ($pages as $page) {

            // バリデーション
            if (!$page['public_flg'] || !$table->hasEntity($page['page_type_code'])) {
                continue;
            }

            // パス取得
            if (in_array($page['page_type_code'], $category[HpPageRepository::CATEGORY_FORM])) {

                $list = array_merge($list, $this->getContactPath($page));
                continue;
            }

            if ($page['public_path']) {
                $list[] = $page['public_path'];
            }
        }
        return $list;
    }

    /**
     * 問い合わせページのパスを取得
     *
     * @param $page
     * @return array
     */
    private function getContactPath($page) {

        $list = [];

        $parent = str_replace('index.html', '', $page['new_path']);

        foreach (self::getContactFileList() as $file) {

            if ($file == 'index') {
                $list[] = $page['new_path'];
            }
            else {
                $list[] = $parent.$file.DIRECTORY_SEPARATOR.'index.html';
            }
        }
        return $list;
    }

    /**
     * パスの変更になったファイルを移動
     */
    public function movePathChangedHtml() {

        $table    = \App::make(HpPageRepositoryInterface::class);
        $category = $table->getCategoryMap();

        foreach ($this->getPages() as $page) {

            // validate
            if (!$page['public_flg'] || !$page['public_path'] || !$table->hasEntity($page['page_type_code']) || $page['public_path'] == $page['new_path']) {
                continue;
            }

            // move
            foreach ($this->getDeviceList() as $device) {

                $viewDir = $this->getTempViewPath().DIRECTORY_SEPARATOR.$device;

                // お問い合わせ
                if (in_array($page['page_type_code'], $category[HpPageRepository::CATEGORY_FORM])) {

                    $this->moveContact($viewDir, $page);
                    continue;
                }

                // その他
                $this->moveHtml($viewDir, $page);
            }
        }

        // 特集はパスが変更にならないので処理は必要ない…はずっ…!!
        // @todo テストちゃんとする
    }

    /**
     * html移動
     *
     * @param $viewDir
     * @param $page
     */
    private function moveHtml($viewDir, $page) {

        $publicPath = $viewDir.DIRECTORY_SEPARATOR.$page['public_path'];
        $newPath    = $viewDir.DIRECTORY_SEPARATOR.$page['new_path'];

        $this->moveFileRecursive($publicPath, $newPath);
    }

    /**
     * お問い合わせページのhtml移動
     *
     * @param $viewDir
     * @param $page
     */
    private function moveContact($viewDir, $page) {

        $parentPublic = str_replace('index.html', '', $page['new_path']);
        $parentNew    = str_replace('index.html', '', $page['public_path']);

        foreach (self::getContactFileList() as $file) {

            if ($file == 'index') {
                $publicPath = $viewDir.DIRECTORY_SEPARATOR.$page['public_path'];
                $newPath    = $viewDir.DIRECTORY_SEPARATOR.$page['new_path'];
            }
            else {
                $publicPath = $viewDir.DIRECTORY_SEPARATOR.$parentPublic.$file.DIRECTORY_SEPARATOR.'index.html';
                $newPath    = $viewDir.DIRECTORY_SEPARATOR.$parentNew.$file.DIRECTORY_SEPARATOR.'index.html';
            }

            $this->moveFileRecursive($publicPath, $newPath);
        }
    }

    /**
     * 差分計算
     *
     */
    private function diffSpecial($publicPathList, $devide) {

        // 特集 検索
        $specialPathList = array_filter($publicPathList, function ($path) {

            return preg_match('/^sp-/', $path);
        });

        // 特集なければリターン
        if (count($specialPathList) < 1) {
            return;
        }

        $special = \Library\Custom\Publish\Special\Make\Rowset::getInstance();

        // 更新後に公開中のファイル名リスト
        $afterList = [];
        foreach ($special->rowsetCms as $row) {
            if ($row->is_public && !in_array($row->origin_id , $special->reserveList)) {
                $afterList[] = $row->filename;
            }
        }

        // 更新前に公開中のファイル名リスト
        $beforeList = [];
        foreach ($special->rowsetPublic as $row) {
            if ($row->is_public && !in_array($row->origin_id , $special->reserveList)) {
                $beforeList[] = $row->filename;
            }
        }

        // 削除するファイル名リスト
        $deleteList = array_diff($beforeList, $afterList);

        $viewDir = $this->getTempViewPath().DIRECTORY_SEPARATOR.$devide;

        foreach ($deleteList as $filename) {
            foreach ($publicPathList as $path) {
                if (preg_match("/^$filename/", $path)) {
                    $path = $viewDir.DIRECTORY_SEPARATOR.$filename;
                    exec("rm -rf $path");
                };
            }
        }
    }

}

?>
