<?php

// cms server
if ($publishType == 4) {
    new breadCrumb($view, $publishType, $device, $filename, $pages, $thisPage);
}
// gmo server
else {
    new breadCrumb($this->_view, $publishType, $device);
}

/**
 * パンくずの表示
 *
 * Class breadCrumb
 */
class breadCrumb {

    public $viewHelper;

    public function __construct($viewObj, $publishType, $device, $filename = null, $pages = null, $thisPage = null) {

        // cms server
        if ($publishType == 4) {
            $this->viewHelper = $viewObj;
        }

        // gmo server
        else {

            $this->viewHelper = new ViewHelper($viewObj);

            // ページ一覧
            $pages = unserialize($this->viewHelper->getContentSettingFile('pages.txt'));

            // ファイル名
            $db = debug_backtrace();
            $filename = $this->viewHelper->getFileName(dirname($db[2]['file']));
            $pathPublic = $this->viewHelper->getPathPublic(dirname($db[2]['file']), $device);

            // 表示中のページ
            $thisPage = $this->viewHelper->getPageByPathPublic($pathPublic, $filename);
        }

        if ($thisPage) {
            // パンくず表示
            $this->hpBreadCrumb($thisPage['id'], $pages);
        }
        elseif (isset($filename) && $filename == 'sitemap') {
            echo '<ul><li><a href="/">ホーム</a></li><li>サイトマップ</li></ul>';
        }
        elseif (isset($filename) && $filename == '404notFound') {
            echo '<ul><li><a href="/">ホーム</a></li><li>ページが見つかりません</li></ul>';
        }

    }

    /**
     * パンくず表示
     *
     * @param $pageId
     * @param $pages
     */
    public function hpBreadCrumb($pageId, $pages) {

        $thisPage = $pages[$pageId];

        $pageIds = array();
        $pageIds = $this->getBreadCrumb($pageId, $pages, $pageIds);
        krsort($pageIds);
        array_pop($pageIds);

        echo '<ul>';
        echo '<li><a href="/">ホーム</a></li>';
        foreach ($pageIds as $pageId) {

            $page = isset($pages[$pageId]) ? $pages[$pageId] : $this->dummy();
            echo '<li>';
            echo '<a '.$this->viewHelper->hpHref($page).'>'.$page['title'].'</a>';
            echo '</li>';
        }
        echo '<li>'.$thisPage['title'].'</li>';
        echo '</ul>';
    }

    /**
     * 親フォルダをさかのぼってページIDを取得
     *
     * @param $pageId
     * @param $pages
     * @param $pageIds
     * @return array
     */
    private function getBreadCrumb($pageId, $pages, $pageIds) {

        $pageIds[] = $pageId;
        $page = isset($pages[$pageId]) ? $pages[$pageId] : $this->dummy();
        if ($page['parent_page_id'] > 0) {
            $pageIds = $this->getBreadCrumb($page['parent_page_id'], $pages, $pageIds);
        }
        return $pageIds;
    }

    private function dummy() {

        return array(
            'title'          => '（作成中）',
            'page_type_code' => '',
            'new_path'       => '',
            'parent_page_id' => '',
        );
    }

}

?>