<?php

// cms server
if ($publishType == 4) {
    $breadCrumbLdJson = new breadCrumbLdJson($view, $publishType, $device, $filename, $pages, $thisPage);
}
// gmo server
else {
    $breadCrumbLdJson = new breadCrumbLdJson($this->_view, $publishType, $device);
}

/**
 * パンくずの表示
 *
 * Class breadCrumb
 */
class breadCrumbLdJson {

    public $viewHelper;

    public $dataBreadCrumb;

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
            $this->hpBreadCrumb($thisPage['id'], $pages, $filename);
        }
        elseif (isset($filename) && $filename == 'sitemap') {
            $this->breadCrumbName('サイトマップ');
        }
        elseif (isset($filename) && $filename == '404notFound') {
            $this->breadCrumbName('ページが見つかりません');
        }

    }

    public function breadCrumbName($name, $filename) {
        $this->dataBreadCrumb[] = array('filename' => '', 'name' => $name);
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
        foreach ($pageIds as $pageId) {
            $page = isset($pages[$pageId]) ? $pages[$pageId] : $this->dummy();
            $this->dataBreadCrumb[] = array('filename' => '/'.substr($page['new_path'], 0, mb_strlen($page['new_path']) - strlen('index.html')), 'name' => $page['title']);
        }
        $this->dataBreadCrumb[] = array('filename' => '', 'name' => $thisPage['title']);
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
$domain = $breadCrumbLdJson->viewHelper->protocol.'://'.$_SERVER['HTTP_HOST'];
?>
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement":
    [
        {
            "@type": "ListItem",
            "position": 1,
            "name": "ホーム",
            "item": "<?php echo $domain;?>"
        },
        <?php
        $i = 2;
        foreach ($breadCrumbLdJson->dataBreadCrumb as $index=>$dataBreadCrumb) {
        ?>
        {
                <?php if (empty($dataBreadCrumb['filename'])) :?>
                "@type": "ListItem",
                "position": <?php echo $i;?>,
                "name": "<?php echo $dataBreadCrumb['name'];?>"
            }
                <?php else :?>
                "@type": "ListItem",
                "position": <?php echo $i;?>,
                "name": "<?php echo $dataBreadCrumb['name'];?>",
                "item": "<?php echo $domain.$dataBreadCrumb['filename'];?>"
            },
                <?php endif; ?>
            <?php
            $i++;
            }      
        ?>
    ]
}
</script>